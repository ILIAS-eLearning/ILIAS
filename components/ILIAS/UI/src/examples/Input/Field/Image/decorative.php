<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Image;

use ILIAS\Data\ImagePurpose;

/**
 * Example showing how the image field is used when the images' purpose
 * is purely decorative.
 */
function decorative(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->image(
        new \ilUIDemoFileUploadHandlerGUI(),
        ImagePurpose::DECORATIVE,
        'Upload Image',
        'This image will be purely decorative',
    );

    $form = $factory->input()->container()->form()->standard("#", [$input]);

    return $renderer->render($form);
}
