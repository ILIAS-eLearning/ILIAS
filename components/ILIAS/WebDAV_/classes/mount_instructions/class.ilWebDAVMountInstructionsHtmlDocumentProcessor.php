<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilWebDAVMountInstructionsHtmlDocumentProcessor extends ilWebDAVMountInstructionsDocumentProcessorBase
{
    protected ilHtmlPurifierInterface $document_purifier;

    public function __construct(ilHtmlPurifierInterface $a_document_purifier)
    {
        $this->document_purifier = $a_document_purifier;
    }

    public function processMountInstructions(string $a_raw_mount_instructions): array
    {
        $purified_html_content = $this->document_purifier->purify($a_raw_mount_instructions);

        $html_validator = new ilWebDAVMountInstructionsDocumentsContainsHtmlValidator($purified_html_content);
        if (!$html_validator->isValid()) {
            $purified_html_content = nl2br($purified_html_content);
        }

        return $this->parseInstructionsToAssocArray($purified_html_content);
    }
}
