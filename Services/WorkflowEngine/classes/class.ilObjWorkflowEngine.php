<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public static function getTempDir(bool $relative) : string
    {
        $relativeTempPath = 'wfe/upload_temp/';

        if ($relative) {
            return $relativeTempPath;
        }

        return ILIAS_DATA_DIR . '/' . CLIENT_ID . '/' . $relativeTempPath;
    }

    public static function getRepositoryDir(bool $relative = false) : string
    {
        $relativeRepositoryPath = 'wfe/repository/';

        if ($relative) {
            return $relativeRepositoryPath;
        }

        return ILIAS_DATA_DIR . '/' . CLIENT_ID . '/' . $relativeRepositoryPath;
    }
}
