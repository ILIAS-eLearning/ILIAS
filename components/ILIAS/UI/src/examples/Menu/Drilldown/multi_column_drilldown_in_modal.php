<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Menu\Drilldown;

use ILIAS\UI\Implementation\Component\ReplaceSignal;

function multi_column_drilldown_in_modal()
{
    /**
        0 Animal of the year
        1    Switzerland
        1.1        Riverine Amphipod (gammarus fossarum)
        1.2        Wildcat
        1.2.1           European Wildcat
        1.2.2           African Wildcat
        2    Germany
        2.1        Otter
        2.2        Mole
                   --divider--
        2.3        Deer
    */


    global $DIC;
    /** @var ILIAS\UI\Factory $f */
    $f = $DIC->ui()->factory();
    /** @var ILIAS\UI\Renderer $r */
    $r = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $url = $_SERVER['REQUEST_URI'];

    if ($request_wrapper->has('content')) {
        renderModalAsync(
            $request_wrapper->retrieve(
                'content',
                $refinery->kindlyTo()->string()
            )
        );
    }

    $drilldown_modal = $f->modal()->roundtrip(
        'My Modal',
        []
    );

    $drilldown_modal = $drilldown_modal->withAsyncRenderUrl(
        $url . '&content=drilldown&replaceSignal=' . $drilldown_modal->getReplaceSignal()->getId()
    );

    $button = $f->button()->standard('Open Animals', $drilldown_modal->getShowSignal());

    return $r->render([
        $button,
        $drilldown_modal
    ]);
}

function renderModalAsync(string $content): void
{
    global $DIC;
    /** @var ILIAS\UI\Factory $f */
    $f = $DIC->ui()->factory();
    /** @var ILIAS\UI\Renderer $r */
    $r = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $url = $_SERVER['REQUEST_URI'];

    $signal_id = "";
    if ($request_wrapper->has('replaceSignal')) {
        $signal_id = $request_wrapper->retrieve('replaceSignal', $refinery->kindlyTo()->string());
    }
    $replace_signal = new ReplaceSignal($signal_id);

    switch ($content) {
        case 'image':
            $image = $f->image()->responsive('components/ILIAS/UI/src/examples/Image/mountains.jpg', 'Some mountains in the dusk');
            $replace_signal_with_url = $replace_signal->withAsyncRenderUrl(
                $url . '&content=drilldown&replaceSignal=' . $replace_signal->getId()
            );
            $button = $f->button()->standard('Back to Drilldown', '')->withOnClick($replace_signal_with_url);
            echo $r->renderAsync(
                $f->modal()->roundtrip(
                    'My Modal',
                    [$image]
                )->withActionButtons([$button])
            );
            exit;
        default:
            $ico = $f->symbol()->icon()->standard('', '')->withSize('small')->withAbbreviation('+');
            $button = $f->button()->bulky($ico->withAbbreviation('>'), 'Modal', '')
                ->withOnClick($replace_signal->withAsyncRenderUrl($url . '&content=image&replaceSignal=' . $replace_signal->getId()));

            $uri = new \ILIAS\Data\URI('https://ilias.de');
            $link = $f->link()->bulky($ico->withAbbreviation('>'), 'Link', $uri);
            $divider = $f->divider()->horizontal();

            $items = [
                $f->menu()->sub('Switzerland', [
                    $f->menu()->sub('Riverine Amphipod', [$button, $link]),
                    $f->menu()->sub('Wildcat', [
                        $f->menu()->sub('European Wildcat', [$button, $link]),
                        $f->menu()->sub('African Wildcat', [$button, $link, $divider, $link])
                    ]),
                    $button,
                    $link
                ]),

                $f->menu()->sub('Germany', [
                    $f->menu()->sub('Otter', [$button, $link]),
                    $f->menu()->sub('Mole', [$button, $link]),
                    $divider,
                    $f->menu()->sub('Deer', [$button, $link])
                ])
            ];

            echo $r->renderAsync(
                $f->modal()->roundtrip(
                    'My Modal',
                    [$f->menu()->drilldown('Animal of the year', $items)]
                )
            );
            exit;
    }
}
