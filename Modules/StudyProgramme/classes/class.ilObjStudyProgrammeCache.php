<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

/**
 * Cache for ilObjStudyProgrammes.
 *
 * Implemented as singleton.
 */
class ilObjStudyProgrammeCache
{
    private static ?ilObjStudyProgrammeCache $instance = null;

    /**
     * @var ilObjStudyProgramme[]
     */
    protected array $instances;

    private function __construct()
    {
        $this->instances = array();
    }

    public static function singleton() : ilObjStudyProgrammeCache
    {
        if (self::$instance === null) {
            self::$instance = new ilObjStudyProgrammeCache();
        }
        return self::$instance;
    }

    public function getInstanceByRefId(int $ref_id) : ilObjStudyProgramme
    {
        // TODO: Maybe this should be done via obj_id instead of ref_id, since two
        // ref_ids could point to the same object, hence leading to two instances of
        // the same object. Since ilObjStudyProgramme is a container, it should (??)
        // only have one ref_id...
        if (!array_key_exists($ref_id, $this->instances)) {
            $this->instances[$ref_id] = new ilObjStudyProgramme($ref_id);
        }
        return $this->instances[$ref_id];
    }
    
    public function addInstance(ilObjStudyProgramme $prg) : void
    {
        if (!$prg->getRefId()) {
            throw new ilException("ilObjStudyProgrammeCache::addInstance: Can't add instance without ref_id.");
        }
        $this->instances[$prg->getRefId()] = $prg;
    }
    
    /**
     * For testing purpose.
     *
     * TODO: Move to mock class in tests.
     */
    public function test_clear() : void
    {
        $this->instances = array();
    }

    public function test_isEmpty() : bool
    {
        return count($this->instances) == 0;
    }
}
