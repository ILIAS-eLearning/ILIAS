<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Genearal
 */
class ilECSNodeMappingSettings
{
    private static array $instances = [];

    private ilSetting $storage;

    
    private int $server_id;
    /**
     * MID of sender
     */
    private int $mid;
    
    private bool $directory_active = false;
    private bool $create_empty_containers = false;
    
    /**
     * Course allocation
     */
    private bool $course_active = false;
    private int $default_cat = 0;
    private bool $allinone = false;
    private int $allinone_cat = 0;
    private bool $attributes = false;
    private array $role_mappings = array();
    private ?string $auth_mode = null;

    /**
     * Singeleton constructor
     */
    protected function __construct(int $a_server_id, int $a_mid)
    {
        global $DIC;

        $this->server_id = $a_server_id;
        $this->mid = $a_mid;
        
        $this->initStorage();
        $this->read();
    }
    
    /**
     * Get instance
     */
    public static function getInstanceByServerMid(int $a_server_id, int $a_mid) : ilECSNodeMappingSettings
    {
        $id = $a_server_id . '_' . $a_mid;
        return self::$instances[$id] ?? (self::$instances[$id] = new self($a_server_id, $a_mid));
    }
    
    /**
     * Get server id of setting
     */
    public function getServerId() : int
    {
        return $this->server_id;
    }
    
    /**
     * Get mid of sender
     */
    public function getMid() : int
    {
        return $this->mid;
    }

    /**
     * Check if node mapping is enabled
     */
    public function isDirectoryMappingEnabled() : bool
    {
        return $this->directory_active;
    }

    /**
     * Enable node mapping
     */
    public function enableDirectoryMapping(bool $a_status) : void
    {
        $this->directory_active = $a_status;
    }

    /**
     * enable creation of empty containers
     */
    public function enableEmptyContainerCreation(bool $a_status) : void
    {
        $this->create_empty_containers = $a_status;
    }

    /**
     * Check if the creation of empty containers (pathes without courses) is enabled
     */
    public function isEmptyContainerCreationEnabled() : bool
    {
        return $this->create_empty_containers;
    }
    
    public function enableCourseAllocation(bool $a_stat) : void
    {
        $this->course_active = $a_stat;
    }

    public function isCourseAllocationEnabled() : bool
    {
        return $this->course_active;
    }
    
    public function setDefaultCourseCategory(int $a_default_category) : void
    {
        $this->default_cat = $a_default_category;
    }
    
    public function getDefaultCourseCategory() : int
    {
        return $this->default_cat;
    }
    
    public function isAllInOneCategoryEnabled() : bool
    {
        return $this->allinone;
    }
    
    public function enableAllInOne(bool $a_stat) : void
    {
        $this->allinone = $a_stat;
    }
    
    public function setAllInOneCategory(int $a_cat) : void
    {
        $this->allinone_cat = $a_cat;
    }
    
    public function getAllInOneCategory() : int
    {
        return $this->allinone_cat;
    }
    
    public function enableAttributeMapping(bool $a_stat) : void
    {
        $this->attributes = $a_stat;
    }
    
    public function isAttributeMappingEnabled() : bool
    {
        return $this->attributes;
    }
    
    public function setRoleMappings(array $a_mappings) : void
    {
        $this->role_mappings = $a_mappings;
    }
    
    public function getRoleMappings() : array
    {
        return $this->role_mappings;
    }
    
    /**
     * Set user auth mode
     */
    public function setAuthMode(string $a_auth_mode) : void
    {
        $this->auth_mode = $a_auth_mode;
    }
    
    /**
     * Get auth mode
     */
    public function getAuthMode() : ?string
    {
        return $this->auth_mode;
    }

    /**
     * Save settings to db
     */
    public function update() : bool
    {
        $this->getStorage()->set('directory_active', (string) $this->isDirectoryMappingEnabled());
        $this->getStorage()->set('create_empty', (string) $this->isEmptyContainerCreationEnabled());
        $this->getStorage()->set('course_active', (string) $this->isCourseAllocationEnabled());
        $this->getStorage()->set('default_category', (string) $this->getDefaultCourseCategory());
        $this->getStorage()->set('allinone', (string) $this->isAllInOneCategoryEnabled());
        $this->getStorage()->set('allinone_cat', (string) $this->getAllInOneCategory());
        $this->getStorage()->set('attributes', (string) $this->isAttributeMappingEnabled());
        $this->getStorage()->set('role_mappings', serialize($this->getRoleMappings()));
        $this->getStorage()->set('auth_mode', $this->getAuthMode());
        return true;
    }

    /**
     * Get storage
     */
    protected function getStorage() : ilSetting
    {
        return $this->storage;
    }

    /**
     * Init storage
     */
    protected function initStorage() : void
    {
        $this->storage = new ilSetting('ecs_node_mapping_' . $this->getServerId() . '_' . $this->getMid());
    }

    
    /**
     * @todo convert to own database table
     * Read settings from db
     */
    protected function read() : void
    {
        if ($this->getStorage()->get('directory_active')) {
            $this->enableDirectoryMapping((bool) $this->getStorage()->get('directory_active'));
        }
        if ($this->getStorage()->get('create_empty')) {
            $this->enableEmptyContainerCreation((bool) $this->getStorage()->get('create_empty'));
        }
        if ($this->getStorage()->get('course_active')) {
            $this->enableCourseAllocation((bool) $this->getStorage()->get('course_active'));
        }
        if ($this->getStorage()->get('default_category')) {
            $this->setDefaultCourseCategory((int) $this->getStorage()->get('default_category'));
        }
        if ($this->getStorage()->get('allinone')) {
            $this->enableAllInOne((bool) $this->getStorage()->get('allinone'));
        }
        if ($this->getStorage()->get('allinone_cat')) {
            $this->setAllInOneCategory((int) $this->getStorage()->get('allinone_cat'));
        }
        if ($this->getStorage()->get('attributes')) {
            $this->enableAttributeMapping((bool) $this->getStorage()->get('attributes'));
        }
        if ($this->getStorage()->get('role_mappings')) {
            $this->setRoleMappings(unserialize($this->getStorage()->get('role_mappings'), ['allowed_classes' => true]));
        }
        if ($this->getStorage()->get('auth_mode')) {
            $this->setAuthMode($this->getStorage()->get('auth_mode'));
        }
    }
}
