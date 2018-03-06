<?php namespace Esecurity\Projects\Components;

use Cms\Classes\ComponentBase;
Use Esecurity\Projects\Models\Category as BaseCategory;

class Category extends ComponentBase
{
    public $group;

    public function componentDetails()
    {
        return [
            'name'        => 'Category',
            'description' => 'Category Component'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addJs('assets/js/category.js', '1.01');
        if ($slug = $this->param('category')) {
            $this->page['category'] = $category = BaseCategory::where('slug', $slug)
                ->where('group', $this->group)->first();
        }
    }

    public function setGroup($group)
    {
        return $this->group = $group;
    }
}
