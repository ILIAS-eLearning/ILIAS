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

declare(strict_types=1);

namespace ILIAS\Test\Logging;

class TestLogViewer
{
    public function __construct(
        private TestLoggingRepository $logging_repository
    ) {
    }

    public function getLegacyLogTableForObjId(\ilObjectGUI $parent_gui, int $obj_id): \ilAssessmentFolderLogTableGUI
    {
        $table_gui = new \ilAssessmentFolderLogTableGUI($parent_gui, 'logs');
        $log_output = $this->logging_repository->getLegacyLogsForObjId($obj_id);

        array_walk($log_output, static function (&$row) use ($parent_gui) {
            $row['location_href'] = '';
            $row['location_txt'] = '';
            if (is_numeric($row['ref_id']) && $row['ref_id'] > 0) {
                $row['location_href'] = ilLink::_getLink((int) $row['ref_id'], 'tst');
                $row['location_txt'] = $parent_gui->lng->txt("perma_link");
            }
        });

        $table_gui->setData($log_output);
        return $table_gui;
    }

}
