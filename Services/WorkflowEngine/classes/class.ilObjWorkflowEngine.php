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
 * Class ilObjWorkflowEngine
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilObjWorkflowEngine extends ilObject
{
    public function __construct(int $id = 0, bool $call_by_reference = true)
    {
        $this->type = "wfe";
        parent::__construct($id, $call_by_reference);
    }

    public static function getTempDir(bool $relative): string
    {
        $relativeTempPath = 'wfe/upload_temp/';

        if ($relative) {
            return $relativeTempPath;
        }

        return ILIAS_DATA_DIR . '/' . CLIENT_ID . '/' . $relativeTempPath;
    }

    public static function getRepositoryDir(bool $relative = false): string
    {
        $relativeRepositoryPath = 'wfe/repository/';

        if ($relative) {
            return $relativeRepositoryPath;
        }

        return ILIAS_DATA_DIR . '/' . CLIENT_ID . '/' . $relativeRepositoryPath;
    }
}
