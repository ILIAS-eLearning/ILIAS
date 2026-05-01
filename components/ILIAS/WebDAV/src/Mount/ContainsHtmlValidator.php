<?php

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

declare(strict_types=1);

namespace ILIAS\WebDAV\Mount;

use DOMDocument;
use Exception;
use Throwable;
use RecursiveIteratorIterator;
use ilHtmlDomNodeIterator;

class ContainsHtmlValidator
{
    public function __construct(private string $text)
    {
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
                if (strtolower((string) $element->nodeName) === 'body') {
                    continue;
                }

                if ($element->nodeType === XML_ELEMENT_NODE) {
                    return true;
                }
            }
        } catch (Exception | Throwable) {
            return false;
        }

        return false;
    }
}
