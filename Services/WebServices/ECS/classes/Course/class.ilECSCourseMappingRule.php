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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseMappingRule
{
    const SUBDIR_ATTRIBUTE_NAME = 1;
    const SUBDIR_VALUE = 2;
    
    private ilLogger $logger;
    private ilTree $tree;
    private ilLanguage $lng;
    private ilDBInterface $db;
    
    private $rid;
    private $sid;
    private $mid;
    private $attribute;
    private $ref_id;
    private bool $is_filter = false;
    private $filter;
    private array $filter_elements = [];
    private bool $create_subdir = true;
    private int $subdir_type = self::SUBDIR_VALUE;
    private string $directory = '';
    
    /**
     * Constructor
     * @param int $a_rid
     */
    public function __construct($a_rid = 0)
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        
        $this->rid = $a_rid;
        $this->read();
    }
    
    /**
     * Lookup existing attributes
     * @param type $a_attributes
     * @return array
     */
    public static function lookupLastExistingAttribute($a_sid, $a_mid, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT attribute FROM ecs_cmap_rule ' .
                'WHERE sid = ' . $ilDB->quote($a_sid, 'integer') . ' ' .
                'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
                'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ' .
                'ORDER BY rid ';
        $res = $ilDB->query($query);
        
        $attributes = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $attributes = $row->attribute;
        }
        return $attributes;
    }
    
    /**
     *
     * @param type $a_sid
     * @param type $a_mid
     */
    public static function getRuleRefIds($a_sid, $a_mid)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT DISTINCT(ref_id) ref_id, rid FROM ecs_cmap_rule ' .
                'WHERE sid = ' . $ilDB->quote($a_sid, 'integer') . ' ' .
                'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
                'GROUP BY ref_id' . ' ' .
                'ORDER BY rid';
        
        $res = $ilDB->query($query);
        $ref_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $ref_ids[] = $row->ref_id;
        }
        // check if ref_ids are in tree
        $checked_ref_ids = [];
        foreach ($ref_ids as $ref_id) {
            if (
                $DIC->repositoryTree()->isInTree($ref_id)) {
                $checked_ref_ids[] = $ref_id;
            }
        }
        return $checked_ref_ids;
    }
    
    /**
     * Get all rule of ref_id
     * @param type $a_sid
     * @param type $a_mid
     * @param type $a_ref_id
     * @return int[]
     */
    public static function getRulesOfRefId($a_sid, $a_mid, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT rid FROM ecs_cmap_rule ' .
                'WHERE sid = ' . $ilDB->quote($a_sid, 'integer') . ' ' .
                'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
                'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        $res = $ilDB->query($query);
        $rids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rids = $row->rid;
        }
        return (array) $rids;
    }
    
    public static function hasRules($a_sid, $a_mid, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT ref_id FROM ecs_cmap_rule ' .
                'WHERE sid = ' . $ilDB->quote($a_sid, 'integer') . ' ' .
                'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
                'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        $res = $ilDB->query($query);
        return $res->numRows() ? true : false;
    }
    
    /**
     * Check if rule matches
     * @param type $course
     * @param type $a_start_rule_id
     * @return string 0 if not matches; otherwise rule_id_index @see matches
     */
    public static function isMatching($course, $a_sid, $a_mid, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT rid FROM ecs_cmap_rule ' .
                'WHERE sid = ' . $ilDB->quote($a_sid, 'integer') . ' ' .
                'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
                'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ' .
                'ORDER BY rid';
        $res = $ilDB->query($query);
        
        $does_match = false;
        $sortable_index = '';
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rule = new ilECSCourseMappingRule($row->rid);
            $matches = $rule->matches($course);
            if ($matches == -1) {
                return '0';
            }
            $does_match = true;
            $sortable_index .= str_pad($matches, 4, '0', STR_PAD_LEFT);
        }
        if ($does_match) {
            return $sortable_index;
        }
        return "0";
    }
    
    /**
     *
     * @param type $course
     * @param type $a_sid
     * @param type $a_mid
     * @param type $a_ref_id
     * @return array
     */
    public static function doMappings($course, $a_sid, $a_mid, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT rid FROM ecs_cmap_rule ' .
                'WHERE sid = ' . $ilDB->quote($a_sid, 'integer') . ' ' .
                'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
                'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ' .
                'ORDER BY rid';
        $res = $ilDB->query($query);
        
        $level = 1;
        $last_level_category = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $rule = new ilECSCourseMappingRule($row->rid);
            if ($level == 1) {
                $last_level_category[] = $rule->getRefId();
            }

            $found_new_level = false;
            $new_level_cats = array();
            foreach ((array) $last_level_category as $cat_ref_id) {
                $refs = $rule->doMapping($course, $cat_ref_id);
                foreach ($refs as $new_ref_id) {
                    $found_new_level = true;
                    $new_level_cats[] = $new_ref_id;
                }
            }
            if ($found_new_level) {
                $last_level_category = $new_level_cats;
            }
            $level++;
        }
        
        return (array) $last_level_category;
    }
    
    /**
     * Do mapping
     * @param type $course
     * @param type $parent_ref
     */
    public function doMapping($course, $parent_ref)
    {
        if (!$this->isSubdirCreationEnabled()) {
            return array();
        }
        $values = ilECSMappingUtils::getCourseValueByMappingAttribute($course, $this->getAttribute());
        
        $childs = $this->tree->getChildsByType($parent_ref, 'cat');
        foreach ($values as $value) {
            $found = false;
            foreach ((array) $childs as $child) {
                // category already created
                if (strcmp($child['title'], $value) === 0) {
                    $found = true;
                    $category_references[] = $child['child'];
                    break;
                }
            }
            if (!$found) {
                $category_references[] = $this->createCategory($value, $parent_ref);
            }
        }
        return (array) $category_references;
    }
    
    /**
     * Create attribute category
     * @return int $ref_id;
     */
    protected function createCategory($a_title, $a_parent_ref)
    {
        // Create category
        $cat = new ilObjCategory();
        $cat->setOwner(SYSTEM_USER_ID);
        $cat->setTitle($a_title);
        $cat->create();
        $cat->createReference();
        $cat->putInTree($a_parent_ref);
        $cat->setPermissions($a_parent_ref);
        $cat->deleteTranslation($this->lng->getDefaultLanguage());
        $cat->addTranslation(
            $a_title,
            $cat->getLongDescription(),
            $this->lng->getDefaultLanguage(),
            1
        );
        return $cat->getRefId();
    }


    /**
     * Check if rule matches
     * @param type $course
     * @return int -1 does not match, 0 matches with disabled filter, >0 matches xth index in course attribute value.
     */
    public function matches($course)
    {
        if ($this->isFilterEnabled()) {
            $values = ilECSMappingUtils::getCourseValueByMappingAttribute($course, $this->getAttribute());
            $this->logger->dump($values);
            $index = 0;
            foreach ($values as $value) {
                $index++;
                foreach ($this->getFilterElements() as $filter_element) {
                    $this->logger->debug('Comparing ' . $value . ' with ' . $filter_element);
                    if (strcmp(trim($value), trim($filter_element)) === 0) {
                        $this->logger->debug($value . ' matches ' . $filter_element);
                        $this->logger->debug('Found index: ' . $index);
                        return $index;
                    }
                }
            }
            return -1;
        }
        return 0;
    }
    
    
    /**
     * Get rule instance by attribute
     * @param type $a_sid
     * @param type $a_mid
     * @param type $a_ref_id
     * @param type $a_att
     * @return \ilECSCourseMappingRule
     */
    public static function getInstanceByAttribute($a_sid, $a_mid, $a_ref_id, $a_att)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT rid FROM ecs_cmap_rule ' .
                'WHERE sid = ' . $ilDB->quote($a_sid, 'integer') . ' ' .
                'AND mid = ' . $ilDB->quote($a_mid, 'integer') . ' ' .
                'AND ref_id = ' . $ilDB->quote($a_ref_id, 'integer') . ' ' .
                'AND attribute = ' . $ilDB->quote($a_att, 'text');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilECSCourseMappingRule($row->rid);
        }
        return new ilECSCourseMappingRule();
    }
    
    public function setRuleId($a_rule_id)
    {
        $this->rid = $a_rule_id;
    }
    
    public function getRuleId()
    {
        return $this->rid;
    }
    
    public function setServerId($a_server_id)
    {
        $this->sid = $a_server_id;
    }
    
    public function getServerId()
    {
        return $this->sid;
    }

    public function setMid($a_mid)
    {
        $this->mid = $a_mid;
    }
    
    public function getMid()
    {
        return $this->mid;
    }
    
    public function setAttribute($a_att)
    {
        $this->attribute = $a_att;
    }
    
    public function getAttribute()
    {
        return $this->attribute;
    }
    
    public function setRefId($a_ref_id)
    {
        $this->ref_id = $a_ref_id;
    }
    
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    public function enableFilter($a_status)
    {
        $this->is_filter = $a_status;
    }
    
    public function isFilterEnabled()
    {
        return $this->is_filter;
    }
    
    public function setFilter($a_filter)
    {
        $this->filter = $a_filter;
    }
    
    public function getFilter()
    {
        return $this->filter;
    }
    
    public function getFilterElements()
    {
        return (array) $this->filter_elements;
    }
    
    public function enableSubdirCreation($a_stat)
    {
        $this->create_subdir = $a_stat;
    }
    
    public function isSubdirCreationEnabled()
    {
        return $this->create_subdir;
    }
    
    public function setSubDirectoryType($a_type)
    {
        $this->subdir_type = $a_type;
    }
    
    public function getSubDirectoryType()
    {
        return self::SUBDIR_VALUE;
    }
    
    public function setDirectory($a_dir)
    {
        $this->directory = $a_dir;
    }
    
    public function getDirectory()
    {
        return $this->directory;
    }
    
    public function delete()
    {
        $query = 'DELETE from ecs_cmap_rule ' .
            'WHERE rid = ' . $this->db->quote($this->getRuleId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }
    
    /**
     * Save a new rule
     * @return boolean
     */
    public function save()
    {
        $this->setRuleId($this->db->nextId('ecs_cmap_rule'));
        $query = 'INSERT INTO ecs_cmap_rule ' .
                '(rid,sid,mid,attribute,ref_id,is_filter,filter,create_subdir,subdir_type,directory) ' .
                'VALUES (' .
                $this->db->quote($this->getRuleId(), 'integer') . ', ' .
                $this->db->quote($this->getServerId(), 'integer') . ', ' .
                $this->db->quote($this->getMid(), 'integer') . ', ' .
                $this->db->quote($this->getAttribute(), 'text') . ', ' .
                $this->db->quote($this->getRefId(), 'integer') . ', ' .
                $this->db->quote($this->isFilterEnabled(), 'integer') . ', ' .
                $this->db->quote($this->getFilter(), 'text') . ', ' .
                $this->db->quote($this->isSubdirCreationEnabled(), 'integer') . ', ' .
                $this->db->quote($this->getSubDirectoryType(), 'integer') . ', ' .
                $this->db->quote($this->getDirectory(), 'text') . ' ' .
                ')';
        $this->db->manipulate($query);
        return $this->getRuleId();
    }
    
    /**
     * Update mapping rule
     */
    public function update()
    {
        $query = 'UPDATE ecs_cmap_rule ' . ' ' .
                'SET ' .
                'attribute = ' . $this->db->quote($this->getAttribute(), 'text') . ', ' .
                'ref_id = ' . $this->db->quote($this->getRefId(), 'integer') . ', ' .
                'is_filter = ' . $this->db->quote($this->isFilterEnabled(), 'integer') . ', ' .
                'filter = ' . $this->db->quote($this->getFilter(), 'text') . ', ' .
                'create_subdir = ' . $this->db->quote($this->isSubdirCreationEnabled(), 'integer') . ', ' .
                'subdir_type = ' . $this->db->quote($this->getSubDirectoryType(), 'integer') . ', ' .
                'directory = ' . $this->db->quote($this->getDirectory(), 'text') . ' ' .
                'WHERE rid = ' . $this->db->quote($this->getRuleId(), 'integer');
        $this->db->manipulate($query);
    }

    /**
     * Read db entries
     */
    protected function read()
    {
        if (!$this->getRuleId()) {
            return true;
        }
        $query = 'SELECT * from ecs_cmap_rule ' . ' ' .
            'WHERE rid = ' . $this->db->quote($this->getRuleId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setServerId($row->sid);
            $this->setMid($row->mid);
            $this->setRefId($row->ref_id);
            $this->setAttribute($row->attribute);
            $this->enableFilter($row->is_filter);
            $this->setFilter($row->filter);
            $this->enableSubdirCreation($row->create_subdir);
            $this->setSubDirectoryType($row->subdir_type);
            $this->setDirectory($row->directory);
        }
        
        $this->parseFilter();
    }
    
    /**
     * Parse filter
     */
    protected function parseFilter()
    {
        $filter = $this->getFilter();
        //$this->logger->debug('Original filter: ' . $filter);

        $escaped_filter = str_replace('\,', '#:#', $filter);
        //$this->logger->debug('Escaped filter: ' . $escaped_filter);

        $filter_elements = explode(',', $escaped_filter);
        foreach ((array) $filter_elements as $filter_element) {
            $replaced = str_replace('#:#', ',', $filter_element);
            if (strlen(trim($replaced))) {
                $this->filter_elements[] = $replaced;
            }
        }
        //$this->logger->dump($this->filter_elements);
    }
}
