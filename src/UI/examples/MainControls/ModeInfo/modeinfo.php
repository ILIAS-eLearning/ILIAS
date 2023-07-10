<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\ModeInfo;

use ILIAS\DI\Container;

function modeinfo(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render([
        $f->link()->standard(
            'See UI in fullscreen-mode',
            'src/UI/examples/MainControls/ModeInfo/modeinfo.php?new_mode_info='.MODE_INFO_INACTIVE
        )
    ]);
}

global $DIC;
const MODE_INFO_ACTIVE = 2;
const MODE_INFO_INACTIVE = 1;

//Render Mode Info example in Fullscreen mode
if (basename($_SERVER["SCRIPT_FILENAME"]) == "modeinfo.php") {
    chdir('../../../../../');
    require_once("libs/composer/vendor/autoload.php");
    \ilInitialisation::initILIAS();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();
}


if (isset($request_wrapper) && isset($refinery) && $request_wrapper->has('new_mode_info')) {
    echo renderModeInfoPage($DIC, $request_wrapper->retrieve('new_mode_info', $refinery->kindlyTo()->int()));
}

function renderModeInfoPage(Container $dic, int $mode): string
{
    $f = $dic->ui()->factory();
    $data_factory = new \ILIAS\Data\Factory();
    $renderer = $dic->ui()->renderer();

    if ($mode == MODE_INFO_INACTIVE) {
        $url = 'src/UI/examples/MainControls/ModeInfo/modeinfo.php?new_mode_info='.MODE_INFO_ACTIVE;
        $label = "Activate Mode Info";
        $panel_content = $f->button()->standard($label, $url);
        $slate = $f->mainControls()->slate()->legacy(
            "Mode Info Inactive",
            $f->symbol()->glyph()->settings(),
            $f->legacy("Just regular Mainbar stuff")
        );
    } else {
        $components[] = $f->mainControls()->modeInfo(
            "Active Mode Info",
            $data_factory->uri($dic->http()->request()->getUri()->withQuery('new_mode_info='.MODE_INFO_INACTIVE)->__toString())
        );
        $panel_content = $f->legacy("Mode Info is Active");
        $slate = $f->mainControls()->slate()->legacy(
            "Mode Info Active",
            $f->symbol()->glyph()->notification(),
            $f->legacy("Things todo when special Mode is active")
        );
    }

    $components[] = $f->layout()->page()->standard(
        [$f->panel()->standard(
            'Mode Info Example',
            $panel_content
        )],
        $f->mainControls()->metaBar()->withAdditionalEntry(
            'help',
            $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#')
        ),
        $f->mainControls()->mainBar()->withAdditionalEntry("entry1", $slate),
        $f->breadcrumbs([]),
        $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS"),
        $f->image()->responsive("templates/default/images/HeaderIconResponsive.svg", "ILIAS"),
        "./templates/default/images/favicon.ico",
        $dic->ui()->factory()->toast()->container(),
        $dic->ui()->factory()->mainControls()->footer([], "Footer"),
        'UI PAGE MODE INFO DEMO', //page title
        'ILIAS', //short title
        'Mode Info Demo' //view title
    )->withUIDemo(true);

    return $renderer->render($components);
}
