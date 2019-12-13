<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history entry collector
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryEntryCollector
{
    /**
     * @var ilLearningHistoryService
     */
    protected $service;

    /**
     * Constructor
     * @param ilLearningHistoryService $service
     */
    public function __construct(ilLearningHistoryService $service)
    {
        $this->service = $service;
    }
    
    /**
     * Get entries
     *
     * @param int $from unix timestamp
     * @param int $to unix timestamp
     * @param int $user_id user id
     * @param array $classes
     * @return ilLearningHistoryEntry[]
     */
    public function getEntries(int $from = null, int $to = null, int $user_id = null, array $classes = null)
    {
        $entries = array();
        $lng = $this->service->language();

        $to = (is_null($to))
            ? time()
            : $to;
        $from = (is_null($from))
            ? time() - (365 * 24 * 60 * 60)
            : $from;

        $sort_array = [];
        foreach ($this->service->provider()->getAllProviders(true, $user_id) as $provider) {
            if (is_array($classes) && !in_array(get_class($provider), $classes)) {
                continue;
            }

            foreach ($provider->getEntries($from, $to) as $e) {
                $sort_array[] = array("entry" => $e,"ts" => $e->getTimestamp());
            }
        }

        $sort_array = ilUtil::sortArray($sort_array, "ts", "desc");

        // add today entry
        $entries = [];

        if (date("Y-m-d", $to) == date("Y-m-d", time())) {
            if (count($sort_array) == 0 ||
                date("Y-m-d", (current($sort_array)["ts"])) != date("Y-m-d", time())) {
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
