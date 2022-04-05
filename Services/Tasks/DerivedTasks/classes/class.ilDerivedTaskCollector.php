<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Derived task collector
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDerivedTaskCollector
{
    protected \ilTaskService $service;

    /**
     * Constructor
     */
    public function __construct(ilTaskService $service)
    {
        $this->service = $service;
    }

    /**
     * Get entries
     *
     * @param int $user_id user id
     * @return ilDerivedTask[]
     */
    public function getEntries(int $user_id) : array
    {
        $sort_array = [];
        /** @var ilDerivedTaskProvider $provider */
        foreach ($this->service->derived()->factory()->getAllProviders(true, $user_id) as $provider) {
            foreach ($provider->getTasks($user_id) as $t) {
                $sort_array[] = array("entry" => $t,"ts" => $t->getDeadline());
            }
        }

        $sort_array = ilArrayUtil::sortArray($sort_array, "ts", "desc");

        // add today entry
        $entries = [];

        foreach ($sort_array as $s) {
            $entries[] = $s["entry"];
        }

        return $entries;
    }
}
