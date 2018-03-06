<?php namespace Esecurity\Projects\Components;

use Auth;
use Cms\Classes\ComponentBase;
use ApplicationException;
use Rainlab\User\Models\User;
use Logimonde\Stock\Models\Order;
use RainLab\Translate\Classes\Translator as languageTranslator;

class PrintConfirmation extends ComponentBase
{
    public $lang;

    public $user;
    public $server;

    public function componentDetails()
    {
        return [
            'name'        => 'Print Confirmation',
            'description' => 'Print Confirmation Component'
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
        $this->server = env('APP_ENV') == 'dev' ? 'http://10.1.1.24/dev/media_stock' : 'http://www.paxstockphoto.com/';
    }

    public function onRun()
    {
        if ($token = $this->param('token')) {
            $order = Order::where('token', $token)->first();
            if ($order) {
                $this->page['order'] = $order;

                $this->page['logo'] = $this->server. 'themes/logimonde/assets/images/PAX-StockPhoto.png';
                $this->page['server'] = $this->server;
                $this->page['urlPhoto'] = $this->server . 'photo/';
                $this->page['urlOrder'] = $this->server . 'order/confirmation/';
            } else {
                return \Redirect::to('404');
            }
        } else {
            return \Redirect::to('404');
        }
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
