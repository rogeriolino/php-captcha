<?php

namespace RogerioLino\Captcha;

/**
 * Abstract Image renderer
 *
 * @author Rogério Lino <rogeriolino.com>
 */
abstract class AbstractImageCaptchaRenderer implements CaptchaRenderer 
{
    
    public function render(Captcha $c) {
        $dataUri = $this->encodeImage(new ImageCaptcha($c));
        return '<img width="'. $c->getWidth() .'" height="'. $c->getHeight() .'" src="' . $dataUri . '" />';
    }

    public abstract function encodeImage(ImageCaptcha $ci);
    
}