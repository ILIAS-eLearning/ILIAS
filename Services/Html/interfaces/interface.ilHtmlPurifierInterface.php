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

/**
 * Interface for html sanitizing functionality
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilHtmlPurifierInterface
{
    /**
     * Filters an HTML snippet/document to be XSS-free and standards-compliant.
     */
    public function purify(string $html): string;

    /**
     * Filters an array of HTML snippets/documents to be XSS-free and standards-compliant.
     * @param string[] $htmlCollection
     * @return string[]
     * @throws InvalidArgumentException If one of the arrays element is not of tpye string
     */
    public function purifyArray(array $htmlCollection): array;
}
