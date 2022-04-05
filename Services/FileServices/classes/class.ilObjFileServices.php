<?php declare(strict_types=1);

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
 * Class ilObjFileServices
 * @author Lukas Zehnder <lz@studer-raimann.ch>
 */
class ilObjFileServices extends ilObject
{
    public const TYPE_FILE_SERVICES = "fils";

    /**
     * ilObjFileServices constructor.
     * @param int  $id
     * @param bool $call_by_reference
     */
    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        $this->type = self::TYPE_FILE_SERVICES;
        parent::__construct($id, $call_by_reference);
    }

    public function getPresentationTitle() : string
    {
        return $this->lng->txt("file_services");
    }

    public function getLongDescription() : string
    {
        return $this->lng->txt("file_services_description");
    }
}
