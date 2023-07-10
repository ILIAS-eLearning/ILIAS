<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Link\Standard;
use ILIAS\Data\Factory as DataFactory;

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
