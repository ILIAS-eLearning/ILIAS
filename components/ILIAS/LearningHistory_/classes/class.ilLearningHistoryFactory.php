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
 * Learning history factory
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningHistoryFactory
{
    protected ilLearningHistoryService $service;

    public function __construct(ilLearningHistoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Create entry
     */
    public function entry(
        string $achieve_text,
        string $achieve_in_text,
        string $icon_path,
        int $ts,
        int $obj_id,
        int $ref_id = 0
    ): ilLearningHistoryEntry {
        return new ilLearningHistoryEntry($achieve_text, $achieve_in_text, $icon_path, $ts, $obj_id, $ref_id);
    }

    /**
     * Entry collector
     */
    public function collector(): ilLearningHistoryEntryCollector
    {
        return new ilLearningHistoryEntryCollector($this->service);
    }
}
