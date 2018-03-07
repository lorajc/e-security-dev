<?php namespace Esecurity\Account\Components;

use Lang;
use Auth;
use Mail;
use Event;
use Flash;
use Input;
use Request;
use Redirect;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\Settings as UserSettings;
use Exception;
use RainLab\Translate\Classes\Translator as languageTranslator;

class UserRegister extends ComponentBase
{
    public $lang;

    public function componentDetails()
    {
        return [
            'name' => 'User Register',
            'description' => 'User Register Component'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'rainlab.user::lang.account.redirect_to',
                'description' => 'rainlab.user::lang.account.redirect_to_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ],
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
    {
        $translator = languageTranslator::instance();
        $this->lang = $translator->getLocale();
    }

    public function onRun()
    {
        $this->addJs('assets/js/register.js', '1.01');
    }

    /**
     * Register the user
     */
    public function onRegisterUser()
    {
        try {
            if (!UserSettings::get('allow_registration', true)) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            /*
             * Validate input
             */
            $data = post();
            $data['name'] = $data['first_name'] . ' ' . $data['last_name'];
            $data['language'] = $this->lang;
            $data['media_folder'] = str_slug($data['name'], '-') . '-' . str_random(5);

            if (!array_key_exists('password_confirmation', $data)) {
                $data['password_confirmation'] = post('password');
            }

            $rules = [
                'email' => 'required|email|between:6,255',
                'password' => 'required|between:4,255',
            ];

            if ($this->loginAttribute() == UserSettings::LOGIN_USERNAME) {
                $rules['username'] = 'required|between:2,255';
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Register user
             */
            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
            $user = Auth::register($data, $automaticActivation);

            /*
             * Automatically activated or not required, log the user in
             */
            if ($automaticActivation || !$requireActivation) {
                Auth::login($user);
            }

            //$this->sendEmailConfirmation($data);

            Flash::success(Lang::get('esecurity.account::lang.mail.welcome_user'));

            /*
             * Redirect to the intended page after successful sign in
             */
            $redirectUrl = $this->pageUrl($this->property('redirect'))
                ?: $this->property('redirect');

            if ($redirectUrl = post('redirect', $redirectUrl)) {
                return Redirect::intended($redirectUrl);
            }

        } catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }
    }

    private function sendEmailConfirmation($data)
    {
        $data['logo'] = 'http://e-security.ca/themes/esecurity/assets/images/e-security-inverse.png';

        \Mail::send('esecurity.account::mail.welcome_user_' . $data['language'], $data, function ($message) use ($data) {
            $message->to($data['email'], $data['name']);
        });
        /*
         * Send mail notify admin
         * New user signed up
         * TODO: Update email admin
         */
        \Mail::send('rainlab.user::mail.new_user', $data, function ($message) use ($data) {
            $message->to('lorajc@hotmail.com');
        });
    }


    /**
     * Returns the login model attribute.
     */
    public function loginAttribute()
    {
        return UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
    }

}