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
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMepMultiSrt implements ilMobMultiSrtInt
{
    protected ilObjMediaPool $mep;

    public function __construct(ilObjMediaPool $a_mep)
    {
        $this->mep = $a_mep;
    }

    /**
     * Get directory for multi srt upload
     */
    public function getUploadDir(): string
    {
        return ilFileUtils::getDataDir() . "/mep_data" .
            "/mep_" . $this->mep->getId() . "/srt_tmp";
    }

    /**
     * Get all mob ids of pool (incl mobs in snippet pages)
     * @return int[]
     */
    public function getMobIds(): array
    {
        $mobs = array();

        foreach (ilObjMediaPool::getAllMobIds($this->mep->getId()) as $id) {
            $mobs[$id] = $id;
        }
        $pages = ilMediaPoolItem::getIdsForType($this->mep->getId(), "pg");
        foreach ($pages as $p) {
            // all media objects
            $pg_mobs = ilObjMediaObject::_getMobsOfObject("mep:pg", $p, 0, "");
            foreach ($pg_mobs as $k => $pg_mob) {
                $mobs[(int) $pg_mob] = (int) $pg_mob;
            }
        }

        return $mobs;
    }
}
