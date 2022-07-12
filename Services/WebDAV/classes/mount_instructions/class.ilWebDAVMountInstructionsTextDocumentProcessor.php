<?php declare(strict_types = 1);

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
 
class ilWebDAVMountInstructionsTextDocumentProcessor extends ilWebDAVMountInstructionsDocumentProcessorBase
{
    public function processMountInstructions(string $a_raw_mount_instructions) : array
    {
        $stripped_instructions = htmlspecialchars($a_raw_mount_instructions);
        $stripped_instructions = nl2br($stripped_instructions);

        return $this->parseInstructionsToAssocArray($stripped_instructions);
    }
}
