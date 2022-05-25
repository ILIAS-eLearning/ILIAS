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
    private const LIBXML_CODE_HTML_UNKNOWN_TAG = 801;

    private string $text;
    private bool $xmlErrorState = false;
    /** @var LibXMLError[] */
    private array $xmlErrors = [];

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function isValid() : bool
    {
        if (!preg_match('/<[^>]+?>/', $this->text)) {
            return false;
        }

        try {
            set_error_handler(static function (int $severity, string $message, string $file, int $line) : void {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            });

            $this->beginXmlLogging();

            $dom = new DOMDocument();
            $import_succeeded = $dom->loadHTML($this->text);

            $this->endXmlLogging();

            if (!$import_succeeded || $this->xmlErrorsOccured()) {
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
            $this->endXmlLogging();
            return false;
        } finally {
            restore_error_handler();
        }
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
        $this->addErrors();

        libxml_use_internal_errors($this->xmlErrorState);
    }

    /**
     * @return LibXMLError[]
     */
    private function relevantXmlErrors() : array
    {
        return array_filter($this->xmlErrors, static function (LibXMLError $error) : bool {
            return $error->code !== self::LIBXML_CODE_HTML_UNKNOWN_TAG;
        });
    }

    private function xmlErrorsOccured() : bool
    {
        return $this->relevantXmlErrors() !== [];
    }
}
