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

class ilWebDAVNotDavableException extends ilException
{
    public const OBJECT_TYPE_NOT_DAVABLE = 'This object type is not davable!';
    public const FILE_EXTENSION_NOT_ALLOWED = 'This object has a forbidden file extension!';
    public const OBJECT_TITLE_NOT_DAVABLE = 'This object title is invalid or hidden!';

    /**
     * @param mixed $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
