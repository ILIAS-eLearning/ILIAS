<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Tag;

function with_tooltip()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $tag = $f->button()->tag("simple tag", "#")
        ->withEngagedState(true)
        ->withHelpTopics(
            ...$f->helpTopics("ilias", "learning management system")
        );

    $possible_relevances = array(
        $tag::REL_MID,
        $tag::REL_HIGH
    );

    foreach ($possible_relevances as $w) {
        $buffer[] = $renderer->render($tag->withRelevance($w));
    }

    return implode(' ', $buffer);
}
