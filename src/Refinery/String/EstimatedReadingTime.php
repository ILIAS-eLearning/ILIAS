<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;

/**
 * Class EstimatedReadingTime
 * @package ILIAS\Refinery\String
 */
class EstimatedReadingTime implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /** @var int  */
    private $wordsPerMinute = 275;

    /** @var int */
    private $firstImageReadingTimeInSeconds = 12;
    
    /** @var bool */
    private $withImages = false;

    /**
     * ReadingTime constructor.
     * @param bool $withImages
     */
    public function __construct(bool $withImages)
    {
        $this->withImages = $withImages;
    }

    /**
     * @inheritdoc
     */
    public function transform($from) {
        if (!is_string($from)) {
            throw new \InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        return $this->calculate($from);
    }

    /**
     * @param string $text
     * @return int
     */
    private function calculate(string $text) : int
    {
        $text = mb_convert_encoding(
            '<!DOCTYPE html><html><head><meta charset="utf-8"/></head><body>' . $text . '</body></html>',
            'HTML-ENTITIES',
            'UTF-8'
        );

        $document = new \DOMDocument();
        if (!@$document->loadHTML($text)) {
            throw new \InvalidArgumentException(__METHOD__ . " the argument is not a XHTML string.");
        }
        
        $numberOfWords = 0;

        $xpath = new \DOMXPath($document);
        $textNodes = $xpath->query('//text()');
        if ($textNodes->length > 0) {
            foreach ($textNodes as $textNode) {
                /** @var \DOMText $textNode */
                $wordsInContent = array_filter(preg_split( '/\s+/', $textNode->textContent));
                
                $numberOfWords += count($wordsInContent);
            }
        }
        
        $imageNodes = $document->getElementsByTagName('img');
        
        if ($this->withImages) {
            $numberOfWords += $this->calculateTimeForImages($imageNodes->length);
        }

        $readingTime = ceil($numberOfWords / $this->wordsPerMinute);
        
        return (int) $readingTime;
    }

    /**
     * @param int $numberOfImages
     * @see https://blog.medium.com/read-time-and-you-bc2048ab620c
     * @return float The calculated reading time for the passed number of images translated to words
     */
    private function calculateTimeForImages(int $numberOfImages) : float
    {
        $time = 0.0;

        for ($i = 1; $i <= $numberOfImages; $i++) {
            if ($i >= 10) {
                $time += 3 * ((int) $this->wordsPerMinute / 60);
            } else {
                $time += (12 - ($i - 1)) * ((int) $this->wordsPerMinute / 60);
            }
        }

        return $time;
    }
}