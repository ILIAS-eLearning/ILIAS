<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
 * Class ilObjTaxonomyAdministration
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesTaxonomy
 */
class ilObjTaxonomyAdministration extends ilObject
{	
	public function __construct($a_id = 0, $a_call_by_reference = true)
	{
		$this->type = "taxs";
		parent::__construct($a_id,$a_call_by_reference);
	}

	public function delete()
	{
		// DISABLED
		return false;
	}
	
	protected function getPath($a_ref_id)
	{
		global $tree;
		
		$res = array();
		
		foreach($tree->getPathFull($a_ref_id) as $data)
		{			
			$res[] = $data['title'];
		}
				
		return $res;
	}
	
	public function getRepositoryTaxonomies()
	{
		global $ilDB, $tree;
					
		$res = array();
		
		include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
				
		$sql = "SELECT oref.ref_id, od.obj_id, od.type obj_type, od.title obj_title,".
			" tu.tax_id, od2.title tax_title, cs.value tax_status".
			" FROM object_data od".
			" JOIN object_reference oref ON (od.obj_id = oref.obj_id)".
			" JOIN tax_usage tu ON (tu.obj_id = od.obj_id)".	
			" JOIN object_data od2 ON (od2.obj_id = tu.tax_id)".
			" LEFT JOIN container_settings cs ON (cs.id = od.obj_id AND keyword = ".$ilDB->quote(ilObjectServiceSettingsGUI::TAXONOMIES, "text").")".
			" WHERE od.type = ".$ilDB->quote("cat", "text"). // categories only currently
			" AND tu.tax_id > ".$ilDB->quote(0, "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!$tree->isDeleted($row["ref_id"]))
			{
				$res[$row["tax_id"]][$row["obj_id"]] = array(				
					"tax_id" => $row["tax_id"]
					,"tax_title" => $row["tax_title"]
					,"tax_status" => (bool)$row["tax_status"]
					,"obj_title" => $row["obj_title"]
					,"obj_type" => $row["obj_type"]
					,"obj_id" => $row["obj_id"]
					,"ref_id" => $row["ref_id"]
					,"path" => $this->getPath($row["ref_id"])					
				);		
			}
		}
					
		return $res;
	}
} 

?>