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
 * Learning history entry
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningHistoryEntry
{
    protected string $achieve_text;
    protected string $achieve_in_text;
    protected string $icon_path;
    protected int $ts;
    protected int $obj_id;
    protected int $ref_id;

    public function __construct(
        string $achieve_text,
        string $achieve_in_text,
        string $icon_path,
        int $ts,
        int $obj_id,
        int $ref_id = 0
    ) {
        $this->achieve_text = $achieve_text;
        $this->achieve_in_text = $achieve_in_text;
        $this->icon_path = $icon_path;
        $this->ts = $ts;
        $this->obj_id = $obj_id;
        $this->ref_id = $ref_id;
    }

    public function getTimestamp() : int
    {
        return $this->ts;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getAchieveText() : string
    {
        return $this->achieve_text;
    }

    /**
     * Get "achieve in ..." text
     */
    public function getAchieveInText() : string
    {
        return $this->achieve_in_text;
    }

    public function getIconPath() : string
    {
        return $this->icon_path;
    }
}
