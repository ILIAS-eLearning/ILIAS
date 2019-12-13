<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/MediaObjects/interfaces/interface.ilMobMultiSrtInt.php");

/**
 * Handler class for multi srt upload in learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilMepMultiSrt implements ilMobMultiSrtInt
{
    public function __construct($a_mep)
    {
        $this->mep = $a_mep;
    }

    /**
     * Get directory for multi srt upload
     *
     * @return string diretory
     */
    public function getUploadDir()
    {
        return ilUtil::getDataDir() . "/mep_data" .
            "/mep_" . $this->mep->getId() . "/srt_tmp";
    }

    /**
     *
     *
     * @param
     * @return
     */
    public function getMobIds()
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
                $mobs[$k] = $pg_mob;
            }
        }

        return $mobs;
    }
}
