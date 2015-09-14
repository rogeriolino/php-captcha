<?php

namespace RogerioLino\Captcha;

/**
 * Captcha Renderer interface.
 *
 * @author Rogério Lino <rogeriolino.com>
 */
interface CaptchaRenderer
{
    public function render(Captcha $c);
}
