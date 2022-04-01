<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Standard;

/**
 * With progress meter chart
 */
function with_image_and_progress()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $chart = $f->chart()->progressMeter()->standard(100, 75);
    $app_item = $f->item()->standard("Item Title")
                  ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
                  ->withProgress($chart)
                  ->withLeadImage($f->image()->responsive(
                      "src/UI/examples/Image/HeaderIconLarge.svg",
                      "Thumbnail Example"
                  ));
    return $renderer->render($app_item);
}
