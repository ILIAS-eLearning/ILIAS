<?php declare(strict_types=1);

/**
 * Cache for object definitions, based on ilGlobalCache.
 */
class ilCachedObjectDefinition
{
    protected array $cached_results = [];
    protected static ilCachedObjectDefinition $instance;
    protected bool $changed = false;
    protected array $il_object_def = [];
    protected array $subobj_for_parent = [];
    protected array $grouped_rep_obj_types = [];
    protected array $il_object_group = [];
    protected array $il_object_sub_type = [];
    
    protected function __construct()
    {
        $this->global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_OBJ_DEF);
        $this->readFromDB();
    }


    protected function readFromDB() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        /**
         * @var $ilDB ilDB
         */

        $set = $ilDB->query('SELECT * FROM il_object_def');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->il_object_def[$rec['id']] = $rec;
        }

        $set = $ilDB->query('SELECT * FROM il_object_subobj');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $parent = $rec['parent'];
            $this->subobj_for_parent[$parent][] = $rec;
        }
        $set = $ilDB->query('SELECT DISTINCT(id) AS sid, parent, il_object_def.* FROM il_object_def, il_object_subobj WHERE NOT (' . $ilDB->quoteIdentifier('system') . ' = 1) AND NOT (sideblock = 1) AND subobj = id');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->grouped_rep_obj_types[$rec['parent']][] = $rec;
        }
        $set = $ilDB->query('SELECT * FROM il_object_group');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->il_object_group[$rec['id']] = $rec;
        }
        $set = $ilDB->query('SELECT * FROM il_object_sub_type');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->il_object_sub_type[$rec['obj_type']][] = $rec;
        }
    }

    public function getIlObjectDef() : array
    {
        return $this->il_object_def;
    }

    public function getIlObjectGroup() : array
    {
        return $this->il_object_group;
    }

    public function getIlObjectSubType() : array
    {
        return $this->il_object_sub_type;
    }

    public static function getInstance() : ilCachedObjectDefinition
    {
        if (!isset(self::$instance)) {
            $global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_OBJ_DEF);
            $cached_obj = $global_cache->get('ilCachedObjectDefinition');
            if ($cached_obj instanceof ilCachedObjectDefinition) {
                self::$instance = $cached_obj;
            } else {
                self::$instance = new self();
                $global_cache->set('ilCachedObjectDefinition', self::$instance);
            }
        }

        return self::$instance;
    }


    public static function flush() : void
    {
        ilGlobalCache::getInstance(ilGlobalCache::COMP_OBJ_DEF)->flush();
        self::$instance = null;
    }


    /**
     * @param mixed $parent
     *
     * @return mixed
     */
    public function lookupSubObjForParent($parent)
    {
        if (is_array($parent)) {
            $index = md5(serialize($parent));
            if (isset($this->cached_results['subop_par'][$index])) {
                return $this->cached_results['subop_par'][$index];
            }

            $return = array();
            foreach ($parent as $p) {
                if (is_array($this->subobj_for_parent[$p])) {
                    foreach ($this->subobj_for_parent[$p] as $rec) {
                        $return[] = $rec;
                    }
                }
            }

            $this->cached_results['subop_par'][$index] = $return;
            $this->changed = true;

            return $return;
        }

        return $this->subobj_for_parent[$parent];
    }

    public function __destruct()
    {
        $ilGlobalCache = ilGlobalCache::getInstance(ilGlobalCache::COMP_OBJ_DEF);
        if ($this->changed && $ilGlobalCache->isActive()) {
            $this->changed = false;
            $ilGlobalCache->set('ilCachedObjectDefinition', $this);
        }
    }


    /**
     * @param mixed $parent
     *
     * @return mixed
     */
    public function lookupGroupedRepObj($parent)
    {
        if (is_array($parent)) {
            $index = md5(serialize($parent));
            if (isset($this->cached_results['grpd_repo'][$index])) {
                return $this->cached_results['grpd_repo'][$index];
            }

            $return = array();
            $sids = array();
            foreach ($parent as $p) {
                $s = $this->grouped_rep_obj_types[$p];
                foreach ($s as $child) {
                    if (!in_array($child['sid'], $sids)) {
                        $sids[] = $child['sid'];
                        $return[] = $child;
                    }
                }
            }
            $this->changed = true;
            $this->cached_results['grpd_repo'][$index] = $return;

            return $return;
        } else {
            return $this->grouped_rep_obj_types[$parent];
        }
    }
}
