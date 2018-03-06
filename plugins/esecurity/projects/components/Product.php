<?php namespace Esecurity\Projects\Components;

use Cms\Classes\ComponentBase;
Use Esecurity\Projects\Models\Category as BaseCategory;
Use Esecurity\Projects\Models\Product as BaseProduct;
Use Esecurity\Projects\Models\Order;

class Product extends ComponentBase
{

    public $group;

    public function componentDetails()
    {
        return [
            'name'        => 'Product',
            'description' => 'Product Component'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addCss('assets/css/product.css');
        $this->addCss('assets/vendor/lightGallery/css/lightgallery.css');
        $this->addJs('assets/vendor/lightGallery/js/lightgallery.min.js');
        $this->addJs('assets/js/product.js', '1.01');
        if ($slugProduct = $this->param('product')) {
            $this->page['product'] = $product = BaseProduct::where('slug', $slugProduct)
                ->first();
            if ($slug = $this->param('category')) {
                $this->page['category'] = $category = BaseCategory::where('slug', $slug)
                    ->where('group', $product->group)->first();
            } else {
                return \Redirect::to('404');
            }
        } else {
            return \Redirect::to('404');
        }


    }

    public function setGroup($group)
    {
        return $this->group = $group;
    }

    public function onSetOrder()
    {
        $data = post();
        $order = new Order;
        $order->product_id = $data['product_id'];
        $order->costumer_name = $data['name'];
        $order->costumer_email = $data['email'];
        $order->costumer_address = $data['address'];
        $order->costumer_zip = $data['zip'];
        $order->costumer_phone = $data['phone'];

        $order->save();

        $this->sendAdminOrder($order);
        return [
            'message' => trans('esecurity.projects::lang.order.success'),
        ];
    }

    private function sendMailCustomer()
    {

    }

    private function sendAdminOrder($order)
    {
        $data['order'] = $order;
        \Mail::send('esecurity.projects::mail.admin_order', $data, function ($message) use ($data) {
            $message->to('alex.vasquez@e-security.ca', 'Alexander Vasquez');
            $message->bcc('lorajc@hotmail.com', 'Juan Carlos Lora');
        });
    }
}
