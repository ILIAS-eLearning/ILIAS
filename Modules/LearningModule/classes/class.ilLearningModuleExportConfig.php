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
 * Export configuration for learning modules
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningModuleExportConfig extends ilExportConfig
{
    protected bool $master_only = false;
    protected bool $include_media = true;

    public function setMasterLanguageOnly(
        bool $a_val,
        bool $a_include_media = true
    ) : void {
        $this->master_only = $a_val;
        $this->include_media = $a_include_media;
    }

    public function getMasterLanguageOnly() : bool
    {
        return $this->master_only;
    }

    public function getIncludeMedia() : bool
    {
        return $this->include_media;
    }
}
