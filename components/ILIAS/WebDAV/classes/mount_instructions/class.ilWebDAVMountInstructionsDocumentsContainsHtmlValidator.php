<?php

declare(strict_types=1);


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

class ilWebDAVMountInstructionsDocumentsContainsHtmlValidator
{
    private string $text;

    public function __construct(string $purified_html_content)
    {
        $this->text = $purified_html_content;
    }

    public function isValid(): bool
    {
        if (!preg_match('/<[^>]+?>/', $this->text)) {
            return false;
        }

        try {
            $dom = new DOMDocument();
            if (!$dom->loadHTML($this->text)) {
                return false;
            }

            $iter = new RecursiveIteratorIterator(
                new ilHtmlDomNodeIterator($dom),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iter as $element) {
                /** @var $element DOMNode */
                if (strtolower($element->nodeName) === 'body') {
                    continue;
                }

                if ($element->nodeType === XML_ELEMENT_NODE) {
                    return true;
                }
            }
        } catch (Exception|Throwable $e) {
            return false;
        }

        return false;
    }
}
