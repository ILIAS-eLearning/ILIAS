<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once "./Services/Container/classes/class.ilContainer.php";
require_once("./Modules/OrgUnit/classes/class.ilOrgUnitImporter.php");
require_once('./Modules/OrgUnit/classes/Types/class.ilOrgUnitType.php');
require_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
require_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');


/**
 * Class ilObjOrgUnit
 *
 * Based on methods of ilObjCategoryGUI
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 * @author: Stefan Wanzenried <sw@studer-raimann.ch>
 *
 */
class ilObjOrgUnit extends ilContainer {

    const TABLE_NAME = 'orgu_data';

	protected static $root_ref_id;
	protected static $root_id;

	protected $employee_role;
	protected $superior_role;
    protected $ilDB;
    protected $rbacadmin;
    protected $ilAppEventHandler; 
    protected $ilLog;

    /**
     * Cache storing OrgUnit objects that have OrgUnit types with custom icons assigned
     * @var array
     */
    protected static $icons_cache;

    /**
     * ID of assigned OrgUnit type
     * @var int
     */
    protected $orgu_type_id = 0;

    /**
     * Advanced Metadata Values for this OrgUnit
     * @var array
     */
    protected $amd_data;


	public function __construct($a_id = 0,$a_call_by_reference = true){
        global $ilDB, $rbacadmin, $ilAppEventHandler, $lng, $ilLog, $rbacreview;

        $this->ilDB = $ilDB;
        $this->rbacadmin = $rbacadmin;
        $this->ilAppEventHandler = $ilAppEventHandler;
        $this->ilLog = $ilLog;
        $this->rbacreview = $rbacreview;

		$this->type = "orgu";
		$this->ilContainer($a_id,$a_call_by_reference);
	}

    public function read() {
        parent::read();
        /** @var ilDB $ilDB */
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $this->ilDB->quote($this->getId(), 'integer');
        $set = $this->ilDB->query($sql);
        if ($this->ilDB->numRows($set)) {
            $rec = $this->ilDB->fetchObject($set);
            $this->setOrgUnitTypeId($rec->orgu_type_id);
        }
    }

    public function create() {
        parent::create();
        $this->ilDB->insert(self::TABLE_NAME, array(
           'orgu_type_id' => array('integer', $this->getOrgUnitTypeId()),
           'orgu_id' => array('integer', $this->getId()),
        ));
    }

    public function update() {
        // gev-patch start
        // moved to after AMD update
        //parent::update();
        // gev-patch end
        $sql = 'SELECT * FROM ' . self::TABLE_NAME .' WHERE orgu_id = ' . $this->ilDB->quote($this->getId(), 'integer');
        $set = $this->ilDB->query($sql);
        if ($this->ilDB->numRows($set)) {
            $this->ilDB->update(self::TABLE_NAME, array(
                'orgu_type_id' => array('integer', $this->getOrgUnitTypeId()),
            ), array(
                'orgu_id' => array('integer', $this->getId()),
            ));
        } else {
            $this->ilDB->insert(self::TABLE_NAME, array(
                'orgu_type_id' => array('integer', $this->getOrgUnitTypeId()),
                'orgu_id' => array('integer', $this->getId()),
            ));
        }
        // Update selection for advanced meta data of the type
        if ($this->getOrgUnitTypeId()) {
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'orgu_type', $this->getOrgUnitType()->getAssignedAdvancedMDRecordIds());
        } else {
            // If no type is assigned, delete relations by passing an empty array
            ilAdvancedMDRecord::saveObjRecSelection($this->getId(), 'orgu_type', array());
        }
        
        // gev-patch start
        parent::update();

