<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Listing\Property;

/**
 * ---
 * description: >
 *    Example of differently used properties.
 *
 * expected output: >
 *   ILIAS shows the rendered Component. The following options are showcased at least once:
 *      - Key is a text string, value is a text string
 *      - Key is not shown, value is a Learning Progress status image followed by text
 *      - Key is a Glyph, value is a (date) text string
 *      - Key is a text string, value is a long text with a show more/less toggle
 *      - Key is a text string, value is a clickable link
 *      - Key is a text string, value is a Glyph
 *
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $some_legacy_code = $f->legacy(
        $renderer->render(
            $f->symbol()->icon()->custom('./assets/images/learning_progress/in_progress.svg', 'incomplete'),
        ) . ' <strong>in progress</strong>'
    );

    $glyph_calendar = $f->symbol()->glyph()->calendar()->withLabel("date of upload");

    $props = $f->listing()->property()
        ->withProperty('Title', 'Some Title')
        ->withProperty('number', '7')
        ->withProperty(
            'status',
            $some_legacy_code,
            false
        )
        ->withProperty($glyph_calendar, "21.03.2026", false);

    $props2 = $f->listing()->property()
        ->withProperty("Description", "Heads up, this is a very long description. It is always a challenge: You have more to say, but there is so little space. And we still want this text to be shown with all the other properties. For this case we have the automatic text collapsing feature. This way we get the best of both worlds: The text doesn't expand beyond one line, but you can see the rest if you need to. A good use case is on the entity. As the entity might be used to show course entities with lengthy descriptions. Those will take up less space initially. Isn't that sweet? And the crazy thing is: It does not need JavaScript. It's pure HTML and CSS only. Isn't that nice?", false);

    $yes_checkmark = $f->symbol()->glyph()->apply()->withLabel("yes, approved");

    $props3 = $props->withItems([
        ['a', "1"],
        ['y', "25", false],
        ['link', $f->link()->standard('Goto ILIAS', 'http://www.ilias.de')],
        ['approved', $yes_checkmark],
    ]);

    return $renderer->render([
            $props,
            $props2,
            $f->divider()->horizontal(),
            $props3
    ]);
}
