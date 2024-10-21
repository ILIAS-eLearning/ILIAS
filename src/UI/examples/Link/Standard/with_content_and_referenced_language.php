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

namespace ILIAS\UI\examples\Link\Standard;

use ILIAS\Data\Factory as DataFactory;

/**
 * ---
 * description: >
 *   Example for rendering a standard link including content and a referenced language
 *
 * expected output: >
 *   ILIAS shows a link with thte title "Abrir ILIAS". Clicking the link opens the website www.ilias.de in the same
 *   browser window. If possible use the Developer Tools to check if the hreflang ("de") and lang tags ("es") were set
 *   correctly.
 * ---
 */
function with_content_and_referenced_language()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $data_factaory = new DataFactory();

    $link = $f->link()->standard("Abrir ILIAS", "http://www.ilias.de")
        ->withLanguageOfReferencedContent($data_factaory->languageTag("de"))
        ->withContentLanguage($data_factaory->languageTag("es"));
    return $renderer->render($link);
}
