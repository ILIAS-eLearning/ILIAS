<?php

declare(strict_types=1);

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

namespace ILIAS\Notes;

/**
 * Repository internal data service
 * @author Alexander Killing <killing@leifos.de>
 */
class DataService
{
    protected InternalDataService $internal_data;

    public function __construct(InternalDataService $internal_data)
    {
        $this->internal_data = $internal_data;
    }

    /**
     * @param int    $obj_id        ilObject ID, 0 for notes without context
     * @param int    $sub_obj_id    e.g. page id
     * @param string $type          ilObject type or type of sub object ("pd" for dashboard without context)
     * @param int    $news_id       news id (news must be attached to same ilObject ID)
     * @param int    $news_id       e.g. false for portfolios or "pd" notes
     */
    public function context(
        int $obj_id = 0,
        int $sub_obj_id = 0,
        string $type = "",
        int $news_id = 0,
        bool $in_repo = true
    ): Context {
        return $this->internal_data->context(
            $obj_id,
            $sub_obj_id,
            $type,
            $news_id,
            $in_repo
        );
    }
}
