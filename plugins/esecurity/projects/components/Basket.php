<?php namespace Esecurity\Projects\Components;

use Auth;
use Cart;
use Cms\Classes\ComponentBase;
use Esecurity\Projects\Models\Product;


class Basket extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Basket',
            'description' => 'Basket Component'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->loadBasketInfo();
    }

    public function loadBasketInfo()
    {
        $content = Cart::content();

        $content->each(function ($row) {
            $row->slug = $row->model->slug;
        });
        $this->page['basketItems'] = $content;
        $this->page['count'] = Cart::count();
        $this->page['subtotal'] = Cart::subtotal();
    }

    public function onAddCart()
    {
        $id = post('id');
        $product = Product::whereId($id)->first();
        if ($product) {
            $item = Cart::add($id, $product->name, 1, $product->price);
            Cart::associate($item->rowId, 'Esecurity\Projects\Models\Product');
            $this->loadBasketInfo();
            return [
                'message' => \Lang::get('esecurity.projects::lang.basket.add')
            ];
        }
    }

    public function onRemoveProduct()
    {
        Cart::remove(post('row_id'));

        $this->loadBasketInfo();
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
