<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
 * @author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>, Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
*/


require_once "./Modules/Scorm2004/classes/ilSCORM13Package.php";
require_once "./Modules/Scorm2004/classes/adlparser/SeqTreeBuilder.php";


class ilSCORM13Package
{

	const DB_ENCODE_XSL = './Modules/Scorm2004/templates/xsl/op/op-scorm13.xsl';
	const CONVERT_XSL   = './Modules/Scorm2004/templates/xsl/op/scorm12To2004.xsl';
	const DB_DECODE_XSL = './Modules/Scorm2004/templates/xsl/op/op-scorm13-revert.xsl';
	const VALIDATE_XSD  = './Modules/Scorm2004/templates/xsd/op/op-scorm13.xsd';
	
	const WRAPPER_HTML  = './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/GenericRunTimeWrapper.htm';
	const WRAPPER_JS  	= './Modules/Scorm2004/scripts/converter/GenericRunTimeWrapper1.0_aadlc/SCOPlayerWrapper.js';
	

	private $packageFile;
	private $packageFolder;
	private $packagesFolder;
	private $packageData;

	public $imsmanifest;
	public $manifest;
	public $diagnostic;
	public $status;
	public $packageId;
	public $packageName;
	public $packageHash;
	public $userId;

	private $idmap = array();
	private $progress = 0.0;

	static private $elements = array(
		'cp' => array(
		'manifest',
		'organization',
		'item',
		'hideLMSUI',
		'resource',
		'file',
		'dependency',
		'sequencing',
		'rule',
		'auxilaryResource',
		'condition',
		'mapinfo',
		'objective',
	),
		'cmi' => array(
		'comment',
		'correct_response',
		'interaction',
		'node',
		'objective',
	),
	);

	public function __construct($packageId = null)
	{
		$this->packagesFolder = IL_OP_PACKAGES_FOLDER;
		$this->load($packageId);
		$this->userId = $GLOBALS['USER']['usr_id'];	  	
	}
	
	public function load($packageId)
	{
		global $ilDB;
		
		if (!is_numeric($packageId))
		{
			return false;
		}
		
		$set = $ilDB->query("SELECT * FROM sahs_lm WHERE id = ".$ilDB->quote($packageId));
		$lm_data = $set->fetchRow(DB_FETCHMODE_ASSOC);
		$set = $ilDB->query("SELECT * FROM cp_package WHERE obj_id = ".$ilDB->quote($packageId));
		$pg_data = $set->fetchRow(DB_FETCHMODE_ASSOC);

		$this->packageData = array_merge($lm_data, $pg_data);
		$this->packageId = $packageId;
		$this->packageFolder = $this->packagesFolder . '/' . $packageId;
		$this->packageFile = $this->packageFolder . '.zip';
		$this->imsmanifestFile = $this->packageFolder . '/' . 'imsmanifest.xml';
		return true;
	}

	public function rollback()
	{
		$this->setProgress(0, 'Rolling back...');
		$this->dbRemoveAll();
		if (is_dir($this->packageFolder))
		dir_delete($this->packageFolder);
		if (is_file($this->packageFile))
		@unlink($this->packageFile);
		$this->setProgress(0, 'Roll back finished: Ok. ');
	}

	public function exportZIP()
	{
		header('content-type: application/zip');
		header('content-disposition: attachment; filename="' . basename($this->packageFile) . '"');
		readfile($this->packageFile);
	}

	/**
	 * Export as internal XML
	 */
	public function exportXML()
	{
		global $ilDB;
		
		header('content-type: text/xml');
		header('content-disposition: attachment; filename="manifest.xml"');

		//$row = ilSCORM13DB::getRecord("cp_package", "obj_id",$this->packageId);
		$set = $ilDB->query("SELECT * FROM cp_package WHERE obj_id = ".$ilDB->quote($this->$packageId));
		$row = $set->fetchRow(DB_FETCHMODE_ASSOC);

		print($row["xmldata"]);
	}


