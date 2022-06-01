<?php declare(strict_types = 1);

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

namespace ILIAS\COPage\History;

use ILIAS\COPage\InternalDataService;
use ILIAS\COPage\InternalRepoService;
use ILIAS\COPage\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class HistoryManager
{
    protected HistoryDBRepository $history_repo;

    public function __construct(
        InternalDataService $data_service,
        InternalRepoService $repo_service,
        InternalDomainService $domain_service
    ) {
        $this->history_repo = $repo_service->history();
    }

    /**
     * @param int $x_days delet entries older than x days
     * @param int $keep_entries entries that should be kept as minimum
     * @throws \ilDateTimeException
     */
    public function deleteOldHistoryEntries(int $x_days, int $keep_entries) : bool
    {
        $deleted = false;

        foreach ($this->history_repo->getMaxHistEntryPerPageOlderThanX($x_days) as $page) {
            $max_deletable = $this->history_repo->getMaxDeletableNr($keep_entries, $page["parent_type"], $page["page_id"], $page["lang"]);
            $delete_lower_than_nr = min($page["max_nr"], $max_deletable);
            if ($delete_lower_than_nr > 0) {
                $this->deleteHistoryEntriesOlderEqualThanNr(
                    $delete_lower_than_nr,
                    $page["parent_type"],
                    $page["page_id"],
                    $page["lang"]
                );
                $deleted = true;
            }
        }
        return $deleted;
    }


    protected function deleteHistoryEntriesOlderEqualThanNr(
        int $delete_lower_than_nr,
        string $parent_type,
        int $page_id,
        string $lang
    ) : void {
        $defs = \ilCOPagePCDef::getPCDefinitions();
        foreach ($defs as $def) {
            $cl = $def["pc_class"];
            $cl::deleteHistoryLowerEqualThan(
                $parent_type,
                $page_id,
                $lang,
                $delete_lower_than_nr
            );
        }

        $this->history_repo->deleteHistoryEntriesOlderEqualThanNr(
            $delete_lower_than_nr,
            $parent_type,
            $page_id,
            $lang
        );
    }
}