        $this->ilAppEventHandler->raise('Modules/OrgUnit'
            ,'update' 
            ,array('object' => $this
                  ,'obj_id' => $this->getId()
                  ,'ref_id' =>  $this->getRefId()
                  ,'orgu_title' => $this->getTitle()));
        // gev-patch end
        
    }

    public function getOrgUnitTypeId() {
        return $this->orgu_type_id;
    }

    public function getOrgUnitType() {
        return ilOrgUnitType::getInstance($this->getOrgUnitTypeId());
    }

    public function setOrgUnitTypeId($a_id) {
        $this->orgu_type_id = $a_id;
    }

    /**
     * Get the assigned AMD Values.
     * If a record_id is given, returns an array with all Elements (instances of ilADT objects) belonging to this record.
     * If no record_id is given, returns an associative array with record-IDs as keys and ilADT objects as values
     *
     * @param int $a_record_id
     * @return array
     */
    public function getAdvancedMDValues($a_record_id=0) {
        if (!$this->getOrgUnitTypeId()) {
            return array();
        }
        // Serve from cache?
        if (is_array($this->amd_data)) {
            if ($a_record_id) {
                return (isset($this->amd_data[$a_record_id])) ? $this->amd_data[$a_record_id] : array();
            } else {
                return $this->amd_data;
            }
        }
        /** @var ilAdvancedMDValues $amd_values */
        foreach(ilAdvancedMDValues::getInstancesForObjectId($this->getId(), 'orgu') as $record_id => $amd_values) {
            $amd_values = new ilAdvancedMDValues($record_id, $this->getId(), 'orgu_type', $this->getOrgUnitTypeId());
            $amd_values->read();
            $this->amd_data[$record_id] = $amd_values->getADTGroup()->getElements();
        }
        if ($a_record_id) {
            return (isset($this->amd_data[$a_record_id])) ? $this->amd_data[$a_record_id] : array();
        } else {
            return $this->amd_data;
        }
    }

    /**
     * Returns an array that maps from OrgUnit object IDs to its icon defined by the assigned OrgUnit type.
     * Keys = OrgUnit object IDs, values = Path to the icon
     * This allows to get the Icons of OrgUnits without loading the object (e.g. used in the tree explorer)
     *
     * @return array
     */
    public static function getIconsCache() {
        if (is_array(self::$icons_cache)) {
            return self::$icons_cache;
        }
        /** @var ilDB $ilDB */
        global $ilDB;
        $sql = 'SELECT orgu_id, ot.id AS type_id FROM orgu_data
                INNER JOIN orgu_types AS ot ON (ot.id = orgu_data.orgu_type_id)
                WHERE ot.icon IS NOT NULL';
        $set = $ilDB->query($sql);
        $icons_cache = array();
        while ($row = $ilDB->fetchObject($set)) {
            $type = ilOrgUnitType::getInstance($row->type_id);
            if ($type && is_file($type->getIconPath(true))) {
                $icons_cache[$row->orgu_id] = $type->getIconPath(true);
            }
        }
        self::$icons_cache = $icons_cache;
        return $icons_cache;
    }

	public static function getRootOrgRefId(){
		self::loadRootOrgRefIdAndId();
		return self::$root_ref_id;
	}

	public static function getRootOrgId(){
		self::loadRootOrgRefIdAndId();
		return self::$root_id;
	}

	private static function loadRootOrgRefIdAndId(){
		if(self::$root_ref_id === Null || self::$root_id === null){
            global $ilDB;
			$q = "SELECT o.obj_id, r.ref_id FROM object_data o
			INNER JOIN object_reference r ON r.obj_id = o.obj_id
			WHERE title = ".$ilDB->quote('__OrgUnitAdministration', 'text')."";
			$set = $ilDB->query($q);
			$res = $ilDB->fetchAssoc($set);
			self::$root_id = $res["obj_id"];
			self::$root_ref_id= $res["ref_id"];
		}
	}

	private function loadRoles(){
			if(!$this->employee_role || !$this->superior_role){
				$this->doLoadRoles();
			}

			if(!$this->employee_role || !$this->superior_role){
				$this->initDefaultRoles();
				$this->doLoadRoles();
				if(!$this->employee_role || !$this->superior_role)
					throw new Exception("The standard roles the orgu object with id: ".$this->getId()." aren't initialized or have been deleted, newly creating them didn't work!");
				else
					$this->ilLog->write("[".__FILE__.":".__LINE__."] The standard roles for the orgu obj with id: ".$this->getId()." were newly created as they couldnt be found.");
			}
	}

	private function doLoadRoles(){
		if(!$this->employee_role || !$this->superior_role){
    		$q = "SELECT obj_id, title FROM object_data WHERE title LIKE 'il_orgu_employee_".$this->ilDB->quote($this->getRefId(),"integer")."' OR title LIKE 'il_orgu_superior_".$this->ilDB->quote($this->getRefId(),"integer")."'";
    		$set = $this->ilDB->query($q);
    		while($res = $this->ilDB->fetchAssoc($set)){
    			if($res["title"] == "il_orgu_employee_".$this->getRefId())
    				$this->employee_role = $res["obj_id"];
    			elseif($res["title"] == "il_orgu_superior_".$this->getRefId())
    				$this->superior_role = $res["obj_id"];
    		}
    		if(!$this->employee_role || !$this->superior_role)
    			throw new Exception("The standard roles the orgu object with id: ".$this->getId()." aren't initialized or have been deleted!");
	    }	
    }

	public function assignUsersToEmployeeRole($user_ids){
		foreach($user_ids as $user_id)
        {
            $this->rbacadmin->assignUser($this->getEmployeeRole(), $user_id);

            $this->ilAppEventHandler->raise('Modules/OrgUnit',
                'assignUsersToEmployeeRole',
                array('object' => $this,
                    'obj_id' => $this->getId(),
                    'ref_id' =>  $this->getRefId(),
                    'role_id' => $this->getEmployeeRole(),
                    'user_id' => $user_id));
        }
	}

	public function assignUsersToSuperiorRole($user_ids){
		foreach($user_ids as $user_id)
        {
            $this->rbacadmin->assignUser($this->getSuperiorRole(), $user_id);

            $this->ilAppEventHandler->raise('Modules/OrgUnit',
                'assignUsersToSuperiorRole',
                array('object' => $this,
                    'obj_id' => $this->getId(),
                    'ref_id' =>  $this->getRefId(),
                    'role_id' => $this->getSuperiorRole(),
                    'user_id' => $user_id));
        }

	}

	public function deassignUserFromEmployeeRole($user_id){

		$this->rbacadmin->deassignUser($this->getEmployeeRole(), $user_id);

        $this->ilAppEventHandler->raise('Modules/OrgUnit',
            'deassignUserFromEmployeeRole',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'ref_id' =>  $this->getRefId(),
                'role_id' => $this->getEmployeeRole(),
                'user_id' => $user_id));
	}

	public function deassignUserFromSuperiorRole($user_id){

		$this->rbacadmin->deassignUser($this->getSuperiorRole(), $user_id);


        $this->ilAppEventHandler->raise('Modules/OrgUnit',
            'deassignUserFromSuperiorRole',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'ref_id' =>  $this->getRefId(),
                'role_id' => $this->getSuperiorRole(),
                'user_id' => $user_id));
	}

	/**
	 * @param int $employee_role
	 */
	public function setEmployeeRole($employee_role)
	{
		$this->employee_role = $employee_role;
	}

	public static function _exists($a_id, $a_reference = false){
		return parent::_exists($a_id, $a_reference, "orgu");
	}

	/**
	 * @return int
	 */
	public function getEmployeeRole()
	{
		$this->loadRoles();
		return $this->employee_role;
	}

	/**
	 * @param int $superior_role
	 */
	public function setSuperiorRole($superior_role)
	{
		$this->superior_role = $superior_role;
	}

	/**
	 * @return int
	 */
	public function getSuperiorRole()
	{
		$this->loadRoles();
		return $this->superior_role;
	}

	public function initDefaultRoles(){

		$rolf_obj = $this->createRoleFolder();

		// CREATE Employee ROLE
		$role_obj = $rolf_obj->createRole("il_orgu_employee_".$this->getRefId(),"Emplyee of org unit obj_no.".$this->getId());
// = $
// EMPLOYEE DOES NOT YET NEED A ROLE TEMPLATE.
//		// SET PERMISSION TEMPLATE OF NEW LOCAL ADMIN ROLE
//		$query = "SELECT obj_id FROM object_data ".
//			" WHERE type='rolt' AND title='il_orgu_employee'";
//
//		$res = $this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT);
//		$rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());
//
//		// SET OBJECT PERMISSIONS OF COURSE OBJECT
//		$ops = $this->rbacreview->getOperationsOfRole($role_obj->getId(),"orgu",$rolf_obj->getRefId());
//		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		// CREATE Superior ROLE
		$role_obj = $rolf_obj->createRole("il_orgu_superior_".$this->getRefId(),"Superior of org unit obj_no.".$this->getId());

		// SET PERMISSION TEMPLATE OF NEW LOCAL ADMIN ROLE
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type='rolt' AND title='il_orgu_superior'";

		$res = $this->ilDB->getRow($query, DB_FETCHMODE_OBJECT);
		$this->rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $this->rbacreview->getOperationsOfRole($role_obj->getId(),"orgu",$rolf_obj->getRefId());
		$this->rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());


        $this->ilAppEventHandler->raise('Modules/OrgUnit',
            'initDefaultRoles',
            array('object' => $this,
                  'obj_id' => $this->getId(),
                  'ref_id' =>  $this->getRefId(),
                  'role_superior_id' => $role_obj->getId(),
                  'role_employee_id' => $role_obj->getId()));

	}

	public function getTitle(){
		if(parent::getTitle() != "__OrgUnitAdministration")
			return parent::getTitle();
		else
			return $this->lng->txt("objs_orgu");
	}

	/**
	 * @return array This catches if by some means there is no translation.
	 */
	public function getTranslations(){
        $translations = array();

		$q = "SELECT * FROM object_translation WHERE obj_id = ".
            $this->ilDB->quote($this->getId(),'integer')." ORDER BY lang_default DESC";
		$r = $this->ilias->db->query($q);

		$num = 0;

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
        {
            $data["Fobject"][$num]= array("title"	=> $row->title,
                "desc"	=> $row->description,
                "lang"	=> $row->lang_code,
                'lang_default' => $row->lang_default,
            );
            $num++;
        }

		$translations = $data;

		if(!count($translations["Fobject"])){
			$this->addTranslation($this->getTitle(), "", $this->lng->getDefaultLanguage(), true);
			$translations["Fobject"][] = array("title"	=> $this->getTitle(),
				"desc"	=> "",
				"lang"	=> $this->lng->getDefaultLanguage());
		}
		return $translations;
	}


    /**
     * delete category and all related data
     *
     * @access	public
     * @return	boolean	true if all object data were removed; false if only a references were removed
     */
    function delete() {


        // always call parent delete function first!!
        if (!parent::delete())
        {
            return false;
        }

        // put here category specific stuff
        include_once('./Services/User/classes/class.ilObjUserFolder.php');
        ilObjUserFolder::_updateUserFolderAssignment($this->ref_id,USER_FOLDER_ID);

        $query = "DELETE FROM object_translation WHERE obj_id = ".$this->ilDB->quote($this->getId(),'integer');
        $res = $this->ilDB->manipulate($query);

        $this->ilAppEventHandler->raise('Modules/OrgUnit',
            'delete',
            array('object' => $this,
                'obj_id' => $this->getId()));

        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE orgu_id = ' . $this->ilDB->quote($this->getId(), 'integer');
        $this->ilDB->manipulate($sql);

        return true;
    }


    // remove all Translations of current OrgUnit
    function removeTranslations() {
        $query = "DELETE FROM object_translation WHERE obj_id= ".
            $this->ilDB->quote($this->getId(),'integer');
        $res = $this->ilDB->manipulate($query);
    }

    // remove translations of current OrgUnit
    function deleteTranslation($a_lang)
    {
        $query = "DELETE FROM object_translation WHERE obj_id= ".
            $this->ilDB->quote($this->getId(),'integer')." AND lang_code = ".
            $this->ilDB->quote($a_lang, 'text');
        $res = $this->ilDB->manipulate($query);
    }

    // add a new translation to current OrgUnit
    function addTranslation($a_title,$a_desc,$a_lang,$a_lang_default) {
        if (empty($a_title))
        {
            $a_title = "NO TITLE";
        }

        $query = "INSERT INTO object_translation ".
            "(obj_id,title,description,lang_code,lang_default) ".
            "VALUES ".
            "(".$this->ilDB->quote($this->getId(),'integer').",".
            $this->ilDB->quote($a_title,'text').",".$this->ilDB->quote($a_desc,'text').",".
            $this->ilDB->quote($a_lang,'text').",".$this->ilDB->quote($a_lang_default,'integer').")";
        $res = $this->ilDB->manipulate($query);

        return true;
    }

    // update a translation to current OrgUnit
    function updateTranslation($a_title,$a_desc,$a_lang,$a_lang_default) {    

        if (empty($a_title))
        {
            $a_title = "NO TITLE";
        }

        $query = "UPDATE object_translation SET ";


	    $query .= " title = ". $this->ilDB->quote($a_title,'text');


	    if($a_desc != "") {
		    $query .= ", description = ".$this->ilDB->quote($a_desc,'text')." ";
	    }

	    if($a_lang_default) {
		    $query .= ", lang_default = ".$this->ilDB->quote($a_lang_default,'integer')." ";
	    }

	    $query .=  " WHERE obj_id = ".$this->ilDB->quote($this->getId(),'integer')." AND lang_code = ".$this->ilDB->quote($a_lang,'text');
        $res = $this->ilDB->manipulate($query);

        return true;
    }

}
?>