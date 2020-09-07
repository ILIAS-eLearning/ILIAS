<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history entry
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryEntry
{
    /**
     * @var string
     */
    protected $achieve_text;

    /**
     * @var string
     */
    protected $achieve_in_text;

    /**
     * @var string
     */
    protected $icon_path;

    /**
     * @var int
     */
    protected $ts;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * Constructor
     * @param string $achieve_text
     * @param string $achieve_in_text
     * @param string $icon_path
     * @param int $ts
     * @param int $obj_id
     * @param int $ref_id
     */
    public function __construct($achieve_text, $achieve_in_text, $icon_path, $ts, $obj_id, $ref_id = 0)
    {
        $this->achieve_text = $achieve_text;
        $this->achieve_in_text = $achieve_in_text;
        $this->icon_path = $icon_path;
        $this->ts = $ts;
        $this->obj_id = $obj_id;
        $this->ref_id = $ref_id;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->ts;
    }

    /**
     * Get obj id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get ref id
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * Get achieve text
     *
     * @return string
     */
    public function getAchieveText()
    {
        return $this->achieve_text;
    }

    /**
     * Get achieve in text
     *
     * @return string
     */
    public function getAchieveInText()
    {
        return $this->achieve_in_text;
    }

    /**
     * Get Icon path
     *
     * @return string
     */
    public function getIconPath()
    {
        return $this->icon_path;
    }
}
