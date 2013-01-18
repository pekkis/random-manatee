<?php

namespace Trichechus\Manatus;

use DirectoryIterator;

use Imagick;

class ManatizerService
{

    private $manateePath;

    public function __construct($manateePath)
    {
        $this->manateePath = $manateePath;
    }

    public function getManatees()
    {
        $manatees = [];
        $iter = new DirectoryIterator($this->manateePath);
        foreach ($iter as $file) {

            if (!in_array($file->getFilename(), ['.', '..'])) {
                $manatees[] = $this->manateePath . '/' . $file->getFilename();
            }
        }

        return $manatees;
    }


    public function createManatee($width, $height)
    {
        $manatees = $this->getManatees();

        $manatee = $manatees[array_rand($manatees)];

        $imagick = new Imagick($manatee);

        $imagick->cropThumbnailimage($width, $height);
        $imagick->setImageFormat("jpeg");

        return $imagick->getImageBlob();
    }


}
