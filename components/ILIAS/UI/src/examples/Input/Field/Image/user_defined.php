<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Image;

use ILIAS\Data\ImagePurpose;

/**
 * Example showing how the image field is used when the image purpose cannot
 * be determined by the consumer, leaving the decision up to the user.
 */
function user_defined(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->image(
        new \ilUIDemoFileUploadHandlerGUI(),
        ImagePurpose::USER_DEFINED,
        'Upload Image',
        'Please provide an alternate text if necessary.',
    );

    $form = $factory->input()->container()->form()->standard("#", [$input]);

    return $renderer->render($form);
}
