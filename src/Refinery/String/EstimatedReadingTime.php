<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\String;

use ErrorException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use InvalidArgumentException;
use DOMDocument;
use DOMText;
use DOMCdataSection;
use DOMXPath;

class EstimatedReadingTime implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private int $wordsPerMinute = 275;
    private int $firstImageReadingTimeInSeconds = 12;
    private bool $withImages;

    public function __construct(bool $withImages)
    {
        $this->withImages = $withImages;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!is_string($from)) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a string.");
        }

        return $this->calculate($from);
    }

    private function calculate(string $text) : int
    {
        $text = mb_convert_encoding(
            '<!DOCTYPE html><html><head><meta charset="utf-8"/></head><body>' . $text . '</body></html>',
            'HTML-ENTITIES',
            'UTF-8'
        );

        $document = new DOMDocument();

        set_error_handler(static function (int $severity, string $message, string $file, int $line, array $errcontext) : void {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        });

        try {
            if (!$document->loadHTML($text)) {
                throw new InvalidArgumentException(__METHOD__ . " the argument is not a XHTML string.");
            }
        } catch (ErrorException $e) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a XHTML string: " . $e->getMessage());
        } finally {
            restore_error_handler();
        }
        
        $numberOfWords = 0;

        $xpath = new DOMXPath($document);
        $textNodes = $xpath->query('//text()');
        if ($textNodes->length > 0) {
            foreach ($textNodes as $textNode) {
                /** @var DOMText|DOMCdataSection $textNode */
                if ($textNode instanceof DOMCdataSection) {
                    continue;
                }

                $wordsInContent = array_filter(preg_split('/\s+/', $textNode->textContent));

                $wordsInContent = array_filter($wordsInContent, static function (string $word) : bool {
                    return preg_replace('/^\pP$/u', '', $word) !== '';
                });
                
                $numberOfWords += count($wordsInContent);
            }
        }
        
        if ($this->withImages) {
            $imageNodes = $document->getElementsByTagName('img');
            $numberOfWords += $this->calculateWordsForImages($imageNodes->length);
        }

        $readingTime = ceil($numberOfWords / $this->wordsPerMinute);
        
        return (int) $readingTime;
    }

    /**
     * @see https://blog.medium.com/read-time-and-you-bc2048ab620c
     * @param int $numberOfImages
     * @return float The calculated reading time for the passed number of images translated to words
     */
    private function calculateWordsForImages(int $numberOfImages) : float
    {
        $time = 0.0;

        for ($i = 1; $i <= $numberOfImages; $i++) {
            if ($i >= 10) {
                $time += 3 * ($this->wordsPerMinute / 60);
            } else {
                $time += (12 - ($i - 1)) * ($this->wordsPerMinute / 60);
            }
        }

        return $time;
    }
}
