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

namespace ILIAS\UI\examples\MainControls\ModeInfo;

use ILIAS\DI\Container;

/**
 * ---
 * expected output: >
 *   ILIAS shows a button "See UI in fullscreen-mode".
 *   When clicked, a new page is shown with
 *   - only one entry in the mainbar
 *   - only the help-glyph in the metabar
 *   - very(!) little content
 *   - and a colored frame around the entire page.
 *   On the top of the frame, there is colored area "Active Mode Info"
 *   with a close-glyph. Clicking the close-glyph will return to the
 *   UI documentation.
 * ---
 */
function modeinfo(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $icon = $f->symbol()->icon()->standard('root', '')->withSize('large');
    $target = new \ILIAS\Data\URI(
        $DIC->http()->request()->getUri()->__toString() . '&new_mode_info=' . MODE_INFO_ACTIVE
    );
    return $renderer->render([
        $f->divider()->horizontal(),
        $f->link()->bulky($icon, 'See UI in fullscreen-mode', $target),
        $f->legacy()->content('<p><b>press the link above to init a page with Mode Info</b></p><p><br/></p>'),
        $f->divider()->horizontal()
    ]);
}

const MODE_INFO_ACTIVE = 2;
const MODE_INFO_INACTIVE = 1;

global $DIC;
$request_wrapper = $DIC->http()->wrapper()->query();
$refinery = $DIC->refinery();

if ($request_wrapper->has('new_mode_info')
    && $request_wrapper->retrieve('new_mode_info', $refinery->kindlyTo()->int()) === MODE_INFO_ACTIVE
) {
    \ilInitialisation::initILIAS();
    echo(renderModeInfoFullscreenMode($DIC));
    exit();
}

function renderModeInfoFullscreenMode(\ILIAS\DI\Container $dic)
{
    $f = $dic->ui()->factory();
    $data_factory = new \ILIAS\Data\Factory();
    $renderer = $dic->ui()->renderer();

    $panel_content = $f->legacy()->content("Mode Info is Active");
    $slate = $f->mainControls()->slate()->legacy(
        "Mode Info Active",
        $f->symbol()->glyph()->notification(),
        $f->legacy()->content("Things todo when special Mode is active")
    );

    $page = $f->layout()->page()->standard(
        [
            $f->legacy()->content("<div id='mainspacekeeper'><div style='padding: 15px;'>"),
            $f->panel()->standard(
                'Mode Info Example',
                $panel_content
            ),
            $f->legacy()->content("</div></div>")
        ],
        $f->mainControls()->metaBar()->withAdditionalEntry(
            'help',
            $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#')
        ),
        $f->mainControls()->mainBar()->withAdditionalEntry("entry1", $slate),
        $f->breadcrumbs([]),
        $f->image()->responsive("assets/images/logo/HeaderIcon.svg", "ILIAS"),
        $f->image()->responsive("assets/images/logo/HeaderIconResponsive.svg", "ILIAS"),
        "./assets/images/logo/favicon.ico",
        $dic->ui()->factory()->toast()->container(),
        $dic->ui()->factory()->mainControls()->footer()->withAdditionalText("Footer"),
        'UI PAGE MODE INFO DEMO', //page title
        'ILIAS', //short title
        'Mode Info Demo' //view title
    )
    ->withHeaders(true)
    ->withUIDemo(true);


    /**
     * a Mode Info needs to know what happens when you exit the mode
     */
    $back = str_replace(
        'new_mode_info=' . MODE_INFO_ACTIVE,
        'new_mode_info=' . MODE_INFO_INACTIVE,
        $dic->http()->request()->getUri()->getQuery()
    );

    $mode_info = $f->mainControls()->modeInfo(
        "Active Mode Info",
        $data_factory->uri($dic->http()->request()->getUri()->withQuery($back)->__toString())
    );

    /**
     * the Mode Info is attached to the page
     */
    $page = $page->withModeInfo($mode_info);

    return $renderer->render($page);
}
