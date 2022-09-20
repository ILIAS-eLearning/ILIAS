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
 * @author  Niels Theen <ntheen@databay.de>
 */
interface ilCertificatePlaceholderDescription
{
    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     * @return array<string, string> A [PLACEHOLDER] => 'description' map
     */
    public function getPlaceholderDescriptions(): array;

    /**
     * @return string - HTML that can used to be displayed in the GUI
     */
    public function createPlaceholderHtmlDescription(): string;
}
