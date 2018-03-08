<?php namespace Esecurity\Account\Components;

use Auth;
use Redirect;
use System\Models\File;
use ApplicationException;
use Cms\Classes\ComponentBase;
use Esecurity\Projects\Models\Order;

class UserUpdate extends ComponentBase
{

    public $user;

    public function componentDetails()
    {
        return [
            'name' => 'User Update',
            'description' => 'User Update Component'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title' => 'Page number',
                'description' => 'This value is used to determine what page the user is on.',
                'type' => 'string',
                'default' => '{{ :page }}',
            ],
            'itemsPerPage' => [
                'title' => 'Items per page',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format of the posts per page value',
                'default' => '10',
            ],
            'sortOrder' => [
                'title' => 'Items order',
                'description' => 'Attribute on which the items should be ordered',
                'type' => 'dropdown',
                'default' => 'created_at desc'
            ],
        ];
    }

    public function getSortOrderOptions()
    {
        return Order::$allowedSortingOptions;
    }

    public function getRedirectOptions()
    {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
    {
        $this->user = $this->user();

    }

    public function onRun()
    {
        $this->loadAssets();
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->page['orders'] = $this->getOrders();

    }

    private function getOrders($page = null)
    {

        $page = !is_null($page) ? $page : $this->property('pageNumber');

        $orders = Order::listOrders([
            'page' => $page,
            'perPage' => $this->property('itemsPerPage'),
            'user' => $this->user->id
        ]);
        return $orders;
    }

    private function loadAssets()
    {

        $this->addCss('/plugins/esecurity/account/assets/css/profile.css');

        $this->addJs('/plugins/esecurity/account/assets/js/user.js');
    }

    public function onLoadPagination()
    {
        $page = post('page');
        $this->page['orders'] = $this->getOrders($page);
    }

    /**
     * Update the user
     */
    public function onUpdate()
    {

        $user = $this->model;

        $data = post();
        $data['city'] = post('city');
        $data['state_id'] = post('state');
        $data['country_id'] = post('country');
        $user->fill($data);
        $user->save(null, post('_session_key'));

        /*
         * Password has changed, reauthenticate the user
         */
        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        \Flash::success(\Lang::get('esecurity.account::lang.user.update'));

        $redirectUrl = $this->pageUrl($this->property('redirect'));

        if ($redirectUrl = post('redirect', $redirectUrl))
            return Redirect::intended($redirectUrl);
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