<?php


class ilWebDAVMountInstructionsHtmlDocumentProcessor extends ilWebDAVMountInstructionsDocumentProcessorBase
{
    public function processMountInstructions(string $raw_mount_instructions) : string
    {
        // TODO: Implement function to separate mount instructions from different operating systems
        return $raw_mount_instructions;

        $purified_html_content = $this->document_purifier->purify($raw_mount_instructions);

        $html_validator = new ilWebDAVMountInstructionsDocumentsContainsHtmlValidator($purified_html_content);
        if (!$html_validator->isValid())
        {
            $purified_html_content = nl2br($purified_html_content);
        }

        $processed_text = $purified_html_content;

        return $processed_text;

    }
}