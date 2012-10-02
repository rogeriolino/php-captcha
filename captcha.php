<?php
/*
 * PHP Captcha
 * @author rogeriolino
 */

/**
 * Abstract class Captcha
 */
abstract class Captcha {

    private $width;
    private $height;
    private $length;
    private $fontSize = 20;
    private $caseSensitive = true;
    private $assertValue;
    private $generateValue;

    public function __construct($width, $height, $length = 4) {
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setLength($length);
    }

    public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
    }

    public function getLength() {
        return $this->length;
    }

    public function setLength($length) {
        $this->length = $length;
    }
    
    public function getCaseSensitive() {
        return $this->caseSensitive;
    }
    
    public function setCaseSensitive($caseSensitive) {
        $this->caseSensitive = $caseSensitive;
    }
    
    public function getFontSize() {
        return $this->fontSize;
    }
    
    public function setFontSize($fontSize) {
        $this->fontSize = $fontSize;
    }

    public function getGenerateValue() {
        return $this->generateValue;
    }

    public function setGenerateValue($value) {
        $this->generateValue = $value;
    }

    public function getAssertValue() {
        return $this->assertValue;
    }

    public function setAssertValue($value) {
        $this->assertValue = $value;
    }

    public function assert($submittedValue) {
        if (!$this->caseSensitive) {
            $submittedValue = strtolower($submittedValue);
            $this->assertValue = strtolower($this->assertValue);
        }
        return "{$this->assertValue}" === "$submittedValue";
    }

}

/**
 * Mathematical captcha
 */
class MathCaptcha extends Captcha {

    private $operators = array('+', '-');

    public function __construct($width = 150, $height = 50) {
        parent::__construct($width, $height);
        $exp = $this->generateExpression();
        $this->setGenerateValue($exp);
        eval('$this->setAssertValue(' . $exp . ');');
    }

    private function generateExpression() {
        $total = $this->getLength() / 2;
        $exps = array();
        for ($i = 0; $i < $total; $i++) {
            $exps[] = $this->generateOperation(($i > 0) ? $exps[$i - 1] : null);
        }
        return end($exps);
    }

    private function generateOperation($prevOp = null) {
        $op = $this->operators[rand(0, sizeof($this->operators) - 1)];
        $v1 = rand(0, 9);
        $v2 = rand(0, 9);
        if ($prevOp) {
            if (rand(0,10) % 2 == 0) {
                $v1 = "($prevOp)";
            } else {
                $v2 = "($prevOp)";
            }
        }
        return "$v1 $op $v2";
    }

}

/**
 * Plain text captcha
 */
class TextCaptcha extends Captcha {

    const CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789';

    public function __construct($width = 150, $height = 50) {
        parent::__construct($width, $height);
        $text = '';
        $chars = self::CHARS;
        for ($i = 0; $i < $this->getLength(); $i++) {
            $char = $chars[rand(0, strlen($chars) - 1)];
            if (rand(0,10) % 2 == 0) {
                $char = strtoupper($char);
            }
            $text .= $char;
        }
        $this->setGenerateValue($text);
        $this->setAssertValue($text);
    }

}

/*
 * Captcha Renderer interface
 */
interface CaptchaRenderer {
    
    public function render(Captcha $c);

}

/**
 * Plain text renderer
 */
class PlainTextCaptchaRenderer implements CaptchaRenderer {

    public function render(Captcha $c) {
        return $c->getGenerateValue();
    }

}

/**
 * Abstract Image renderer
 */
abstract class CaptchaImageRenderer implements CaptchaRenderer {

    public function render(Captcha $c) {
        $dataUri = $this->encodeImage(new CaptchaImage($c));
        return '<img width="'. $c->getWidth() .'" height="'. $c->getHeight() .'" src="' . $dataUri . '" />';
    }

    public abstract function encodeImage(CaptchaImage $ci);

}

/**
 * PHP GD Renderer (png)
 */
class GdCaptchaRenderer extends CaptchaImageRenderer {

    private $font = '/fonts/DroidSans.ttf';

