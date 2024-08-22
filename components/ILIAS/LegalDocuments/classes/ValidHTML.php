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

namespace ILIAS\LegalDocuments;

use ilHtmlDomNodeIterator;
use DOMDocument;
use ErrorException;
use RecursiveIteratorIterator;
use LibXMLError;
use Throwable;

class ValidHTML
{
    // @see: https://gnome.pages.gitlab.gnome.org/libxml2/devhelp/libxml2-xmlerror.html enum xmlParserErrors.
    // The xmlParserErrors enum in the C API is not made available in PHP.
    private const UNKNOWN_TAG = 801;

    public function isTrue(string $string): bool
    {
        if (!preg_match('/<[^>]+?>/', $string)) {
            return false;
        }

        $error_state = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            set_error_handler(static function (int $severity, string $message, string $file, int $line): void {
                throw new ErrorException($message, $severity, $severity, $file, $line);
            });

            $dom = new DOMDocument();
            $import_succeeded = $dom->loadHTML($string);

            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($error_state);

            if (
                !$import_succeeded ||
                [] !== array_filter(
                    $errors,
                    static fn(LibXMLError $error): bool => $error->code !== self::UNKNOWN_TAG
                )
            ) {
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
        } catch (Throwable) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($error_state);
            return false;
        } finally {
            restore_error_handler();
        }
    }
}
