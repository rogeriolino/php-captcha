<?php

namespace RogerioLino\Captcha;

/**
 * Plain text renderer.
 *
 * @author Rogério Lino <rogeriolino.com>
 */
class PlainTextCaptchaRenderer implements CaptchaRenderer
{
    public function render(Captcha $c)
    {
        return $c->getGenerateValue();
    }
}
