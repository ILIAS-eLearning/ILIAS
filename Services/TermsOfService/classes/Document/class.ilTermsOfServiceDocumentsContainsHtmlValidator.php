<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentsContainsHtmlValidator
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentsContainsHtmlValidator
{
    /** @var string */
    private $text;

    /**
     * ilTermsOfServiceDocumentsContainsHtmlValidator constructor.
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        if (!preg_match('/<[^>]+?>/', $this->text)) {
            return false;
        }

        try {
            $dom = new \DOMDocument();
            if (!$dom->loadHTML($this->text)) {
                return false;
            }

            $iter = new \RecursiveIteratorIterator(
                new \ilHtmlDomNodeIterator($dom),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iter as $element) {
                /** @var $element \DOMNode */
                if (in_array(strtolower($element->nodeName), ['body'])) {
                    continue;
                }

                if ($element->nodeType === \XML_ELEMENT_NODE) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
