<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
use LibXMLError;

class EstimatedReadingTime implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private int $wordsPerMinute = 275;
    private bool $withImages;
    private bool $xmlErrorState = false;
    /** @var LibXMLError[] */
    private array $xmlErrors = [];

    public function __construct(bool $withImages)
    {
        $this->withImages = $withImages;
    }

    /**
     * @inheritDoc
     */
    public function transform($from) : int
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

        try {
            set_error_handler(static function (int $severity, string $message, string $file, int $line) : void {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            });

            $this->beginXmlLogging();

            if (!$document->loadHTML($text)) {
                throw new InvalidArgumentException(__METHOD__ . " the argument is not a parsable XHTML string.");
            }
        } catch (ErrorException $e) {
            throw new InvalidArgumentException(__METHOD__ . " the argument is not a parsable XHTML string: " . $e->getMessage());
        } finally {
            restore_error_handler();
            $this->addErrors();
            $this->endXmlLogging();
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

    private function beginXmlLogging() : void
    {
        $this->xmlErrorState = libxml_use_internal_errors(true);
        libxml_clear_errors();
    }

    private function addErrors() : void
    {
        $currentErrors = libxml_get_errors();
        libxml_clear_errors();

        $this->xmlErrors = $currentErrors;
    }

    private function endXmlLogging() : void
    {
        libxml_use_internal_errors($this->xmlErrorState);
    }

    private function xmlErrorsOccurred() : bool
    {
        return $this->xmlErrors !== [];
    }

    private function xmlErrorsToString() : string
    {
        $text = '';
        foreach ($this->xmlErrors as $error) {
            $text .= implode(',', [
                'level=' . $error->level,
                'code=' . $error->code,
                'line=' . $error->line,
                'col=' . $error->column,
                'msg=' . trim($error->message)
            ]) . "\n";
        }

        return $text;
    }
}
