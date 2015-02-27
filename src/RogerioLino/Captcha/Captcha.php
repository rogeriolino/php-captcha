<?php

namespace RogerioLino\Captcha;

/**
 * Abstract class Captcha
 * 
 * @author Rogério Lino <rogeriolino.com>
 */
abstract class Captcha 
{

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
