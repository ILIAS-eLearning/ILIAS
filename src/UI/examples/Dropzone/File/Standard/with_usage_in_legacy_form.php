<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

function with_usage_in_legacy_form()
{
    // Build our form
    $form = new \ilPropertyFormGUI();
    $form->setId('myUniqueFormId');
    $form->setTitle('Form');
    $form->setFormAction($_SERVER['REQUEST_URI'] . '&example=6');
    $form->setPreventDoubleSubmission(false);
    $flag = new \ilHiddenInputGUI('submitted');
    $flag->setValue('1');
    $form->addItem($flag);
    $item = new \ilTextInputGUI('Title', 'title');
    $item->setRequired(true);
    $form->addItem($item);
    $item = new \ilTextAreaInputGUI('Description', 'description');
    $item->setRequired(true);
    $form->addItem($item);
    $item = new \ilFileStandardDropzoneInputGUI('cancel', 'Files', 'files');
    $item->setUploadUrl($form->getFormAction());
    $item->setSuffixes([ 'jpg', 'gif', 'png', 'pdf' ]);
    $item->setInfo('Allowed file types: ' . implode(', ', $item->getSuffixes()));
    $item->setDropzoneMessage('For the purpose of this demo, any PDF file will fail to upload');
    $form->addItem($item);
    $form->addCommandButton('save', 'Save');

    // Check for submission
    global $DIC;
    $refinery = $DIC->refinery();
    $post_wrapper = $DIC->http()->wrapper()->post();
    if ($post_wrapper->has('submitted') && $post_wrapper->retrieve('submitted', $refinery->kindlyTo()->bool())) {
        if ($form->checkInput()) {
            // We might also want to process and save other form data here
            $upload = $DIC->upload();
            // Check if this is a request to upload a file
            if ($upload->hasUploads()) {
                try {
                    $upload->process();
                    // We simulate a failing response for any uploaded PDF file
                    $uploadedPDFs = array_filter($upload->getResults(), function ($uploadResult) {
                        /** @var $uploadResult \ILIAS\FileUpload\DTO\UploadResult */
                        return ($uploadResult->getMimeType() == 'application/pdf');
                    });
                    $uploadResult = count($uploadedPDFs) == 0;
                    echo json_encode(array( 'success' => $uploadResult ));
                } catch (\Exception $e) {
                    echo json_encode(array( 'success' => false ));
                }
                exit();
            }
        } else {
            $form->setValuesByPost();
        }
        \ilUtil::sendSuccess('Form processed successfully');
    }

    return $form->getHTML();
}
