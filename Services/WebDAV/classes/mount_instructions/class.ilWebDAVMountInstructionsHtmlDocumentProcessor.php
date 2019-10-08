<?php


class ilWebDAVMountInstructionsHtmlDocumentProcessor extends ilWebDAVMountInstructionsDocumentProcessorBase
{
    /** @var ilWebDAVMountInstructionsDocumentPurifier */
    protected $document_purifier;

    public function __construct(ilHtmlPurifierInterface $a_document_purifier)
    {
        $this->document_purifier = $a_document_purifier;
    }

    public function processMountInstructions(string $a_raw_mount_instructions) : array
    {
        // TODO: Implement function to separate mount instructions from different operating systems
        //return $raw_mount_instructions;

        $purified_html_content = $this->document_purifier->purify($a_raw_mount_instructions);

        $html_validator = new ilWebDAVMountInstructionsDocumentsContainsHtmlValidator($purified_html_content);
        if (!$html_validator->isValid())
        {
            $purified_html_content = nl2br($purified_html_content);
        }

        return $this->parseInstructionsToAssocArray($purified_html_content);

    }
}