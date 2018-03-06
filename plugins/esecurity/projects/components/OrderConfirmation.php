<?php namespace Esecurity\Projects\Components;

use Auth;
use ApplicationException;
use Cms\Classes\ComponentBase;
use Rainlab\User\Models\User;
use Logimonde\Stock\Models\Order;
use Logimonde\Stock\Models\Settings;
use RainLab\Translate\Classes\Translator as languageTranslator;

class OrderConfirmation extends ComponentBase
{

    public $lang;

    public $user;

    public function componentDetails()
    {
        return [
            'name' => 'Order Confirmation',
            'description' => 'Order Confirmation Component'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function init()
    {
        $translator = languageTranslator::instance();
        $this->lang = $translator->getLocale();
        $this->user = $this->user();
    }

    public function onRun()
    {
        if ($token = $this->param('token')) {
            $order = Order::where('token', $token)->first();
            if ($order) {
                $data = $_REQUEST;
                $this->page['order'] = $order;
                $discountRate = Settings::get('discount_rate');
                $this->page['discountRate'] = $discountRate;
                $this->setPayment($data,$order);
            } else {
                return \Redirect::to('404');
            }
        } else {
            return \Redirect::to('404');
        }
    }

    private function setPayment($data, $order)
    {
        $data['invoice'] = $order->id;
        $data['payerid'] = $order->payerid;

        if (isset($_REQUEST['txn_id'])) {
            $data['paypal_transaction'] = $_REQUEST['txn_id'];

            $this->updatePaymentSuccessful($data, $order->id);
            $this->sendConfirmationOrderEmail($data, $order);
            return true;

        } else {

        }
    }

    private function updatePaymentSuccessful($data, $orderId)
    {
        $order = Order::whereId($orderId)->first();
        $order->completion_date = date('Y-m-d H:i:s');
        $order->paypal_transaction = $data['paypal_transaction'];
        $order->save();
    }

    private function sendConfirmationOrderEmail($data, $order)
    {
        $data['order'] = $order;
        $data['urlOrder'] = "http://www.paxstockphoto.com/order/confirmation/";
        $data['server'] = 'http://www.paxstockphoto.com/';
        \Mail::send('logimonde.stock::mail.order', $data, function ($message) use ($data) {
            $message->to($data['order']['email'], $data['order']['card_holder_name']);
            $message->to('juancarlos@logimonde.com', 'Juan Carlos lora');
        });
    }

    private function sendEmailAdminErrorMessage($error, $data)
    {
        $debug_export = var_export($data, true);
        \Mail::send([
            'text' => $error,
            'html' => "<p>$error</p><p><pre>$debug_export</pre></p>",
            'raw' => true
        ], $data, function ($message) {
            $message->subject('PayPal Error message - From PAXStockPhoto');
            $message->to('juancarlos@logimonde.com');
        });
    }

    /**
     * Returns the logged in user, if available
     */
    public function user()
    {
        if (!Auth::check())
            return null;

        return Auth::getUser();
    }
}
