<?php

declare(strict_types=1);

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
* Remote category app class
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesRemoteCategory
*/

class ilObjRemoteCategory extends ilRemoteObjectBase
{
    public const DB_TABLE_NAME = "rcat_settings";

    public function initType(): void
    {
        $this->type = "rcat";
    }

    protected function getTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    protected function getECSObjectType(): string
    {
        return "/campusconnect/categories";
    }
}
