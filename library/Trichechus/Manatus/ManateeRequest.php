<?php

namespace Trichechus\Manatus;

class ManateeRequest
{
    private $width;

    private $height;

    private $format;

    private $specificManatee;

    public function __construct($width, $height, $format = 'jpeg')
    {
        $this->width = $width;
        $this->height = $height;
        $this->format = $format;

    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setSpecificManatee($specificManatee)
    {
        $this->specificManatee = $specificManatee;
    }

    public function getSpecificManatee()
    {
        return $this->specificManatee;
    }
}
