<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for html sanitizing functionality
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilHtmlPurifierInterface
{
    /**
     * Filters an HTML snippet/document to be XSS-free and standards-compliant.
     * @param string $html
     * @return string
     */
    public function purify(string $html) : string;

    /**
     * Filters an array of HTML snippets/documents to be XSS-free and standards-compliant.
     * @param string[] $htmlCollection
     * @return string[]
     */
    public function purifyArray(array $htmlCollection) : array;
}