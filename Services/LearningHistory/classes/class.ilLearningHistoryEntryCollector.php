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
 * Learning history entry collector
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningHistoryEntryCollector
{
    protected ilLearningHistoryService $service;

    public function __construct(ilLearningHistoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Get entries
     * @param ?int   $from    unix timestamp
     * @param ?int   $to      unix timestamp
     * @param ?int   $user_id user id
     * @param ?array $classes
     * @return ilLearningHistoryEntry[]
     */
    public function getEntries(
        ?int $from = null,
        ?int $to = null,
        ?int $user_id = null,
        ?array $classes = null
    ) : array {
        $lng = $this->service->language();

        $to = (is_null($to))
            ? time()
            : $to;
        $from = (is_null($from))
            ? time() - (365 * 24 * 60 * 60)
            : $from;

        $sort_array = [];
        foreach ($this->service->provider()->getAllProviders(true, $user_id) as $provider) {
            if (is_array($classes) && !in_array(get_class($provider), $classes, true)) {
                continue;
            }

            foreach ($provider->getEntries($from, $to) as $e) {
                $sort_array[] = array("entry" => $e,"ts" => $e->getTimestamp());
            }
        }

        $sort_array = ilArrayUtil::sortArray($sort_array, "ts", "desc");

        // add today entry
        $entries = [];

        if (date("Y-m-d", $to) === date("Y-m-d")) {
            if (count($sort_array) === 0 ||
                date("Y-m-d", (current($sort_array)["ts"])) !== date("Y-m-d")) {
                $entries[] = $this->service->factory()->entry(
                    $lng->txt("lhist_no_entries"),
                    $lng->txt("lhist_no_entries"),
                    ilUtil::getImagePath("spacer.png"),
                    time(),
                    0
                );
            }
        }


        foreach ($sort_array as $s) {
            $entries[] = $s["entry"];
        }

        return $entries;
    }
}
