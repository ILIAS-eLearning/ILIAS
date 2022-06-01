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
 *********************************************************************/

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerFilterField
{
    public const STD_FIELD_TITLE = 1;
    public const STD_FIELD_DESCRIPTION = 2;
    public const STD_FIELD_TITLE_DESCRIPTION = 3;
    public const STD_FIELD_KEYWORD = 4;
    public const STD_FIELD_AUTHOR = 5;
    public const STD_FIELD_COPYRIGHT = 6;
    public const STD_FIELD_TUTORIAL_SUPPORT = 7;
    public const STD_FIELD_OBJECT_TYPE = 8;
    public const STD_FIELD_ONLINE = 9;

    protected int $record_set_id = 0;
    protected int $field_id = 0;

    public function __construct(int $record_set_id, int $field_id)
    {
        $this->record_set_id = $record_set_id;
        $this->field_id = $field_id;
    }

    public function getFieldId() : int
    {
        return $this->field_id;
    }

    public function getRecordSetId() : int
    {
        return $this->record_set_id;
    }
}
