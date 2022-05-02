<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Standard;

/**
 * With audio player
 */
function with_audio_player() : string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $audio = $f->player()->audio("https://files.ilias.de/ILIAS-Audio.mp3", "");

    $app_item = $f->item()->standard("ILIAS Audio")
        ->withActions($actions)
        ->withAudioPlayer($audio)
        ->withProperties(array(
            "Length" => "00:00:26"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($f->image()->responsive(
            "src/UI/examples/Image/HeaderIconLarge.svg",
            "Thumbnail Example"
        ));
    return $renderer->render($app_item);
}
