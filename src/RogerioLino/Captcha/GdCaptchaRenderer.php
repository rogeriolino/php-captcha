<?php

namespace RogerioLino\Captcha;

/**
 * PHP GD Renderer (png format).
 *
 * @author Rogério Lino <rogeriolino.com>
 */
class GdCaptchaRenderer extends AbstractImageCaptchaRenderer
{
    private $font = '/fonts/DroidSans.ttf';

    public function __construct()
    {
        // checking gd
        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            throw new Exception('PHP Gd library not found');
        }
        $this->font = __DIR__.$this->font;
    }

    public function encodeImage(ImageCaptcha $ci)
    {
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
        $data = fread(fopen($temp, 'r'), filesize($temp));
        $base64 = base64_encode($data);
        imagedestroy($image);
        unlink($temp);

        return 'data:image/png;base64,'.$base64;
    }

    private function toHex($str)
    {
        return str_replace('#', '0x', $str);
    }
}
