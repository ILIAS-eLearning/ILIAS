<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Link\Bulky;

//The Bulky Links in this example point to ilias.de
//Note the exact look of the Bulky Links is mostly defined by the
//surrounding container.
function with_tooltip()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $target = new \ILIAS\Data\URI("https://ilias.de");
    $glyph = $f->symbol()->glyph()->comment();

    $link = $f->link()->bulky($glyph, 'Link to ilias.de with Glyph', $target)
        ->withHelpTopics(
            ...$f->helpTopics("ilias", "learning management system")
        );

    return $renderer->render([
        $link
    ]);
}
