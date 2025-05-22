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

namespace ILIAS\UI\examples\MainControls\Footer;

/**
 * ---
 * description: >
 *   The initial screen shows a Primary Button. When clicked, the current Page is reloaded
 *   and a fullscreen demo Page appears, which features the Footer.
 *
 * expected output: >
 *   ILIAS shows the Primary Button. After the Button is clicked, ILIAS loads a demo Page,
 *   which shows the Footer. The Footer consists of 5 distinct sections, each operable by
 *   keyboard and accessible for screen-readers.
 *
 *   (For testing/HTML validation, please click the button and validate the footer on the consecutive page.)
 * ---
 */
function base(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->wrapper()->query();
    $request_url = $DIC->http()->request()->getUri();
    $data_factory = new \ILIAS\Data\Factory();
    $refinery_factory = new \ILIAS\Refinery\Factory($data_factory, $DIC->language());

    $example_uri = $data_factory->uri($request_url->__toString());

    $pseudo_icon = $factory->symbol()->icon()->standard('', 'Pseudo Icon')->withAbbreviation('P');

    $pseudo_links = [
        $factory->link()->standard('Some Pseudo Link', '#'),
        $factory->link()->standard('Another Pseudo Link', '#'),
        $factory->link()->standard('Pseudo Link', '#'),
    ];

    $footer = $factory->mainControls()->footer();
    $footer = $footer
        ->withPermanentURL($example_uri)
        ->withAdditionalLinkGroup("Link Group 1", $pseudo_links)
        ->withAdditionalLinkGroup("Link Group 2", $pseudo_links)
        ->withAdditionalLinkGroup("Link Group 3", $pseudo_links)
        ->withAdditionalLinkGroup("Link Group 4", $pseudo_links)
        ->withAdditionalLink(...$pseudo_links)
        ->withAdditionalIcon($pseudo_icon, null)
        ->withAdditionalIcon($pseudo_icon, null)
        ->withAdditionalIcon($pseudo_icon, null)
        ->withAdditionalText(
            'Powered by ILIAS',
            'Running on green energy',
            'It\'s (swearword) huge!',
        );

    maybeRenderFooterInFullScreenMode($request, $refinery_factory, $footer);

    $open_footer_in_fullscreen = $factory->button()->primary(
        'See Footer in fullscreen',
        $example_uri . '&ui_maincontrols_footer_example=1',
    );

    return $renderer->render($open_footer_in_fullscreen);
}

function maybeRenderFooterInFullScreenMode(
    \ILIAS\HTTP\Wrapper\RequestWrapper $request,
    \ILIAS\Refinery\Factory $refinery,
    \ILIAS\UI\Component\MainControls\Footer $footer,
): void {
    if (!$request->has('ui_maincontrols_footer_example')
        || 1 !== $request->retrieve('ui_maincontrols_footer_example', $refinery->kindlyTo()->int())
    ) {
        return;
    }

    \ilInitialisation::initILIAS();

    global $DIC;

    $html = $DIC->ui()->renderer()->render(
        getFullScreenDemoPage($DIC->ui()->factory(), $footer)
    );

    $DIC->http()->saveResponse(
        $DIC->http()->response()
            ->withHeader('Content-Type', 'text/html; charset=utf-8')
            ->withBody(\ILIAS\Filesystem\Stream\Streams::ofString($html))
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}

function getFullScreenDemoPage(
    \ILIAS\UI\Factory $factory,
    \ILIAS\UI\Component\MainControls\Footer $footer,
): \ILIAS\UI\Component\Layout\Page\Page {
    return $factory->layout()->page()->standard(
        [],
        $factory->mainControls()->metaBar(),
        $factory->mainControls()->mainBar(),
        $factory->breadcrumbs([]),
        $factory->image()->responsive("assets/images/logo/HeaderIcon.svg", "ILIAS"),
        $factory->image()->responsive("assets/images/logo/HeaderIconResponsive.svg", "ILIAS"),
        './assets/images/logo/favicon.ico',
        $factory->toast()->container(),
        $footer,
        'Footer Example',
        'Footer Example',
        'ILIAS',
    )->withUIDemo(true);
}
