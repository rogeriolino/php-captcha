<?php

namespace RogerioLino\Captcha;

/**
 * Captcha used in AbstractImageCaptchaRenderer subclasses
 *
 * @author Rogério Lino <rogeriolino.com>
 */
class ImageCaptcha 
{

    private $captcha;
    private $lines;
    private $borderColor;
    private $backgroundColor;
    private $textColor;
    private $text;

    public function __construct(Captcha $c) {
        $this->captcha = $c;
        // TODO: random lines
        $this->lines = array(
            array('x1' => 0, 'y1' => $c->getWidth() / 2, 'x2' => $c->getWidth(), 'y2' => $c->getHeight() / 2, 'color' => '#00ff00', 'width' => 1),
            array('x1' => 0, 'y1' => 10, 'x2' => $c->getWidth(), 'y2' => $c->getHeight() / 1.3, 'color' => '#cccccc', 'width' => 2),
            array('x1' => 0, 'y1' => $c->getHeight() / 2, 'x2' => $c->getWidth(), 'y2' => $c->getHeight() / 3, 'color' => '#cccccc', 'width' => 2)
        );
        $this->fontSize = 12;
        $this->borderColor = '#666666';
        $this->backgroundColor = '#f1f1f1';
        $this->textColor = '#333333';
        $this->text = $c->getGenerateValue();
    }

    public function getCaptcha() {
        return $this->captcha;
    }

    public function getLines() {
        return $this->lines;
    }

    public function getBorderColor() {
        return $this->borderColor;
    }

    public function getBackgroundColor() {
        return $this->backgroundColor;
    }

    public function getTextColor() {
        return $this->textColor;
    }

    public function getText() {
        return $this->text;
    }
    
    public function randColor() {
        $char = function() {
            $hex = '0123456789a'; // avoiding bright colors (bcdef)
            return $hex[rand(0, strlen($hex) - 1)];
        };
        return '#' . $char() . $char() . $char() . $char() . $char() . $char();
    }
    
    public function randAngle() {
        return rand(-10, 10);
    }

}