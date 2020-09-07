<?php

/**
 * Class ilCachedCtrl
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilCachedCtrl
{
    /**
     * @var ilDB
     */
    protected $db;


    /**
     * @var bool
     */
    protected $changed = false;
    /**
     * @var ilCachedCtrl
     */
    protected static $instance;
    /**
     * @var bool
     */
    protected $loaded = false;
    /**
     * @var array
     */
    protected $module_classes = array();
    /**
     * @var array
     */
    protected $service_classes = array();
    /**
     * @var array
     */
    protected $ctrl_calls = array();
    /**
     * @var array
     */
    protected $ctrl_classfile = array();
    /**
     * @var array
     */
    protected $ctrl_classfile_parent = array();


    /**
     * @return ilCachedComponentData
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_ILCTRL);
            $cached_obj = $global_cache->get('ilCachedCtrl');
            if ($cached_obj instanceof ilCachedCtrl) {
                self::$instance = $cached_obj;
            } else {
                self::$instance = new self();
                $global_cache->set('ilCachedCtrl', self::$instance);
            }
        }

        return self::$instance;
    }


    public static function flush()
    {
        ilGlobalCache::getInstance(ilGlobalCache::COMP_ILCTRL)->flush();
        self::$instance = null;
    }


    /**
     * @return bool
     */
    public function isActive()
    {
        return ilGlobalCache::getInstance(ilGlobalCache::COMP_ILCTRL)->isActive();
    }


    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->global_cache = ilGlobalCache::getInstance(ilGlobalCache::COMP_ILCTRL);
        $this->readFromDB();
    }


    public function __destruct()
    {
        if ($this->changed) {
            $this->global_cache->set('ilCachedCtrl', $this);
        }
    }


    protected function readFromDB()
    {
        $ilDB = $this->db;
        /**
         * @var $ilDB ilDB
         */
        $set = $ilDB->query('SELECT module_class.*, LOWER(module_class.class) lower_class FROM module_class');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->module_classes[$rec['lower_class']] = $rec;
        }
        $set = $ilDB->query('SELECT service_class.*, LOWER(service_class.class) lower_class FROM service_class');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->service_classes[$rec['lower_class']] = $rec;
        }
        $set = $ilDB->query('SELECT * FROM ctrl_calls');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->ctrl_calls[$rec['parent']][] = $rec;
        }
        $set = $ilDB->query('SELECT * FROM ctrl_classfile');
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->ctrl_classfile[$rec['cid']] = $rec;
            $this->ctrl_classfile_parent[$rec['class']] = $rec;
        }
    }


    /**
     * @param $class
     *
     * @return mixed
     */
    public function lookupModuleClass($class)
    {
        return $this->module_classes[$class];
    }


    /**
     * @param $class
     *
     * @return mixed
     */
    public function lookupServiceClass($class)
    {
        return $this->service_classes[$class];
    }


    /**
     * @param $cid
     *
     * @return mixed
     */
    public function lookupCid($cid)
    {
        return $this->ctrl_classfile[$cid];
    }


    /**
     * @param $parent
     *
     * @return mixed
     */
    public function lookupCall($parent)
    {
        if (is_array($this->ctrl_calls[$parent])) {
            return $this->ctrl_calls[$parent];
        } else {
            return array();
        }
    }


    /**
     * @param $class
     *
     * @return mixed
     */
    public function lookupClassFile($class)
    {
        return $this->ctrl_classfile_parent[$class];
    }


    /**
     * @return boolean
     */
    public function getLoaded()
    {
        return $this->loaded;
    }


    /**
     * @param boolean $loaded
     */
    public function setLoaded($loaded)
    {
        $this->loaded = $loaded;
    }


    /**
     * @param array $ctrl_calls
     */
    public function setCtrlCalls($ctrl_calls)
    {
        $this->ctrl_calls = $ctrl_calls;
    }


    /**
     * @return array
     */
    public function getCtrlCalls()
    {
        return $this->ctrl_calls;
    }


    /**
     * @param array $ctrl_classfile
     */
    public function setCtrlClassfile($ctrl_classfile)
    {
        $this->ctrl_classfile = $ctrl_classfile;
    }


    /**
     * @return array
     */
    public function getCtrlClassfile()
    {
        return $this->ctrl_classfile;
    }


    /**
     * @param array $module_classes
     */
    public function setModuleClasses($module_classes)
    {
        $this->module_classes = $module_classes;
    }


    /**
     * @return array
     */
    public function getModuleClasses()
    {
        return $this->module_classes;
    }


    /**
     * @param array $service_classes
     */
    public function setServiceClasses($service_classes)
    {
        $this->service_classes = $service_classes;
    }


    /**
     * @return array
     */
    public function getServiceClasses()
    {
        return $this->service_classes;
    }


    /**
     * @param array $ctrl_classfile_parent
     */
    public function setCtrlClassfileParent($ctrl_classfile_parent)
    {
        $this->ctrl_classfile_parent = $ctrl_classfile_parent;
    }


    /**
     * @return array
     */
    public function getCtrlClassfileParent()
    {
        return $this->ctrl_classfile_parent;
    }


    /**
     * Declares all fields which should be serialized by php.
     * This has to be done, because the PDO objects are not serializable.
     *
     * @return string[]
     */
    public function __sleep()
    {
        return [
            'changed',
            'loaded',
            'module_classes',
            'service_classes',
            'ctrl_calls',
            'ctrl_classfile',
            'ctrl_classfile_parent'
        ];
    }


    /**
     * Restore database connection.
     */
    public function __wakeup()
    {
        global $DIC;

        $this->db = $DIC->database();
    }
}
