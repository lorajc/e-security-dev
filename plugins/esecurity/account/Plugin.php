<?php namespace Esecurity\Account;

use Yaml;
use File;
use Auth;
use Mail;
use Event;
use Backend;
use System\Classes\PluginBase;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\User\Models\User as UserModel;

/**
 * Account Plugin Information File
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
            'name'        => 'E-security Account',
            'description' => 'No description provided yet...',
            'author'      => 'E-security',
            'icon'        => 'icon-user'
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
        $this->extendUserModel();
        $this->extendUserController();
    }

    private function extendUserModel()
    {
        UserModel::extend(function($model)
        {

            $model->addFillable([
                'first_name',
                'last_name',
                'company',
                'title',
                'phone',
                'mobile',
                'street_addr',
                'language',
                'zip',
            ]);

            $model->addDynamicMethod('getLanguageOptions', function ($query, $options) {
                return ['en' => 'English', 'fr' => 'Français'];
            });

        });
    }

    private function extendUserController()
    {

        UsersController::extendFormFields(function($widget) {
            // Prevent extending of related form instead of the intended User form
            if (!$widget->model instanceof UserModel) {
                return;
            }

            $configFile = __DIR__ . '/config/profile_fields.yaml';
            $config = Yaml::parse(File::get($configFile));
            $widget->addTabFields($config);
        });

        UsersController::extendListColumns(function($list, $model) {

            if (!$model instanceof UserModel)
                return;

            $list->addColumns([
                'is_activated' => [
                    'label' => 'Activated',
                    'type' => 'switch',
                ]
            ]);

        });
    }
    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Esecurity\Account\Components\UserUpdate' => 'UserUpdate',
            'Esecurity\Account\Components\UserRegister' => 'UserRegister',
            'Esecurity\Account\Components\UserPasswordReset' => 'UserPasswordReset',
            'Esecurity\Account\Components\UserLogin' => 'UserLogin',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

    }

}