    public function __construct() {
        // checking gd
        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            throw new Exception('PHP Gd library not found');
        }
        $this->font = __DIR__ . $this->font;
    }

    public function encodeImage(CaptchaImage $ci) {
        $w = $ci->getCaptcha()->getWidth();
        $h = $ci->getCaptcha()->getHeight();
        $image = imagecreatetruecolor($w, $h);
        imagefilledrectangle($image, 0, 0, $w, $h, $this->toHex($ci->getBackgroundColor()));
        imagerectangle($image, 0, 0, $w - 1, $h - 1, $this->toHex($ci->getBorderColor())); // border
        $lines = $ci->getLines();
        foreach ($lines as $line) {
            $color = $this->toHex($line['color']);
            imageline($image, $line['x1'], $line['y1'], $line['x2'], $line['y2'], $color);
        }
        $fontSize = $ci->getCaptcha()->getFontSize();
        $box = imagettfbbox($fontSize, 0, $this->font, '0');
        $spacing = ($ci->getCaptcha() instanceof MathCaptcha) ? 0 : 5;
        $text = $ci->getText();
        $x = ($w - ($fontSize * strlen(str_replace(' ', '', $text)))) / 2;
        $y = (($h / 2) - (($box[3] - $box[5]) / 2)) + $fontSize;
        for ($i = 0; $i < strlen($text); $i++) {
            $angle = $ci->randAngle();
            $color = $this->toHex($ci->randColor());
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $color, $this->font, $text[$i]);
            $x += $spacing + ($box[2] - $box[0]);
        }
        $temp = tempnam(sys_get_temp_dir(), 'captcha');
        imagepng($image, $temp);
        $data = fread(fopen($temp, "r"), filesize($temp));
        $base64 = base64_encode($data);
        imagedestroy($image);
        unlink($temp);
        return 'data:image/png;base64,' . $base64;
    }

    private function toHex($str) {
        return str_replace('#', '0x', $str);
    }

}

/**
 * Inline Svg renderer
 */
class SvgCaptchaRenderer extends CaptchaImageRenderer {

    public function encodeImage(CaptchaImage $ci) {
        $w = $ci->getCaptcha()->getWidth();
        $h = $ci->getCaptcha()->getHeight();
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'. $w .'" height="'. $h .'">';
        $svg .= '<rect width="'. $w .'" height="'. $h .'" style="fill:'. $ci->getBackgroundColor() .';stroke-width:1;stroke:'. $ci->getBorderColor() .'"/>';
        $lines = $ci->getLines();
        foreach ($lines as $line) {
            $svg .= '<line x1="'. $line['x1'] .'" y1="'. $line['y1'] .'" x2="'. $line['x2'] .'" y2="'. $line['y2'] .'" style="stroke:'. $line['color'] .';stroke-width:'. $line['width'] .'"/>';
        }
        $text = $ci->getText();
        $fontSize = $ci->getCaptcha()->getFontSize();
        $x = ($w - ($fontSize * strlen(str_replace(' ', '', $text)))) / 2;
        $y = $h / 2;
        $spacing = ($ci->getCaptcha() instanceof MathCaptcha) ? 0 : 7;
        $fixedStyle = 'dominant-baseline: central;font-family:Arial;letter-spacing:0px;';
        for ($i = 0; $i < strlen($text); $i++) {
            $angle = $ci->randAngle();
            $color = $ci->randColor();
            $style = $fixedStyle . 'font-size:'. $fontSize .'pt;';
            $transform = "translate($x, $y) rotate($angle) translate(-$x, -$y)";
            $svg .= '<text x="'. $x .'" y="'. $y .'" fill="'. $color .'" style="' . $style . '" transform="'. $transform .'">' . $text[$i] . '</text>';
            $x += $spacing + ($this->isChar($text[$i]) ? $fontSize : $fontSize / 2);
        }
        $svg .= '</svg>';
        return 'data:image/svg+xml;base64,'. base64_encode($svg);
    }
    
    private function isChar($c) {
        return strpos(TextCaptcha::CHARS, $c) > -1;
    }

}

/**
 * Captcha Image used in CaptchaImageRenderer subclasses
 */
class CaptchaImage {

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


/**
 * Captcha form handler
 */
class CaptchaForm {

    const SESSION_CAPTCHA_ID = 'captcha_form_control';
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
        @session_start();
        $_SESSION[self::SESSION_CAPTCHA_ID] = $this;
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
        return (isset($_SESSION[self::SESSION_CAPTCHA_ID])) ? $_SESSION[self::SESSION_CAPTCHA_ID] : null;
    }

}