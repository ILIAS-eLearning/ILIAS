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

use ILIAS\Setup;

class ilGlobalCacheDataCollectedObjective extends Setup\Artifact\BuildArtifactObjective
{
    protected string $data_path;

    public function __construct()
    {
        $this->data_path = ilApc::APC_DATA_PATH . "/" . ilApc::APC_DATA_FILE;
    }

    public function getArtifactPath() : string
    {
        return $this->data_path;
    }

    public function build() : Setup\Artifact
    {
        return new Setup\Artifact\ArrayArtifact([time()]);
    }
}