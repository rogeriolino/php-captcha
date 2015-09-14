<?php

namespace RogerioLino\Captcha;

/**
 * Mathematical captcha.
 *
 * @author Rogério Lino <rogeriolino.com>
 */
class MathCaptcha extends Captcha
{
    private $operators = ['+', '-'];

    public function __construct($width = 150, $height = 50)
    {
        parent::__construct($width, $height);
        $exp = $this->generateExpression();
        $this->setGenerateValue($exp);
        eval('$this->setAssertValue('.$exp.');');
    }

    private function generateExpression()
    {
        $total = $this->getLength() / 2;
        $exps = [];
        for ($i = 0; $i < $total; $i++) {
            $exps[] = $this->generateOperation(($i > 0) ? $exps[$i - 1] : null);
        }

        return end($exps);
    }

    private function generateOperation($prevOp = null)
    {
        $op = $this->operators[rand(0, count($this->operators) - 1)];
        $v1 = rand(0, 9);
        $v2 = rand(0, 9);
        if ($prevOp) {
            if (rand(0, 10) % 2 == 0) {
                $v1 = "($prevOp)";
            } else {
                $v2 = "($prevOp)";
            }
        }

        return "$v1 $op $v2";
    }
}