	/**
	* Imports an extracted SCORM 2004 module from ilias-data dir into database
	*
	* @access       public
	* @return       string title of package
	*/
	public function il_import($packageFolder,$packageId,$ilias,$validate,$reimport=false){
		global $ilDB, $ilLog;
		
		
		if ($reimport===true) {
			$this->packageId = $packageId;
			$this->dbRemoveAll();
		}
		
	  	$this->packageFolder=$packageFolder;
	  	$this->packageId=$packageId;
	  	$this->imsmanifestFile = $this->packageFolder . '/' . 'imsmanifest.xml';
	  	//step 1 - parse Manifest-File and validate
	  	$this->imsmanifest = new DOMDocument;
	  	$this->imsmanifest->async = false;
	  	if (!@$this->imsmanifest->load($this->imsmanifestFile))
	  	{
	  		$this->diagnostic[] = 'XML not wellformed';
	  		return false;
	  	}
    
//		$ilLog->write("SCORM: parse");

	  	//step 2 tranform
	  	$this->manifest = $this->transform($this->imsmanifest, self::DB_ENCODE_XSL);
    
	  	if (!$this->manifest)
	  	{
	  		$this->diagnostic[] = 'Cannot transform into normalized manifest';
	  		return false;
	  	}
//		$ilLog->write("SCORM: normalize");
	
	  	//step 3 validation -just for normalized XML
		if ($validate=="y") {
	  		if (!$this->validate($this->manifest, self::VALIDATE_XSD))
	  		{
			
				$ilias->raiseError("<b>The uploaded SCORM 1.2 / SCORM 2004 is not valid. You can try to import the package without the validation option checked on your own risk. </b><br><br>Validation Error(s):</b><br> Normalized XML is not conform to ". self::VALIDATE_XSD,
				$ilias->error_obj->WARNING);
			//	
				//$this->diagnostic[] = 'normalized XML is not conform to ' . self::VALIDATE_XSD;
	  		//	return false;
	  		}
		}
//		$ilLog->write("SCORM: validate");
	
//	  	ilSCORM13DB::begin();
	  	$this->dbImport($this->manifest);
//		$ilLog->write("SCORM: import new");
	
//	  	ilSCORM13DB::commit();
	  	//step 5
	  	$x = simplexml_load_string($this->manifest->saveXML());
	  	// add database values from package and sahs_lm records as defaults
	  	$x['persistPreviousAttempts'] = $this->packageData['persistPreviousAttempts'];
	  	$x['online'] = $this->packageData['online'];
	  	$x['defaultLessonMode'] = $this->packageData['default_lesson_mode'];
	  	$x['credit'] = $this->packageData['credit'];
	  	$x['autoReview'] = $this->packageData['auto_review'];
	  	$j = array();
	  	// first read resources into flat array to resolve item/identifierref later
	  	$r = array();
	  	foreach ($x->resource as $xe)
	  	{
	  		$r[strval($xe['id'])] = $xe;
	  		unset($xe);
	  	}
	  	// iterate through items and set href and scoType as activity attributes
	  	foreach ($x->xpath('//*[local-name()="item"]') as $xe)
	  	{
	  		// get reference to resource and set href accordingly
	  		if ($b = $r[strval($xe['resourceId'])])
	  		{
	  			$xe['href'] = strval($b['base']) . strval($b['href']);
	  			unset($xe['resourceId']);
	  			if (strval($b['scormType'])=='sco') $xe['sco'] = true;
	  		}
	  	}
	  	// iterate recursivly through activities and build up simple php object
	  	// with items and associated sequencings
	  	// top node is the default organization which is handled as an item
	  	self::jsonNode($x->organization, $j['item']);
	  	foreach($x->sequencing as $s)
	  	{
	  		self::jsonNode($s, $j['sequencing'][]);
	  	}
	  	// combined manifest+resources xml:base is set as organization base
	  	$j['item']['base'] = strval($x['base']);
	  	// package folder is base to whole playing process
	  	$j['base'] = $packageFolder . '/';
	  	$j['foreignId'] = floatval($x['foreignId']); // manifest cp_node_id for associating global (package wide) objectives
	  	$j['id'] = strval($x['id']); // manifest id for associating global (package wide) objectives
    	

		//last step - build ADL Activity tree
		$act = new SeqTreeBuilder();
		$adl_tree = $act->buildNodeSeqTree($this->imsmanifestFile);
		

		/*$ilDB->query("INSERT INTO cp_package ".
			"(obj_id, xmldata, jsdata, activitytree) VALUES ".
			"(".$ilDB->quote($this->packageId).",".
				$ilDB->quote($x->asXML()).",".
				$ilDB->quote(json_encode($j)).",".
				$ilDB->quote(json_encode($adl_tree)).")";*/
		$ilDB->query("UPDATE cp_package SET ".
			" xmldata = ".$ilDB->quote($x->asXML()).",".
			" jsdata = ".$ilDB->quote(json_encode($j)).",".
			" activitytree = ".$ilDB->quote(json_encode($adl_tree['tree'])).",".
			" global_to_system = ".$ilDB->quote($adl_tree['global']).
			" WHERE obj_id = ".$ilDB->quote($this->packageId));
				
	  	/*ilSCORM13DB::setRecord(
			'cp_package', array(
	  		'obj_id' => $this->packageId,
	  		'xmldata' => $x->asXML(),
	  		'jsdata' => json_encode($j),
			'activitytree' => json_encode($adl_tree)
	  		), 'obj_id');*/
//		$ilLog->write("SCORM: import update");
	
	  	return $j['item']['title'];
	  }
	/**
	 */
	
