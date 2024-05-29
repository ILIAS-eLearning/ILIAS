<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Markdown;

use ILIAS\UI\Component\Input\Field\Markdown;

/**
 * Example show how to create and render a basic markdown field and attach it to a form.
 */
function with_restricted_syntax(): string
{
    global $DIC;

    // retrieve dependencies
    $md_renderer = new \ilUIRestrictedMarkdownPreviewRendererGUI();
    $query_wrapper = $DIC->http()->wrapper()->query();
    $inputs = $DIC->ui()->factory()->input();
    $glyphs = $DIC->ui()->factory()->symbol()->glyph();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // declare form and input
    $markdown_input = $inputs->field()->markdown($md_renderer, 'Markdown Input', 'Just a markdown input.');
    $markdown_input = $markdown_input->withAllowedActions([
        Markdown::ACTION_ITALIC,
        Markdown::ACTION_BOLD,
    ]);

    $form = $inputs->container()->form()->standard('#', [$markdown_input]);

    // please use ilCtrl to generate an appropriate link target
    // and check it's command instead of this.
    if ('POST' === $request->getMethod()) {
        $form = $form->withRequest($request);
        $data = $form->getData();
    } else {
        $data = 'no results yet.';
    }

    return
        '<pre>' . print_r($data, true) . '</pre>' .
        $renderer->render($form);
}
