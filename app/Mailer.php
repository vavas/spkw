<?php

namespace App;

/**
 * MandrillMailer - wrap for PHP Mandrill API
 *
 * @package Mailer
 */
use Mandrill;
use Illuminate\Support\Facades\URL;

class Mailer
{

    /**
     * Send Mandrill template
     *
     * @param string $template - Mandrill template slug
     * @param User $user - User model
     *
     * @return boolean
     */
    public function sendTemplate($template, User $user)
    {
        $mandrill = new Mandrill();
        $message = array(
            'from_email' => 'support@sparkwoo.com',
            'from_name' => 'SparkWoo',
            'to' => array(
                array(
                    'email' => $user->email,
                    'name' => $user->getFirstLastName(),
                    'type' => 'to'
                )
            ),
            'important' => false,
            'track_opens' => null,
            'track_clicks' => null,
            'auto_text' => null,
            'auto_html' => null,
            'inline_css' => null,
            'url_strip_qs' => null,
            'preserve_recipients' => null,
            'view_content_link' => null,

            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
            'merge' => true,
            'merge_language' => 'mailchimp',

            'merge_vars' => $this->_get_template_content($template, $user)
        );
        try {
            $result = $mandrill->messages->sendTemplate($template, [], $message);
            if($result) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo 'Exception: ',  $e->getMessage(), "\n";
        }

    }

    /**
     * @param string $template
     * @param User $user
     * @return array
     */
    private function _get_template_content($template, User $user)
    {
        $vars = [];
            switch ($template) {
                case 'email-verification' :
                    $vars[0]['name'] = 'emailverifylink';
                    if($user->role == config('constants.USER_ROLE_BRAND')) {
                        $vars[0]['content'] = URL::to('create-new-password').'/'.$user->authToken;
                    } else {
                        $vars[0]['content'] = URL::to('confirmation-email').'/'.$user->authToken;
                    }
                    break;
                case 'reset-password' :
                    $vars[0]['name'] = 'resetpasswordlink';
                    $vars[0]['content'] = URL::to('forgot-password').'/'.$user->authToken;
                    $vars[1]['name'] = 'temporarypassword';
                    $vars[1]['content'] = $user->remember_token;
                    break;
                case 'welcome-email' :
                    $vars[0]['name'] = 'username';
                    $vars[0]['content'] = $user->getFirstLastName();
                default:
                    break;
            }

        $merge_vars = array(
            array(
                'rcpt' => $user->email,
                'vars' => $vars
                )
            );

        return $merge_vars;
    }
}