	private function setProgress($progress, $msg = '')
	{
		$this->progress = $progress;
		$this->diagnostic[] = $msg;
	}

	/**
	 * Helper for UploadAndImport
	 * Recursively copies values from XML into PHP array for export as json
	 * Elements are translated into sub array, attributes into literals
	 * @param xml element to process
	 * @param reference to array object where to copy values
	 * @return void
	 */
	public function jsonNode($node, &$sink)
	{
		foreach ($node->attributes() as $k => $v)
		{
			// cast to boolean and number if possible
			$v = strval($v);
			if ($v==="true") $v = true;
			else if ($v==="false") $v = false;
			else if (is_numeric($v)) $v = (float) $v;
			$sink[$k] = $v;
		}
		foreach ($node->children() as $name => $child)
		{
			self::jsonNode($child, $sink[$name][]); // RECURSION
		}
	}

	public function dbImport($node, &$lft=1, $depth=1, $parent=0)
	{
		global $ilDB;
		
		switch ($node->nodeType)
		{
			case XML_DOCUMENT_NODE:
			
				// insert into cp_package
				/*ilSCORM13DB::setRecord('cp_package', array(
				'obj_id' => $this->packageId,
				'identifier' => $this->packageName,
				'persistPreviousAttempts' => 0,
				'settings' => '',
				));*/
				$ilDB->query("REPLACE INTO cp_package ".
					"(obj_id, identifier, persistPreviousAttempts, settings) VALUES ".
					"(".$ilDB->quote($this->packageId).",".
					$ilDB->quote($this->packageName).",".
					$ilDB->quote(0).",".
					$ilDB->quote("").
					")");
				// run sub nodes
				$this->dbImport($node->documentElement); // RECURSION
				break;

			case XML_ELEMENT_NODE:
				if ($node->nodeName==='manifest')
				{
					if ($node->getAttribute('uri')=="")
					{
						// default URI is md5 hash of zip file, i.e. packageHash
						$node->setAttribute('uri', 'md5:' . $this->packageHash);
					}
				}
				// insert into cp_node
				/*$cp_node_id = ilSCORM13DB::setRecord('cp_node', array(
				'slm_id' => $this->packageId,
				'nodeName' => $node->nodeName,
				), 'cp_node_id');*/
				$ilDB->query("INSERT INTO cp_node ".
					"(slm_id, nodeName) VALUES ".
					"(".$ilDB->quote($this->packageId).",".
					$ilDB->quote($node->nodeName).
					")");
				$cp_node_id = $ilDB->getLastInsertId();

				// insert into cp_tree
				/*
				ilSCORM13DB::setRecord('cp_tree', array(
				'child' => $cp_node_id,
				'depth' => $depth,
				'lft' => $lft++,
				'obj_id' => $this->packageId,
				'parent' => $parent,
				'rgt' => 0,
				));*/
				$ilDB->query("INSERT INTO cp_tree ".
					"(child, depth, lft, obj_id, parent, rgt) VALUES ".
					"(".
					$ilDB->quote($cp_node_id).",".
					$ilDB->quote($depth).",".
					$ilDB->quote($lft++).",".
					$ilDB->quote($this->packageId).",".
					$ilDB->quote($parent).",".
					$ilDB->quote(0).
					")");

				// insert into cp_*
				//$a = array('cp_node_id' => $cp_node_id);
				$names = array('cp_node_id');
				$values = array($ilDB->quote($cp_node_id));
				foreach ($node->attributes as $attr)
				{
					//$a[$attr->name] = $attr->value;
					$names[] = "`".$attr->name."`";
					$values[] = $ilDB->quote($attr->value);
				}
				//ilSCORM13DB::setRecord('cp_' . $node->nodeName, $a);
				$ilDB->query("INSERT INTO cp_".strtolower($node->nodeName).
					" (".implode($names, ",").") VALUES ".
					"(".implode($values, ",").
					")");
				
				$node->setAttribute('foreignId', $cp_node_id);
				$this->idmap[$node->getAttribute('id')] = $cp_node_id;

				// run sub nodes
				foreach($node->childNodes as $child)
				{
					$this->dbImport($child, $lft, $depth+1, $cp_node_id); // RECURSION
				}

				// update cp_tree (rgt value for pre order walk in sql tree)
				$ilDB->query('UPDATE cp_tree SET rgt='.$ilDB->quote($lft++).
					' WHERE child = '.$ilDB->quote($cp_node_id));
				//ilSCORM13DB::exec($q);

				break;
		}
	}

