<?php

namespace Trichechus\Manatus;

use DirectoryIterator;
use Imagick;
use DOMDocument;


class ManatizerService
{
    private $manateePath;


    public function __construct($manateePath, $writePath)
    {
        $this->manateePath = $manateePath;
        $this->writePath = $writePath;
    }


    public function getManatees($format)
    {
        $manatees = [];
        $iter = new DirectoryIterator($this->manateePath.'/'.$format);
        foreach ($iter as $file) {

            if (!in_array($file->getFilename(), ['.', '..'])) {
                $manatees[] = $this->manateePath .'/'. $format. '/' . $file->getFilename();
            }
        }
        sort($manatees);
        return $manatees;
    }


    private function getPerfectManatee(ManateeRequest $request)
    {
        $manatees = $this->getManatees($request->getFormat());

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
        $imagick = new Imagick();
        $imagick->readimage($manatee);
        $imagick->cropThumbnailimage($request->getWidth(), $request->getHeight());
        $imagick->setImageFormat($request->getFormat());
        $imagick->setImageCompressionQuality(75);

        return $imagick;
    }


    private function createSvgImage(ManateeRequest $request, $manatee)
    {
        $svg = file_get_contents($manatee);

        $svgDom = new DOMDocument();
        libxml_use_internal_errors(true);
        $svgDom->loadXML($svg);
        libxml_use_internal_errors(false);

        $tmpObj = $svgDom->getElementsByTagName('svg')->item(0);
        $svgWidth = floatval($tmpObj->getAttribute('width'));
        $svgHeight = floatval($tmpObj->getAttribute('height'));

        $tmpObj->setAttribute('width', $request->getWidth());
        $tmpObj->setAttribute('height', $request->getHeight());
        $tmpObj->setAttribute('viewBox', "0 0 $svgWidth $svgHeight");

        return $svgDom->saveXML();
    }


    private function writeManatee(ManateeRequest $request, Imagick $img)
    {
        $path = $this->getPath($request);
        $img->writeimage($path . '/' . $request->getHeight() .'.'.$request->getFormat());
    }


    private function writeSvgManatee(ManateeRequest $request, $imgString)
    {
        $path = $this->getPath($request);
        file_put_contents($path . '/' . $request->getHeight() .'.'.$request->getFormat(), $imgString);

    }


    private function getPath(ManateeRequest $request)
    {
        $path = $this->writePath;

        if ($request->getSpecificManatee()) {
            $path .= '/' . $request->getSpecificManatee();
        }

        $path .= '/' . $request->getWidth();

        if (!is_dir($path)) {
            mkdir($path, 0750, true);
        }
        return $path;
    }


    public function createManatee(ManateeRequest $request)
    {
        $manatee = $this->getPerfectManatee($request);

        if($request->getFormat() === 'svg') {
            $imgString = $this->createSvgImage($request, $manatee);
            $this->writeSvgManatee($request, $imgString);
            return $imgString;

        } else {
            $img = $this->createImagick($request, $manatee);
            $this->writeManatee($request, $img);
            return $img->getImageBlob();
        }
    }

}
