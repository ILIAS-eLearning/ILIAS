<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryFactory
{
    /**
     * @var ilLearningHistoryService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct(ilLearningHistoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Create entry
     *
     * @param $title
     * @param $icon_path
     * @param int $ts unix timestamp
     * @return ilLearningHistoryEntry
     */
    public function entry($achieve_text, $achieve_in_text, $icon_path, $ts, $obj_id, $ref_id = 0)
    {
        return new ilLearningHistoryEntry($achieve_text, $achieve_in_text, $icon_path, $ts, $obj_id, $ref_id);
    }

    /**
     * Entry collector
     *
     * @param
     * @return
     */
    public function collector()
    {
        return new ilLearningHistoryEntryCollector($this->service);
    }
}
