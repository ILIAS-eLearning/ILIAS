<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Breadcrumbs;

/**
 * ---
 * description: >
 *   Example showing how to construct Breadcrumbs with an array of Links
 *   and extending the Breadcrumbs afterwards.
 *
 * expected output: >
 *   ILIAS shows two rows of clickable links separated by simple arrows (>).
 *   The second row has one more entry than the first one.
 * ---
 */
function breadcrumbs()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $crumbs = array(
        $f->link()->standard("entry1", '#'),
        $f->link()->standard("entry2", '#'),
        $f->link()->standard("entry3", '#'),
        $f->link()->standard("entry4", '#')
    );

    $bar = $f->breadcrumbs($crumbs);

    $bar_extended = $bar->withAppendedItem(
        $f->link()->standard("entry5", '#')
    );

    return $renderer->render($bar)
        . $renderer->render($bar_extended);
}
