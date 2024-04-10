<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Image;

use ILIAS\Data\ImagePurpose;

/**
 * Example showing how the image field is used with an additional metadata
 * input.
 */
function with_metadata(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->image(
        new \ilUIDemoFileUploadHandlerGUI(),
        ImagePurpose::USER_DEFINED,
        'Upload Image',
        'Please provide an alternate text if necessary.',
        $factory->input()->field()->text('Additional information')
    );

    $form = $factory->input()->container()->form()->standard("#", [$input]);

    return $renderer->render($form);
}