	/**
	 * add new sahs and package record
	 */
	public function dbAddNew()
	{
		global $ilDB;
		
		//$this->packageId = 100;
		//return true;
		//ilSCORM13DB::getRecord('sahs_lm', array());
		//	$this->packageId = ilSCORM13DB::getRecord('sahs_lm', array());
/*		ilSCORM13DB::setRecord('cp_package', array(
		'obj_id' => $this->packageId,
		'xmldata' => $x->asXML(),
		'jsdata' => json_encode($j),
		), 'obj_id');
*/
		$ilDB->query("INSERT INTO cp_package ".
			"(obj_id, xmldata, jsdata) VALUES ".
			"(".
				$ilDB->quote($this->packageId).",".
				$ilDB->quote($x->asXML()).",".
				$ilDB->quote(json_encode($j)).")");

		return true;
	}


	public function removeCMIData()
	{
		global $ilDB;
		//cmi nodes
		$cmi_nodes = array();
		$set_cmi = $ilDB->query("SELECT cmi_node.cmi_node_id FROM cmi_node, cp_node WHERE(
							 cp_node.slm_id=".$ilDB->quote($this->packageId)." AND cmi_node.cp_node_id=cp_node.cp_node_id)");
		while ($data = $set_cmi->fetchRow(DB_FETCHMODE_ASSOC)) {
			array_push($cmi_nodes,$data['cmi_node_id']);
		}
		$cmi_nodes_impl = implode(",",ilUtil::quoteArray($cmi_nodes));		
		
		//cmi interaction nodes
		$cmi_inodes = array();
		$set_icmi = $ilDB->query("SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction, cmi_node, cp_node WHERE(
			 				cp_node.slm_id=".$ilDB->quote($this->packageId)." AND cmi_node.cp_node_id=cp_node.cp_node_id
							AND cmi_node.cmi_node_id=cmi_interaction.cmi_node_id)");
		while ($data = $set_icmi->fetchRow(DB_FETCHMODE_ASSOC)) {
			array_push($cmi_inodes,$data['cmi_interaction_id']);
		}
		$cmi_inodes_impl = implode(",",ilUtil::quoteArray($cmi_inodes));
		
		//response
		$ilDB->query("DELETE FROM cmi_correct_response WHERE cmi_correct_response.cmi_interaction_id IN
					  ($cmi_inodes_impl);");
			
		//objective interaction
		$ilDB->query("DELETE FROM cmi_objective WHERE cmi_objective.cmi_interaction_id IN ($cmi_inodes_impl);");
		
		//objective
		$ilDB->query("DELETE FROM cmi_objective WHERE cmi_objective.cmi_node_id IN ($cmi_nodes_impl);");
		
		//interaction
		$ilDB->query("DELETE FROM cmi_interaction WHERE cmi_interaction.cmi_node_id IN ($cmi_nodes_impl);");

		//comment
		$ilDB->query("DELETE FROM cmi_comment WHERE cmi_comment.cmi_node_id IN ($cmi_nodes_impl);");
		
		//node
		$ilDB->query("DELETE FROM cmi_node WHERE cmi_node.cmi_node_id IN ($cmi_nodes_impl)");
	
	}
	
	
	public function removeCPData()
	{
		global $ilDB,$ilLog;
		
		
		//get relevant nodes
		$cp_nodes = array();
		$set_cp = $ilDB->query("SELECT cp_node.cp_node_id FROM cp_node WHERE cp_node.slm_id=".
							 $ilDB->quote($this->packageId));
		while ($data = $set_cp->fetchRow(DB_FETCHMODE_ASSOC)) {
			array_push($cp_nodes,$data['cp_node_id']);
		}
		$cp_nodes_impl = implode(",",ilUtil::quoteArray($cp_nodes));
				
		
		//remove package data
		foreach (self::$elements['cp'] as $t)
		{
			$t = 'cp_' . $t;
			$ilDB->query("DELETE FROM $t WHERE $t.cp_node_id IN ($cp_nodes_impl);");
		}
		
		// remove CP structure entries in tree and node
		$ilDB->query("DELETE FROM cp_tree WHERE cp_tree.obj_id=".$ilDB->quote($this->packageId));

		$ilDB->query("DELETE FROM cp_node WHERE cp_node.slm_id=".$ilDB->quote($this->packageId));
		
		// remove general package entry
		
		$ilDB->query("DELETE FROM cp_package WHERE cp_package.obj_id=".	$ilDB->quote($this->packageId));
	
	}
	

	public function dbRemoveAll()
	{
		//dont change order of calls
		$this->removeCMIData();
		$this->removeCPData();
	}

	public function transform($inputdoc, $xslfile, $outputpath = null)
	{
		$xsl = new DOMDocument;
		$xsl->async = false;
		if (!@$xsl->load($xslfile))
		{
			die('ERROR: load StyleSheet ' . $xslfile);
		}
		$prc = new XSLTProcessor;
		$r = @$prc->importStyleSheet($xsl);
		if (false===@$prc->importStyleSheet($xsl))
		{
			die('ERROR: importStyleSheet ' . $xslfile);
		}
		if ($outputpath)
		{
			file_put_contents($outputpath, $prc->transformToXML($inputdoc));
		}
		else
		{
			return $prc->transformToDoc($inputdoc);
		}
	}

	public function validate($doc, $schema)
	{
		libxml_use_internal_errors(true);
		$return = @$doc->schemaValidate($schema);
		if (!$return)
		{
			$levels = array(
			LIBXML_ERR_ERROR => 'Error',
			LIBXML_ERR_FATAL => 'Fatal Error'
			);
			foreach (libxml_get_errors() as $error)
			{
				$level = $levels[$error->level];
				if (isset($level))
				{
					$message = trim($error->message);
					//$file = $error->file ? 'in <b>' . $error->file . '</b>' : '';
					// $error->code:
					$this->diagnostic[] = "XSLT $level (Line $error->line) $message";
				}
			}
			libxml_clear_errors();
		}
		libxml_use_internal_errors(false);
		return $return;
	}
}

?>
