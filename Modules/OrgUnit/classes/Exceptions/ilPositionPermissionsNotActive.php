<?php
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
 ********************************************************************
 */

/**
 * Class ilPositionPermissionsNotActive
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilPositionPermissionsNotActive extends ilOrguException
{
    protected string $object_type = "";

    /**
     * ilPositionPermissionsNotActive constructor.
     */
    public function __construct(string $message, string $type, int $code = 0)
    {
        parent::__construct($message, $code);

        $this->object_type = $type;
    }

    public function getObjectType(): string
    {
        return $this->object_type;
    }
}
