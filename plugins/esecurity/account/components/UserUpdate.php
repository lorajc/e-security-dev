<?php namespace Esecurity\Account\Components;

use Auth;
use Redirect;
use System\Models\File;
use ApplicationException;
use Cms\Classes\ComponentBase;
use Esecurity\Projects\Models\Order;

class UserUpdate extends ComponentBase
{


    public $maxSize;
    public $previewWidth;
    public $previewHeight;
    public $previewMode;
    public $previewFluid;
    public $placeholderText;

    public $user;

    /**
     * Supported file types.
     * @var array
     */
    public $fileTypes;

    /**
     * @var bool Has the model been bound.
     */
    protected $isBound = false;

    /**
     * @var bool Is the related attribute a "many" type.
     */
    public $isMulti = false;

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
            'placeholderText' => [
                'title' => 'Placeholder text',
                'description' => 'Wording to display when no image is uploaded',
                'default' => 'Click or drag images to upload',
                'type' => 'string',
            ],
            'maxSize' => [
                'title' => 'Max file size (MB)',
                'description' => 'The maximum file size that can be uploaded in megabytes.',
                'default' => '5',
                'type' => 'string',
            ],
            'fileTypes' => [
                'title' => 'Supported file types',
                'description' => 'File extensions separated by commas (,) or star (*) to allow all types.',
                'default' => '.gif,.jpg,.jpeg,.png',
                'type' => 'string',
            ],
            'previewWidth' => [
                'title' => 'Image preview width',
                'description' => 'Enter an amount in pixels, eg: 100',
                'default' => '120',
                'type' => 'string',
            ],
            'previewHeight' => [
                'title' => 'Image preview height',
                'description' => 'Enter an amount in pixels, eg: 100',
                'default' => '120',
                'type' => 'string',
            ],
            'previewMode' => [
                'title' => 'Image preview mode',
                'description' => 'Thumb mode for the preview, eg: exact, portrait, landscape, auto or crop',
                'default' => 'auto',
                'type' => 'string',
            ],
            'previewFluid' => [
                'title' => 'Fluid preview',
                'description' => 'The image should expand to fit the size of its container',
                'default' => 1,
                'type' => 'checkbox',
            ],
            'deferredBinding' => [
                'title' => 'Use deferred binding',
                'description' => 'If checked the associated model must be saved for the upload to be bound.',
                'type' => 'checkbox',
            ],
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
        $sort = isset($params['sort']) ? $params['sort'] : $this->property('sortOrder');
        $page = !is_null($page) ? $page : $this->property('pageNumber');

        $orders = Order::listOrders([
            'page' => $page,
            'sort' => $sort,
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
        $data['city_id'] = post('city');
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

    public function getThumb($image = null)
    {
        if (!$image) {
            $image = $this->getPopulated();
        }

        return $image->getThumb($this->previewWidth, $this->previewHeight, [
            'extension' => 'png',
            'mode' => $this->previewMode
        ]);
    }

    public function getCssSize($addition = 0)
    {
        $width = $this->previewWidth != 'auto'
            ? ($this->previewWidth + $addition) . 'px;'
            : null;

        $height = $this->previewHeight != 'auto'
            ? ($this->previewHeight + $addition) . 'px;'
            : null;

        if ($this->previewFluid) {
            $css = 'max-width: ' . ($width ?: 'none') . 'max-height: ' . ($height ?: 'none');
        } else {
            $css = 'width: ' . ($width ?: 'auto') . 'height: ' . ($height ?: 'auto');
        }

        return $css;
    }

    //
    // AJAX
    //

    public function onRender()
    {
        if (!$this->isBound)
            throw new ApplicationException('There is no model bound to the uploader!');

        if ($populated = $this->property('populated')) {
            $this->populated = $populated;
        }
    }

    public function onUpdateImage()
    {
        $image = $this->getPopulated();

        if (($deleteId = post('id')) && post('mode') == 'delete') {
            if ($deleteImage = $image->find($deleteId)) {
                $deleteImage->delete();
            }
        }

        $this->page['image'] = $image;
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