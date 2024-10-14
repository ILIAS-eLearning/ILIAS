<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Panel\Report;

function with_view_controls(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $url = $DIC->http()->request()->getRequestTarget();

    $actions = $f->dropdown()->standard([
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ]);
    $current_presentation = 'simple';
    if ($request_wrapper->has('mode')) {
        $current_presentation = $request_wrapper->retrieve('mode', $refinery->kindlyTo()->string());
    }
    $presentation_options = [
        'simple' => 'Simple',
        'detailed' => 'Detailed'
    ];
    $modes = $f->viewControl()->mode(
        array_reduce(
            array_keys($presentation_options),
            static function ($carry, $item) use ($presentation_options, $url) {
                $carry[$presentation_options[$item]] = "$url&mode=$item";
                return $carry;
            },
            []
        ),
        'Presentation Mode'
    )->withActive($presentation_options[$current_presentation]);

    $content = "Just some information.";
    if ($current_presentation === 'detailed') {
        $content = "This is clearly a lot more information!";
    }

    $sub1 = $f->panel()->sub("Sub Panel Title 1", $f->legacy($content))
            ->withFurtherInformation($f->card()->standard("Card Heading")->withSections(array($f->legacy("Card Content"))));
    $sub2 = $f->panel()->sub("Sub Panel Title 2", $f->legacy($content));

    $block = $f->panel()->report("Report Title", [$sub1, $sub2])
        ->withActions($actions)
        ->withViewControls([$modes]);

    return $renderer->render($block);
}
