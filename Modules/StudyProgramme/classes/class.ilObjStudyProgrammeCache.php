<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Cache for ilObjStudyProgrammes.
 *
 * Implemented as singleton.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */

class ilObjStudyProgrammeCache
{
    private static $instance = null; // ilObjStudyProgrammeCache

    private function __construct()
    {
        $this->instances = array();
    }

    public static function singleton()
    {
        if (self::$instance === null) {
            self::$instance = new ilObjStudyProgrammeCache();
        }
        return self::$instance;
    }
    
    protected $instances; // [ilObjStudyProgramme]
    
    public function getInstanceByRefId($a_ref_id)
    {
        require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
        
        // TODO: Maybe this should be done via obj_id instead of ref_id, since two
        // ref_ids could point to the same object, hence leading to two instances of
        // the same object. Since ilObjStudyProgramme is a container, it should (??)
        // only have one ref_id...
        if (!array_key_exists($a_ref_id, $this->instances)) {
            $this->instances[$a_ref_id] = new ilObjStudyProgramme($a_ref_id);
        }
        return $this->instances[$a_ref_id];
    }
    
    public function addInstance(ilObjStudyProgramme $a_prg)
    {
        if (!$a_prg->getRefId()) {
            throw new ilException("ilObjStudyProgrammeCache::addInstance: "
                                 . "Can't add instance without ref_id.");
        }
        $this->instances[$a_prg->getRefId()] = $a_prg;
    }
    
    /**
     * For testing purpose.
     *
     * TODO: Move to mock class in tests.
     */
    public function test_clear()
    {
        $this->instances = array();
    }
    
    public function test_isEmpty()
    {
        return count($this->instances) == 0;
    }
}
