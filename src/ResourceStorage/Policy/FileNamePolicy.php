<?php

namespace ILIAS\ResourceStorage\Policy;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface FileNamePolicy
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface FileNamePolicy
{
    /**
     * @throws FileNamePolicyException
     */
    public function check(string $extension) : bool;

    public function isValidExtension(string $extension) : bool;

    public function isBlockedExtension(string $extension) : bool;

    public function prepareFileNameForConsumer(string $filename_with_extension) : string;
}
