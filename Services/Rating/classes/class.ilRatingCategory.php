<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesRating Services/Rating
 */

/**
* Class ilRatingCategory
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesRating
*/
class ilRatingCategory
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $id; // [int] sequence
    protected $parent_id; // [int] parent object
    protected $title; // [string]
    protected $description; // [string]
    protected $pos; // [int] order
    
    /**
     * Constructor
     *
     * @param int  $a_id
     */
    public function __construct($a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read($a_id);
    }
    
    /**
     * Set id
     *
     * @param int $a_value
     */
    public function setId($a_value)
    {
        $this->id = (int) $a_value;
    }
            
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set parent id
     *
     * @param int $a_value
     */
    public function setParentId($a_value)
    {
        $this->parent_id = (int) $a_value;
    }
            
    /**
     * Get parent object id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }
    
    /**
     * Set title
     *
     * @param string $a_value
     */
    public function setTitle($a_value)
    {
        $this->title = (string) $a_value;
    }
            
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set description
     *
     * @param string $a_value
     */
    public function setDescription($a_value)
    {
        $this->description = (string) $a_value;
    }
            
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Set position
     *
     * @param int $a_value
     */
    public function setPosition($a_value)
    {
        $this->pos = (int) $a_value;
    }
            
    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->pos;
    }
    
    /**
     * Load db entry
     *
     * @param int $a_id
     */
    protected function read($a_id)
    {
        $ilDB = $this->db;
        
        $a_id = (int) $a_id;
        if ($a_id) {
            $sql = "SELECT * FROM il_rating_cat" .
                " WHERE id = " . $ilDB->quote($a_id, "integer");
            $set = $ilDB->query($sql);
            $row = $ilDB->fetchAssoc($set);
            if ($row["id"]) {
                $this->setId($row["id"]);
                $this->setParentId($row["parent_id"]);
                $this->setTitle($row["title"]);
                $this->setDescription($row["description"]);
                $this->setPosition($row["pos"]);
            }
        }
    }
    
    /**
     * Parse properties into db definition
     *
     * @return array
     */
    protected function getDBProperties()
    {
        // parent id must not change
        $fields = array("title" => array("text", $this->getTitle()),
                "description" => array("text", $this->getDescription()),
                "pos" => array("integer", $this->getPosition()));
    
        return $fields;
    }
    
    /**
     * Update db entry
     */
    public function update()
    {
        $ilDB = $this->db;
        
        if ($this->getId()) {
            $fields = $this->getDBProperties();
            
            $ilDB->update(
                "il_rating_cat",
                $fields,
                array("id" => array("integer", $this->getId()))
            );
        }
    }
    
    /**
     * Create db entry
     */
    public function save()
    {
        $ilDB = $this->db;
        
        $id = $ilDB->nextId("il_rating_cat");
        $this->setId($id);
        
        // append
        $sql = "SELECT max(pos) pos FROM il_rating_cat" .
            " WHERE parent_id = " . $ilDB->quote($this->getParentId(), "integer");
        $set = $ilDB->query($sql);
        $pos = $ilDB->fetchAssoc($set);
        $pos = $pos["pos"];
        $this->setPosition($pos + 10);
        
        $fields = $this->getDBProperties();
        $fields["id"] = array("integer", $id);
        $fields["parent_id"] = array("integer", $this->getParentId());
        
        $ilDB->insert("il_rating_cat", $fields);
    }
    
    /**
     * Delete db entry
     *
     * @param int $a_id
     */
    public static function delete($a_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        if ((int) $a_id) {
            $sql = "DELETE FROM il_rating" .
                " WHERE category_id = " . $ilDB->quote($a_id, "integer");
            $ilDB->manipulate($sql);
            
            $sql = "DELETE FROM il_rating_cat" .
                " WHERE id = " . $ilDB->quote($a_id, "integer");
            $ilDB->manipulate($sql);
        }
    }
    
    /**
     * Get all categories for object
     *
     * @param int $a_parent_obj_id
     * @return array
     */
    public static function getAllForObject($a_parent_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $cats = array();
        
        $sql = "SELECT * FROM il_rating_cat" .
            " WHERE parent_id = " . $ilDB->quote($a_parent_obj_id, "integer") .
            " ORDER BY pos";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $cats[] = $row;
        }
        
        return $cats;
    }
    
    /**
     * Delete all categories for object
     *
     * @param int $a_parent_obj_id
     */
    public static function deleteForObject($a_parent_obj_id)
    {
        if ((int) $a_parent_obj_id) {
            foreach (self::getAllForObject($a_parent_obj_id) as $item) {
                self::delete($item["id"]);
            }
        }
    }
}
