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
 
class ilWebDAVObjectlessMountInstructions extends ilWebDAVBaseMountInstructions
{
    public function __construct(
        ilWebDAVMountInstructionsRepository $a_repo,
        ilWebDAVUriBuilder $a_uri_builder,
        ilSetting $a_settings,
        String $language
    ) {
        parent::__construct($a_repo, $a_uri_builder, $a_settings, $language);
    }

    protected function fillPlaceholdersForMountInstructions(array $mount_instructions) : array
    {
        return $mount_instructions;
    }
}
