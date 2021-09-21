<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
interface ilCertificatePlaceholderDescription
{
    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     * @return array<string, string> A [PLACEHOLDER] => 'description' map
     */
    public function getPlaceholderDescriptions() : array;

    /**
     * @return string - HTML that can used to be displayed in the GUI
     */
    public function createPlaceholderHtmlDescription() : string;
}
