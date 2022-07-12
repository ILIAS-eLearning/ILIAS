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
 * Derived task collector
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDerivedTaskCollector
{
    protected ilTaskService $service;

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
