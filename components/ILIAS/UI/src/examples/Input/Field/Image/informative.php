<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Image;

use ILIAS\Data\ImagePurpose;

/**
 * Example showing how the image field is used when the image conveys important
 * information to the context it will be used.
 */
function informative(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->image(
        new \ilUIDemoFileUploadHandlerGUI(),
        ImagePurpose::INFORMATIVE,
        'Upload Image',
        'This image should convey important information.',
    );

    $form = $factory->input()->container()->form()->standard("#", [$input]);

    return $renderer->render($form);
}
