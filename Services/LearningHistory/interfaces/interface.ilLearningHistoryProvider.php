<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history provider interface
 *
 * Providers can add entries to the learning history through this interface
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
interface ilLearningHistoryProviderInterface
{
    /**
     * Is active?
     *
     * @return bool
     */
    public function isActive();

    /**
     * Get entries
     *
     * @param int $ts_start
     * @param int $ts_end
     * @return ilLearningHistoryEntry[]
     */
    public function getEntries($ts_start, $ts_end);

    /**
     * Get name of provider (in user language)
     * @return string
     */
    public function getName() : string;
}
