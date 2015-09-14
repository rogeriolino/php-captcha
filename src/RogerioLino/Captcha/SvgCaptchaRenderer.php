<?php

namespace RogerioLino\Captcha;

/**
 * Inline Svg renderer.
 *
 * @author Rogério Lino <rogeriolino.com>
 */
class SvgCaptchaRenderer extends AbstractImageCaptchaRenderer
{
    public function encodeImage(ImageCaptcha $ci)
    {
        $w = $ci->getCaptcha()->getWidth();
        $h = $ci->getCaptcha()->getHeight();
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.$w.'" height="'.$h.'">';
        $svg .= '<rect width="'.$w.'" height="'.$h.'" style="fill:'.$ci->getBackgroundColor().';stroke-width:1;stroke:'.$ci->getBorderColor().'"/>';
        $lines = $ci->getLines();
        foreach ($lines as $line) {
            $svg .= '<line x1="'.$line['x1'].'" y1="'.$line['y1'].'" x2="'.$line['x2'].'" y2="'.$line['y2'].'" style="stroke:'.$line['color'].';stroke-width:'.$line['width'].'"/>';
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
            $style = $fixedStyle.'font-size:'.$fontSize.'pt;';
            $transform = "translate($x, $y) rotate($angle) translate(-$x, -$y)";
            $svg .= '<text x="'.$x.'" y="'.$y.'" fill="'.$color.'" style="'.$style.'" transform="'.$transform.'">'.$text[$i].'</text>';
            $x += $spacing + ($this->isChar($text[$i]) ? $fontSize : $fontSize / 2);
        }
        $svg .= '</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function isChar($c)
    {
        return strpos(TextCaptcha::CHARS, $c) > -1;
    }
}
