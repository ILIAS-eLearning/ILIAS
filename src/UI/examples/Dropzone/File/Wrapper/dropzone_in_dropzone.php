<?php
function dropzone_in_dropzone()
{
    global $DIC;

    // This example shows how the wrapper-dropzone will be "unusable" when another Dropzone is in it.
    // Dropping a file on the outer wrapper dropzone won't open a modal.
    // The innermost dropzone will be the working one. This example does not proceed the file, it's
    // only purpose is tho show stacking dropzones.

    $uiFactory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $uploadUrl = $_SERVER['REQUEST_URI'] . '&example=2';

    $content = $uiFactory->dropzone()->file()->standard($uploadUrl);
    $panel = $uiFactory->panel()->standard("Panel Titel", $content);

    $upload = $uiFactory->dropzone()->file()->wrapper($uploadUrl, $panel)
        ->withMaxFiles(2)
        ->withFileSizeLimit(new \ILIAS\Data\DataSize(300 * 1000, \ILIAS\Data\DataSize::KB));

    return $renderer->render($upload);
}
