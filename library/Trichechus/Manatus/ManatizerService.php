<?php

namespace Trichechus\Manatus;

use DirectoryIterator;
use Imagick;

class ManatizerService
{

    private $manateePath;

    public function __construct($manateePath, $writePath)
    {
        $this->manateePath = $manateePath;
        $this->writePath = $writePath;
    }

    private function getManatees()
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

    private function getPerfectManatee(ManateeRequest $request)
    {
        $manatees = $this->getManatees();

        if ($request->getSpecificManatee() === null) {
            return $manatees[array_rand($manatees)];
        }

        if ($request->getSpecificManatee() === 0) {
            throw new \InvalidArgumentException("Specific manatee must be 1 or greater.");
        }

        if (!isset($manatees[$request->getSpecificManatee() - 1])) {
            throw new \InvalidArgumentException("Specific manatee #{$request->getSpecificManatee()} was not found.");
        }

        return $manatees[$request->getSpecificManatee() - 1];
    }


    private function createImagick(ManateeRequest $request, $manatee)
    {
        $imagick = new Imagick($manatee);
        $imagick->cropThumbnailimage($request->getWidth(), $request->getHeight());
        $imagick->setImageFormat("jpeg");
        $imagick->setImageCompressionQuality(50);
        return $imagick;
    }

    private function writeManatee(ManateeRequest $request, Imagick $img)
    {
        $path = $this->writePath;

        if ($request->getSpecificManatee()) {
            $path .= '/' . $request->getSpecificManatee();
        }

        $path .= '/' . $request->getWidth();

        if (!is_dir($path)) {
            mkdir($path, 0750, true);
        }

        $img->writeimage($path . '/' . $request->getHeight() . '.jpg');
    }


    public function createManatee(ManateeRequest $request)
    {
        $manatee = $this->getPerfectManatee($request);
        $img = $this->createImagick($request, $manatee);

        $this->writeManatee($request, $img);
        return $img->getImageBlob();
    }



}
