<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Image\Responsive;

/**
 * Example for rendering an responsive Image using
 * withSourceSet and withSizesSelectorStatement
 */
function with_source_set()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $source_set = [
        '1204w' => 'src/UI/examples/Image/mountains.jpg',
        '602w' => 'src/UI/examples/Image/mountains-602w.jpg',
        '301w' => 'src/UI/examples/Image/mountains-301w.jpg',
        '144w' => 'src/UI/examples/Image/mountains-144w.jpg',
    ];

    $sizes_statement = '(min-width: 1200px) 60vw, (max-width: 1199px) 30vw';

    //Genarating and rendering the image
    $image = $f->image()->responsive(
        'src/UI/examples/Image/mountains.jpg',
        'Sunset over the Alps'
    )->withSourceSet($source_set)
        ->withSizesSelectorStatement($sizes_statement);

    $html = $renderer->render($image);

    return $html;
}
