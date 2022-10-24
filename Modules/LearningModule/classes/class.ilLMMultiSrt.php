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
 * Handler class for multi srt upload in learning modules
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMMultiSrt implements ilMobMultiSrtInt
{
    protected ilObjLearningModule $lm;

    public function __construct(ilObjLearningModule $a_lm)
    {
        $this->lm = $a_lm;
    }

    /**
     * Get directory for multi srt upload
     */
    public function getUploadDir(): string
    {
        return ilFileUtils::getDataDir() . "/lm_data" .
            "/lm_" . $this->lm->getId() . "/srt_tmp";
    }

    public function getMobIds(): array
    {
        // add mob information to items
        // all pages
        $pages = ilLMPageObject::getPageList($this->lm->getId());
        $mobs = array();
        foreach ($pages as $page) {
            // all media objects
            $pg_mobs = ilObjMediaObject::_getMobsOfObject("lm:pg", $page["obj_id"], 0, "");
            foreach ($pg_mobs as $k => $pg_mob) {
                $mobs[$k] = $pg_mob;
            }
        }
        return $mobs;
    }
}
