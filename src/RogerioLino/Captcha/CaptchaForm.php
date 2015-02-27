<?php

namespace RogerioLino\Captcha;

/**
 * Captcha form handler
 *
 * @author Rogério Lino <rogeriolino.com>
 */
class CaptchaForm 
{

    const CAPTCHA_SESSION_ID = 'captcha_form_control';
    const FORM_INPUT_ID = 'captcha_enter';

    private $captcha;
    private $renderer;

    public function getCaptcha() {
        return $this->captcha;
    }

    public function setCaptcha(Captcha $c) {
        $this->captcha = $c;
    }

    public function getRenderer() {
        return $this->renderer;
    }

    public function setRenderer(CaptchaRenderer $r) {
        $this->renderer = $r;
    }

    public function create() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION[self::CAPTCHA_SESSION_ID] = $this;
        $html = '<div class="captcha">';
        $html .= '<input class="captcha-input" type="text" name="' . self::FORM_INPUT_ID . '" />';
        $html .= '<span class="captcha-resource">' . $this->renderer->render($this->captcha) . '</span>';
        $html .= '</div>';
        return $html;
    }

    public function match() {
        $value = (isset($_POST[self::FORM_INPUT_ID])) ? $_POST[self::FORM_INPUT_ID] : null;
        return $this->captcha->assert($value);
    }

    public static function restore() {
        @session_start();
        return (isset($_SESSION[self::CAPTCHA_SESSION_ID])) ? $_SESSION[self::CAPTCHA_SESSION_ID] : null;
    }

}