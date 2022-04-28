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

/**
 * Class ilTermsOfServiceDocumentsContainsHtmlValidator
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentsContainsHtmlValidator
{
    private string $text;
    private bool $xmlErrorState = false;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    private function beginXmlLogging() : void
    {
        $this->xmlErrorState = libxml_use_internal_errors(false);
    }

    private function endXmlLogging() : void
    {
        libxml_use_internal_errors($this->xmlErrorState);
    }

    public function isValid() : bool
    {
        if (!preg_match('/<[^>]+?>/', $this->text)) {
            return false;
        }

        try {
            $this->beginXmlLogging();

            $dom = new DOMDocument();
            if (!$dom->loadHTML($this->text)) {
                return false;
            }

            $iter = new RecursiveIteratorIterator(
                new ilHtmlDomNodeIterator($dom),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iter as $element) {
                /** @var DOMNode $element */
                if (strtolower($element->nodeName) === 'body') {
                    continue;
                }

                if ($element->nodeType === XML_ELEMENT_NODE) {
                    return true;
                }
            }

            return false;
        } catch (Throwable $e) {
            return false;
        } finally {
            $this->endXmlLogging();
        }
    }
}
