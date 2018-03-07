<?php namespace Esecurity\Projects;

use App;
use Event;
use Backend;
use System\Classes\PluginBase;
use RainLab\Translate\Classes\Translator as languageTranslator;
use Carbon\Carbon;
use RainLab\Translate\Models\Locale;
use ApplicationException;
use Illuminate\Foundation\AliasLoader;

/**
 * Projects Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Security',
            'description' => 'No description provided yet...',
            'author'      => 'Esecurity',
            'icon'        => 'icon-lock'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        // Register service providers
        App::register('\Gloudemans\Shoppingcart\ShoppingcartServiceProvider');

        // Register facades
        $facade = AliasLoader::getInstance();
        $facade->alias('Cart', '\Gloudemans\Shoppingcart\Facades\Cart');
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Esecurity\Projects\Components\Category' => 'Category',
            'Esecurity\Projects\Components\Product' => 'Product',
            'Esecurity\Projects\Components\Checkout' => 'Checkout',
            'Esecurity\Projects\Components\Basket' => 'Basket',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {

        return [
            'esecurity.projects.access_products' => [
                'tab' => 'Security',
                'label' => 'Manage Products'
            ],
            'esecurity.projects.access_categories' => [
                'tab' => 'Security',
                'label' => 'Manage Categories'
            ],
            'esecurity.projects.access_orders' => [
                'tab' => 'Security',
                'label' => 'Manage Orders'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {

        return [
            'projects' => [
                'label'       => 'Security',
                'url'         => Backend::url('esecurity/projects/products'),
                'icon'        => 'icon-lock',
                'permissions' => ['esecurity.projects.*'],
                'order'       => 200,
                'sideMenu' => [
                    'products' => [
                        'label'       => 'Products',
                        'icon'        => 'icon-video-camera',
                        'url'         => Backend::url('esecurity/projects/products'),
                        'permissions' => ['esecurity.projects.access_products']
                    ],
                    'categories' => [
                        'label'       => 'Categories',
                        'icon'        => 'icon-list',
                        'url'         => Backend::url('esecurity/projects/categories'),
                        'permissions' => ['esecurity.projects.access_categories']
                    ],
                    'orders' => [
                        'label'       => 'Orders',
                        'icon'        => 'icon-usd',
                        'url'         => Backend::url('esecurity/projects/orders'),
                        'permissions' => ['esecurity.projects.access_orders']
                    ],
                ]
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'E-security settings',
                'description' => 'Manage site settings',
                'icon' => 'icon-cog',
                'class' => 'Esecurity\Projects\Models\Settings',
                'order' => 500,
            ]
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'number' => function($number, $decimals = 2) {
                    $translator = languageTranslator::instance();
                    $lang = $translator->getLocale();

                    if ($lang == 'fr') {
                        $format = number_format($number, $decimals, ' ', ' ');
                    } else if ($lang == 'en') {
                        $format = number_format($number, $decimals, '.', ',');
                    } else {
                        $format = number_format($number, $decimals, '.', ',');
                    }
                    return $format;
                },
                'explode' => [$this, 'explodeString'],
                'phone' => [$this, 'phoneFormat'],
                'money' => function ($number, $decimals = 2) {
                    $translator = languageTranslator::instance();
                    $lang = $translator->getLocale();

                    if ($lang == 'fr') {
                        $money = number_format($number, $decimals, '.', ' ') . ' $';
                    } else if ($lang == 'en') {
                        $money = '$ ' . number_format($number, $decimals, '.', ',');
                    } else {
                        $money = '$ ' . number_format($number, $decimals, '.', ',');
                    }
                    return $money;
                },
                'mytime' => [$this, 'myTimeFormat'],
                'mydate' => [$this, 'myDateFormat'],
                'mydatelong' => [$this, 'myDateLongFormat'],
                'bold' => function ($str, $search) {
                    return str_ireplace($search, "<strong>" . $search . "</strong>", $str);
                },
                'initials' => function ($str) {
                    $words = explode(" ", $str);
                    $acronym = "";

                    foreach ($words as $w) {
                        $acronym .= $w[0];
                    }
                    return $acronym;
                },
                'blob' => function ($str) {
                    return 'data: image/png' . ';base64,' . $str;
                },
                'ccv' => function ($str) {
                    return substr($str, 0, 4) . ' ' . substr($str, 4, 4) .
                        ' ' . substr($str, 8, 4) . ' ' . substr($str, 12, 4);
                },
            ],
            'functions' => [
                // Using an inline closure
                'mytrunc' => function ($string, $length = 150, $append = "&hellip;") {
                    $string = strip_tags($string);

                    if (strlen($string) > $length) {
                        $string = wordwrap($string, $length);
                        $string = explode("\n", $string, 2);
                        $string = $string[0] . $append;
                    }

                    return $string;
                },
                'trunc' => function ($string, $length = 150, $append = "&hellip;") {
                    $string = strip_tags($string);
                    if (strlen($string) > $length) {
                        $string = substr($string, 0, $length) . $append;
                    }
                    return $string;
                },
                'nohttp' => function ($string) {
                    $string = preg_replace('#^https?://#', '', rtrim($string, '/'));
                    return $string;
                }
            ]
        ];
    }

    public function explodeString($string)
    {
        return explode(";", $string);
    }

    public function phoneFormat($phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (strlen($phoneNumber) > 10) {
            $countryCode = substr($phoneNumber, 0, strlen($phoneNumber) - 10);
            $areaCode = substr($phoneNumber, -10, 3);
            $nextThree = substr($phoneNumber, -7, 3);
            $lastFour = substr($phoneNumber, -4, 4);

            $phoneNumber = '+' . $countryCode . ' (' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
        } else if (strlen($phoneNumber) == 10) {
            $areaCode = substr($phoneNumber, 0, 3);
            $nextThree = substr($phoneNumber, 3, 3);
            $lastFour = substr($phoneNumber, 6, 4);

            $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
        } else if (strlen($phoneNumber) == 7) {
            $nextThree = substr($phoneNumber, 0, 3);
            $lastFour = substr($phoneNumber, 3, 4);

            $phoneNumber = $nextThree . '-' . $lastFour;
        }

        return $phoneNumber;
    }

    public function myDateFormat($time)
    {
        $translator = languageTranslator::instance();
        $lang = $translator->getLocale();
        $format = "";
        $timeObj = new Carbon($time);
        if ($lang == 'fr') {
            $format = "d-m-Y";
        } else if ($lang == 'en') {
            $format = "m-d-Y";
        }

        return date($format, $timeObj->getTimestamp());

    }

    public function myDateLongFormat($time)
    {
        $translator = languageTranslator::instance();
        $lang = $translator->getLocale();
        $format = "";
        $timeObj = new Carbon($time);
        if ($lang == 'fr') {
            $format = "%A, %e %B %Y";
        } else if ($lang == 'en') {
            $format = "%A %B %e, %Y";
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }

        return strftime($format, $timeObj->getTimestamp());

    }

    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }


    public function myTimeFormat($time)
    {
        $translator = languageTranslator::instance();
        $lang = $translator->getLocale();
        $format = "";
        $timeObj = new Carbon($time);
        if ($lang == 'fr') {
            $format = "G:i";
        } else if ($lang == 'en') {
            $format = "g:i a";
        }
        return date($format, $timeObj->getTimestamp());

    }
}


