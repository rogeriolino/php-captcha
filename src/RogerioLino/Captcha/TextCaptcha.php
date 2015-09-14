<?php

namespace RogerioLino\Captcha;

/**
 * Plain text captcha.
 *
 * @author Rogério Lino <rogeriolino.com>
 */
class TextCaptcha extends Captcha
{
    const CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789';

    public function __construct($width = 150, $height = 50)
    {
        parent::__construct($width, $height);
        $text = '';
        $chars = self::CHARS;
        for ($i = 0; $i < $this->getLength(); $i++) {
            $char = $chars[rand(0, strlen($chars) - 1)];
            if (rand(0, 10) % 2 == 0) {
                $char = strtoupper($char);
            }
            $text .= $char;
        }
        $this->setGenerateValue($text);
        $this->setAssertValue($text);
    }
}
