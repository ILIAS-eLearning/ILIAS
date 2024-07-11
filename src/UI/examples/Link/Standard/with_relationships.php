<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Link\Standard;

use ILIAS\UI\Component\Link\Relationship;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_relationships()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $link = $f->link()->standard("Goto ILIAS", "http://www.ilias.de")
        ->withAdditionalRelationshipToReferencedResource(Relationship::EXTERNAL)
        ->withAdditionalRelationshipToReferencedResource(Relationship::BOOKMARK);

    return $renderer->render($link);
}
