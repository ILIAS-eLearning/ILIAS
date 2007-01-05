<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * Copyright (c) 2005-2007 Alfred Kohnert.
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 */

/**
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Business class for demonstration of current state of ILIAS SCORM 2004 
 * 
 * For security reasons this is not connected to ILIAS database
 * but uses a small fake database in slite2 format.
 * Waits on finishing other sub tasks before being connected to ILIAS.
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id: $
 * @copyright: (c) 2005-2007 Alfred Kohnert
 * 
 * "Playing" a SCORM Package to the end user
 * showing navigation and resources
 * tracking CMI API data     
 *  
 */ 

class ilSCORM13Player
{

	function __construct() {
	}
	
	public function fetchCMIData()
	{
		header('Content-Type: text/javascript; charset=UTF-8');
		$cmiData = getCMIData($this->userId, $this->packageId);
		die('var Userdata = ' . json_encode($cmiData)); 
	}
	
	public function persistCMIData()
	{
		header('Content-Type: text/json; charset=UTF-8');
		$cmiData = json_decode(file_get_contents('php://input'));
		// TODO validieren
		$return = setCMIData($userId, $packageId, $cmiData);
		die(json_encode($return)); 
	}
	
	private function getCMIData($userId, $packageId, $itemsFrom=null, $itemsTo=null) 
	{
		$where = "WHERE cp_node.user_id=$userId AND cp_node.slm_id=$packageId)"
		if (!is_int($itemsTo) 
		{
			$itemsTo = $itemsFrom;
		}
		if (is_int($itemsFrom)) 
		{
			$where .= " AND cp_node.cp_node_id BETWEEN ($itemsTo, $itemsTo)";
		}		
		$dataset = array(
			'userId' => $this->user_id, 
			'userName' => $this->userName,
			'packageId' => $this->packageId,
		);
		$dataset['cmi_node'] = ilSCORM13DB::query("SELECT * FROM cmi_node WHERE cmi_node.cmi_node_id IN (SELECT cmi_node.cmi_node_id FROM cmi_node, cp_node $where");
		$dataset['cmi_comment'] = ilSCORM13DB::query("SELECT * FROM cmi_comment WHERE cmi_comment.cmi_node_id IN (SELECT cmi_node.cmi_node_id FROM cmi_node, cp_node $where");
		$dataset['cmi_interaction'] = ilSCORM13DB::query("SELECT * FROM cmi_interaction WHERE cmi_interaction.cmi_node_id IN (SELECT cmi_node.cmi_node_id FROM cmi_node, cp_node $where");
		$dataset['cmi_correct_response'] = ilSCORM13DB::query("SELECT * FROM cmi_correct_response WHERE cmi_correct_response.cmi_interaction_id IN (SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction, cmi_node, cp_node $where");
		$dataset['cmi_objective'] = ilSCORM13DB::query("SELECT * FROM cmi_objective WHERE cmi_objective.cmi_interaction_id IN (SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction, cmi_node, cp_node WHERE cp_node.user_id=$this->userId AND cp_node.slm_id=$this->packageId) UNION SELECT * FROM cmi_objective WHERE cmi_objective.cmi_node_id IN (SELECT cmi_node.cmi_node_id FROM cmi_node, cp_node $where");
		return $dataset;
	}
	
	private function setCMIData($user_id, $packageId, $itemsFrom=null, $itemsTo=null) 
	{
		// vorhandene Daten als gelöscht markieren
		// Daten zeilenweise eintragen
		// IDs der eingetragenen Daten (item.identifier) zurückgegeben 
	}
	
}

?>
