<?php namespace Esecurity\Projects\Components;

use Cms\Classes\ComponentBase;
use Auth;
use ApplicationException;
use RainLab\Translate\Classes\Translator as languageTranslator;
use Esecurity\Projects\Classes\PaypalPayments;

class Checkout extends ComponentBase
{
    public $lang;


    public $server;

    public function componentDetails()
    {
        return [
            'name' => 'Checkout',
            'description' => 'Checkout Component'
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

        $this->server = env('APP_ENV') == 'dev' ? 'http://10.1.1.24/dev/media_stock' : 'http://www.paxstockphoto.com';
    }

    public function onRun()
    {

    }

    public function loadBasketInfo()
    {

        $this->addJs('assets/js/ccv.js');
        $this->addJs('assets/js/checkout.js');
        $content = Cart::content();

        $content->each(function ($row) {
            $row->slug = $row->model->slug;
        });
        $this->page['basketItems'] = $content;
        $this->page['count'] = Cart::count();
        $this->getOrderSummary();

        $this->page['months'] = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $this->page['years'] = $this->setYears();
    }


    protected function setYears()
    {
        $array = array();
        for ($index = date('Y'); $index < date('Y') + 6; $index++) {
            array_push($array, $index);
        }
        return $array;
    }

    private function getOrderSummary()
    {
        $discountRate = Settings::get('discount_rate');
        $minPhotos = Settings::get('photos_discount');
        $count = Cart::count();
        $subtotal = Cart::subtotal();
        $discount = $count >= $minPhotos ? ($subtotal * $discountRate) / 100 : 0;
        $gst = ($subtotal - $discount) * 0.05;
        $qst = ($subtotal - $discount) * 0.09975;
        $this->page['discountRate'] = $count >= $minPhotos ? $discountRate : 0;
        $this->page['discount'] = $discount;
        $this->page['subtotal'] = $subtotal;
        $this->page['gst'] = $gst;
        $this->page['qst'] = $qst;
        $this->page['total'] = ($subtotal - $discount) + $gst + $qst;
    }

    public function onRemovePhoto()
    {
        Cart::remove(post('row_id'));
        return \Redirect::to($this->pageUrl('checkout'));

    }

    public function saveCheckout()
    {
        $data = post();
        if (!isset($data['city_id']) or $data['city_id'] == '') {
            \Flash::error(\Lang::get('logimonde.stock::lang.checkout.error1'));
            return \Redirect::to($this->pageUrl('checkout'));
        }
        if ($data['grand_total'] > 0) {
            $moreData = $this->getDataPayment($data);
            $data = array_merge($data, $moreData);
            if ($this->user) {
                $this->updateUserAccount($data);
                $order = $this->insertOrder($data, $this->user);
            } else {
                if (isset($data['account']) and $data['account'] == '1') {
                    $user = $this->insertUserAccount($data);
                    $order = $this->insertOrder($data, $user);
                } else {
                    $order = $this->insertOrder($data);
                }
            }
            if ($order) {
                Cart::destroy();
                $data['return'] = $this->pageUrl('order-confirmation', ['token' => $order->token]);
                $data['item_number'] = $order->id;
                $this->page['form_paypal'] = 'OK';
                $mode = Settings::get('mode');
                $this->page['post_url'] = ($mode == 1) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
                $this->page['params'] = (array)$data;
            }

        } else {
            throw new ApplicationException();
        }
    }

    private function insertOrder($data, $user = null)
    {
        $order = new Order;
        $order->token = str_random(64);
        $order->email = $data['email'];
        $order->card_holder_name = $data['first_name'] . ' ' . $data['last_name'];
        $order->billing_address = $data['street_addr'];
        $order->company = $data['company'];
        $order->phone = $data['phone'];
        $order->zip = $data['zip'];
        $order->city_id = $data['city_id'];
        $order->state_id = $data['state_id'];
        $order->country_id = $data['country_id'];
        $order->discount = $data['discount'];
        $order->subtotal = $data['subtotal'];
        $order->tax1_total = $data['tax1_total'];
        $order->tax2_total = $data['tax2_total'];
        $order->grand_total = $data['grand_total'];
        $order->language = $this->lang;
        $order->payerid = str_random(16);
        if (!is_null($user)) {
            $order->user_id = $user->id;
        }
        $order->save();

        $this->insertOrderItems($data, $order);

        return $order;
    }

    private function insertOrderItems($data, $order)
    {
        $photos = $data['photos'];
        if (is_array($photos)) {
            foreach ($photos as $photo) {
                $item = new OrderItem;
                $item->photo_id = $photo;
                $item->order_id = $order->id;
                $item->price = Settings::get('price');
                $item->quantity = '1';
                $item->save();
            }
        }
    }

    private function insertUserAccount($data)
    {
        $user = new User;
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        $user->fill($data);
        $user->save(null, post('_session_key'));

        $this->sendEmailConfirmationUser($data);
    }

    private function updateUserAccount($data)
    {
        $user = $this->user;
        $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
        $user->fill($data);
        $user->save(null, post('_session_key'));
    }


    private function getDataPayment($data)
    {
        if (!$this->user) {
            $data['password'] = str_random(8);
            $data['password_confirmation'] = $data['password'];
        }
        $city = City::where('idVille', $data['city_id'])->first();
        $country = Country::select('shortcut')->where('idPays', $data['country_id'])->first();
        $state = Province::select('code_province')->where('idProvince', $data['state_id'])->first();
        $data['language'] = $this->lang;
        $data['city'] = $this->lang == 'en' ? $city->nom_en : $city->nom_fr;
        $data['country'] = $country->shortcut;
        $data['state'] = $state->code_province;
        $data['logo'] = $this->server . '/themes/logimonde/assets/images/PAX-StockPhoto.png';
        $data['server'] = $this->server;
        $data['urlPhoto'] = $this->server . '/photo/';
        $data['urlOrder'] = $this->server . '/order/confirmation/';
        $data['business'] = Settings::get('paypal_email');
        $data['currency_code'] = Settings::get('currency_code');
        $data['cmd'] = "_xclick";
        $data['ls'] = $country->shortcut;;
        $data['rm'] = "2";
        $data['item_name'] = "PAXStockPhoto.com";
        $data['address1'] = $data['street_addr'];
        return $data;
    }


    private function sendEmailConfirmationUser($data)
    {

        \Mail::send('logimonde.account::mail.welcome_user_' . $data['language'], $data, function ($message) use ($data) {
            $message->to($data['email'], $data['name']);
        });
        /*
         * Send mail notify admin
         * New user signed up
         * TODO: Update email admin
         */
        \Mail::send('rainlab.user::mail.new_user', $data, function ($message) use ($data) {
            $message->to('juancarlos@logimonde.com');
        });
    }


    public function onGetBalance()
    {
        $payment = new PaypalPayments;
        $result = $payment->getTransactionSearch();
        return [
            'result' => $result
        ];
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
