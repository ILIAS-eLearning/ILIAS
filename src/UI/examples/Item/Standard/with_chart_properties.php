<?php
/**
 * With shy buttons as property values
 */
function with_chart_properties()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $chart1 = $f->chart()->progressMeter()->standard(100, 25);
    $chart2 = $f->chart()->progressMeter()->standard(100, 50);
    $chart3 = $f->chart()->progressMeter()->standard(100, 75);
    $app_item = $f->item()->standard("Item Title")
                  ->withProperties(array(
                      "Progress1" => $chart1,
                      "Progress2" => $chart2,
                      "Progress3" => $chart3))
                  ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");
    return $renderer->render($app_item);
}