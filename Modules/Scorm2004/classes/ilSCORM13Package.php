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
require_once "./Modules/Scorm2004/classes/class.ilSCORM2004Chapter.php";
require_once "./Modules/Scorm2004/classes/class.ilSCORM2004Sco.php";
require_once "./Modules/Scorm2004/classes/class.ilSCORM2004PageNode.php";
require_once "./Modules/Scorm2004/classes/adlparser/SeqTreeBuilder.php";
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMTree.php");

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
	private $slm;
	private $slm_tree;

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

		$lm_set = $ilDB->queryF('SELECT * FROM sahs_lm WHERE id = %s', array('integer'), array($packageId));
		$lm_data = $ilDB->fetchAssoc($lm_set);
		$pg_set = $ilDB->queryF('SELECT * FROM cp_package WHERE obj_id  = %s', array('integer'), array($packageId));
		$pg_data = $ilDB->fetchAssoc($lm_set);
		
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
		$statement = $ilDB->queryF('SELECT * FROM cp_package WHERE obj_id = %s', 
		array('integer'), array($this->packageId));
		$row = $ilDB->fetchAssoc($statement);
		
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
/*nk	  	
 		$x['persistPreviousAttempts'] = $this->packageData['persistPreviousAttempts'];
	  	$x['online'] = $this->packageData['online'];
*/		
	  	$x['persistPreviousAttempts'] = $this->packageData['persistprevattempts'];  	
	  	$x['online'] = $this->packageData['c_online'];
	  	
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

		$statment = $ilDB->queryF('
			UPDATE cp_package 
			SET	xmldata = %s,
				jsdata = %s,
			 	activitytree = %s,
			 	global_to_system = %s
			WHERE obj_id = %s', 
		array(	'text',
				'text',
				'text',
				'integer',
				'integer'), 
		array(	$x->asXML(),
				json_encode($j),
				json_encode($adl_tree['tree']),
				$adl_tree['global'],
				$this->packageId ));
		
		
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
	* Imports an extracted SCORM 2004 module from ilias-data dir into database
	*
	* @access       public
	* @return       string title of package
	*/
	public function il_importSco($packageId, $sco_id, $packageFolder)
	{
		global $ilDB, $ilLog;
		
		
	  	$this->packageFolder=$packageFolder;
	  	$this->packageId=$packageId;
	  	$this->imsmanifestFile = $this->packageFolder . '/' . 'index.xml';
	  	$this->imsmanifest = new DOMDocument;
	  	$this->imsmanifest->async = false;
	  	
	  	if (!@$this->imsmanifest->load($this->imsmanifestFile))
	  	{
	  		$this->diagnostic[] = 'XML not wellformed';
	  		return false;
	  	}
	  	
	  	$slm = new ilObjSCORM2004LearningModule($packageId,false);
	  	$sco = new ilSCORM2004Sco($slm,$sco_id);
	  	$this->dbImportSco($slm,$sco);
	  	
		return "";
	}

	public function il_importLM($slm, $packageFolder)
	{
		global $ilDB, $ilLog;
		
	  	$this->packageFolder=$packageFolder;
	  	$this->packageId=$slm->getId();
	  	$this->imsmanifestFile = $this->packageFolder . '/' . 'imsmanifest.xml';
	  	$this->imsmanifest = new DOMDocument;
	  	$this->imsmanifest->async = false;
	  	$this->slm = $slm;
	  	if (!@$this->imsmanifest->load($this->imsmanifestFile))
	  	{
	  		$this->diagnostic[] = 'XML not wellformed';
	  		return false;
	  	}
		$this->dbImportLM(simplexml_import_dom($this->imsmanifest->documentElement),$this->slm);
	  	//die($slm->title);
		return $slm->title;
	}
	
	function dbImportLM($node, $parent_id)
	{
	
		switch($node->getName())
		{
			case "manifest":
				$this->slm_tree =& new ilTree($this->slm->getId());
				$this->slm_tree->setTreeTablePK("slm_id");
				$this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
				$this->slm_tree->addTree($this->slm->getId(), 1);
				//add seqinfo for rootNode
				include_once ("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
				$seq_info = new ilSCORM2004Sequencing($this->slm->getId(),true);
				$seq_info->insert();
		  		$doc = simplexml_load_file($this->packageFolder . '/' . 'index.xml');
		  		$l = $doc->xpath ( "/ContentObject/MetaData" );
				if($l[0])
			  	{
			  		include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
			  		$mdxml =& new ilMDXMLCopier($l[0]->asXML(),$this->slm->getId(),$this->slm->getId(),$this->slm->getType());
					$mdxml->startParsing();
					$mdxml->getMDObject()->update();
			  	}
			  	
				break;
			case "organization":
				$this->slm->title=$node->title;
				break;
			case "item":
				$a = $node->attributes();
				if(preg_match("/il_\d+_chap_\d+/",$a['identifier']))
				{
					$chap=& new ilSCORM2004Chapter($this->slm);
					$chap->setTitle($node->title);
					$chap->setSLMId($this->slm->getId());
					$chap->create(true);
					ilSCORM2004Node::putInTree($chap, "", "");
					$parent_id=$chap->getId();
				}
				if(preg_match("/il_\d+_sco_(\d+)/",$a['identifier'],&$match))
				{
					$sco = new ilSCORM2004Sco($this->slm);
					$sco->setTitle($node->title);
					$sco->setSLMId($this->slm->getId());
					$sco->create();
					ilSCORM2004Node::putInTree($sco, $parent_id, "");
					$newPack = new ilSCORM13Package();
					$newPack->il_importSco($this->slm->getId(),$sco->getId(),$this->packageFolder."/".$match[1]);
					$parent_id = $sco->getId();
				}
				
				break;
		}
		//if($node->nodeType==XML_ELEMENT_NODE)
		{
			foreach($node->children() as $child)
			{
				 $this->dbImportLM($child,$parent_id);
			}
		}
	}

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

	public function dbImportSco($slm, $sco) 
	{
		$qtis = array();
		$d = ilUtil::getDir ( $this->packageFolder );
		foreach ( $d as $f ) {
			//continue;
			if ($f [type] == 'file' && substr ( $f [entry], 0, 4 ) == 'qti_') {
				include_once "./Services/QTI/classes/class.ilQTIParser.php";
				include_once "./Modules/Test/classes/class.ilObjTest.php";
				

				$qtiParser = new ilQTIParser ( $this->packageFolder . "/" . $f [entry], IL_MO_VERIFY_QTI, 0, "" );
				$result = $qtiParser->startParsing ();
				$founditems = & $qtiParser->getFoundItems ();
				//					die(print_r($founditems));
				foreach ( $founditems as $qp ) {
					$newObj = new ilObjTest ( 0, true );
					$newObj->setType ( $qp ['type'] );
					$newObj->setTitle ( $qp ['title'] );
					$newObj->create ( true );
					$newObj->createReference ();
					$newObj->putInTree ($_GET ["ref_id"]);
					$newObj->setPermissions ( $sco->getId ());
					$newObj->notify ("new", $_GET["ref_id"], $sco->getId (), $_GET["ref_id"], $newObj->getRefId () );
					$newObj->mark_schema->flush ();
					$qtiParser = new ilQTIParser ( $this->packageFolder . "/" . $f [entry], IL_MO_PARSE_QTI, 0, "" );
					$qtiParser->setTestObject ( $newObj );
					$result = $qtiParser->startParsing ();
					$newObj->saveToDb ();
					$qtis = array_merge($qtis, $qtiParser->getImportMapping());
				}
			}
		}
		include_once 'class.ilSCORM2004Page.php';
		$doc = new SimpleXMLElement ( $this->imsmanifest->saveXml () );
		$l = $doc->xpath ( "/ContentObject/MetaData" );
		if($l[0])
	  	{
	  		include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
	  		$mdxml =& new ilMDXMLCopier($l[0]->asXML(),$slm->getId(),$sco->getId(),$sco->getType());
			$mdxml->startParsing();
			$mdxml->getMDObject()->update();
	  	}
		$l = $doc->xpath ( "/ContentObject/PageObject" );
		foreach ( $l as $page_xml ) {
			$tnode = $page_xml->xpath ( 'MetaData/General/Title' );
			$page = new ilSCORM2004PageNode ( $slm );
			$page->setTitle ( $tnode [0] );
			$page->setSLMId ( $slm->getId () );
			$page->create ();
			ilSCORM2004Node::putInTree ( $page, $sco->getId (), $target );
			$tnode = $page_xml->xpath ( "//MediaObject/MediaAlias" );
			foreach ( $tnode as $ttnode ) {
				include_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$media_object = new ilObjMediaObject ( );
				$media_object->setTitle ( "" );
				$media_object->setDescription ( "" );
				$media_object->create ();
				
				// determine and create mob directory, move uploaded file to directory
				$media_object->createDirectory ();
				$mob_dir = ilObjMediaObject::_getDirectory ( $media_object->getId () );
				if (! file_exists ( $this->packageFolder . "/objects/" . $ttnode [OriginId] ))
					continue;
				$d = ilUtil::getDir ( $this->packageFolder . "/objects/" . $ttnode [OriginId] );
				foreach ( $d as $f ) {
					if ($f [type] == 'file') {
						$media_item = & new ilMediaItem ( );
						$media_object->addMediaItem ( $media_item );
						$media_item->setPurpose ( "Standard" );
						
						$tmp_name = $this->packageFolder . "/objects/" . $ttnode [OriginId] . "/" . $f [entry];
						$name = $f [entry];
						$file = $mob_dir . "/" . $name;
						copy ( $tmp_name, $file );
						
						// get mime type
						$format = ilObjMediaObject::getMimeType ( $file );
						$location = $name;
						// set real meta and object data
						$media_item->setFormat ( $format );
						$media_item->setLocation ( $location );
						$media_item->setLocationType ( "LocalFile" );
						$media_object->setTitle ( $name );
						$media_object->setDescription ( $format );
						
						if (ilUtil::deducibleSize ( $format )) {
							$size = getimagesize ( $file );
							$media_item->setWidth ( $size [0] );
							$media_item->setHeight ( $size [1] );
						}
						//$media_item->setHAlign("Left");
					}
				}
				
				ilUtil::renameExecutables ( $mob_dir );
				$media_object->update ();
				$ttnode [OriginId] = "il__mob_" . $media_object->getId ();
			}
			$pagex = new ilSCORM2004Page ( $page->getId () );
			$tnode = $page_xml->xpath ( 'PageContent' );
			$t = "<PageObject>";
			foreach ( $tnode as $ttnode )
				$t .= $ttnode->asXML ();
			$t .= "</PageObject>";
			foreach ($qtis as $old=>$q)
				$t = str_replace($old,'il__qst_'.$q['test'], $t);
			$pagex->setXMLContent ( $t );
			$pagex->updateFromXML ();
		}
	}

	public function dbImportSco($slm,$sco)
	{
		include_once 'class.ilSCORM2004Page.php';
		$doc = new SimpleXMLElement($this->imsmanifest->saveXml());
		$l = $doc->xpath("/ContentObject/PageObject");
		foreach($l as $page_xml)
	  	{
	  		$tnode = $page_xml->xpath('MetaData/General/Title');
	  		$page = new ilSCORM2004PageNode($slm);
			$page->setTitle($tnode[0]);
			$page->setSLMId($slm->getId());
			$page->create();
			ilSCORM2004Node::putInTree($page, $sco->getId(), $target);
			$tnode = $page_xml->xpath("//MediaObject/MediaAlias");
			foreach($tnode as $ttnode)
			{
				include_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$media_object = new ilObjMediaObject();
				$media_object->setTitle("");
				$media_object->setDescription("");
				$media_object->create();
		
				// determine and create mob directory, move uploaded file to directory
				$media_object->createDirectory();
				$mob_dir = ilObjMediaObject::_getDirectory($media_object->getId());
				if(!file_exists($this->packageFolder."/objects/".$ttnode[OriginId])) continue;
				$d = ilUtil::getDir($this->packageFolder."/objects/".$ttnode[OriginId]);
				foreach($d as $f)
				{
					if($f[type]=='file') 
					{
						$media_item =& new ilMediaItem();
						$media_object->addMediaItem($media_item);
						$media_item->setPurpose("Standard");
				
						$tmp_name = $this->packageFolder."/objects/".$ttnode[OriginId]."/".$f[entry];
						$name = $f[entry];
						$file = $mob_dir."/".$name;
						copy($tmp_name, $file);
						
						// get mime type
						$format = ilObjMediaObject::getMimeType($file);
						$location = $name;
						// set real meta and object data
						$media_item->setFormat($format);
						$media_item->setLocation($location);
						$media_item->setLocationType("LocalFile");
						$media_object->setTitle($name);
						$media_object->setDescription($format);
				
						if (ilUtil::deducibleSize($format))
						{
							$size = getimagesize($file);
							$media_item->setWidth($size[0]);
							$media_item->setHeight($size[1]);
						}
						//$media_item->setHAlign("Left");
					}
				} 
				
				ilUtil::renameExecutables($mob_dir);
				$media_object->update();
				$ttnode[OriginId]="il__mob_".$media_object->getId();
			}
			$pagex = new ilSCORM2004Page($page->getId());
			$tnode = $page_xml->xpath('PageContent');
			$t = "<PageObject>";
			foreach($tnode as $ttnode)
				$t .= $ttnode->asXML();
			$t .="</PageObject>";
			$pagex->setXMLContent($t);
			$pagex->updateFromXML();
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
				

				$statement = $ilDB->queryF('
				SELECT * FROM cp_package WHERE obj_id = %s AND c_identifier = %s',
				array('integer','text'), array($this->packageId, $this->packageName));
				
				if($res = $ilDB->numRows($statement))
				{ 			
					$statement = $ilDB->manipulateF('
					UPDATE cp_package
					SET persistprevattempts = %s, c_settings = %s
					WHERE obj_id = %s AND c_identifier= %s,',
					array('integer','text','integer', 'text'), 
					array(0, NULL,$this->packageId, $this->packageName));	
				
				}
				else
				{
				
					$statement = $ilDB->manipulateF('
					INSERT INTO cp_package 
					(obj_id, c_identifier, persistprevattempts, c_settings) 
					VALUES (%s,%s,%s,%s)', 
					array('integer','text','integer', 'text'), 
					array($this->packageId, $this->packageName, 0, NULL));					
				}
				
				
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
				

				$cp_node_id = $ilDB->nextId('cp_node');
				
				$statement = $ilDB->manipulateF('
					INSERT INTO cp_node
					(cp_node_id, slm_id, nodename) 
					VALUES (%s, %s, %s)',
					array('integer', 'integer', 'text'), 
					array($cp_node_id,$this->packageId,$node->nodeName)
				);
			
				
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
				

				$statement = $ilDB->manipulateF('
					INSERT INTO cp_tree 
					(child, depth, lft, obj_id, parent, rgt) 
					VALUES (%s,%s,%s,%s,%s,%s)',
					array('integer','integer','integer','integer','integer','integer'),
					array($cp_node_id, $depth, $lft++, $this->packageId, $parent, 0) 
				);
			

				// insert into cp_*
				//$a = array('cp_node_id' => $cp_node_id);
				$names = array('cp_node_id');
				
				$values = array($cp_node_id);
				$types = array('integer');
				$sql_str = array('%s');
		
				foreach ($node->attributes as $attr)
				{
					switch(strtolower($attr->name))
					{
						case 'completionsetbycontent': $names[] = 'completionbycontent';break;
						case 'objectivesetbycontent': $names[] = 'objectivebycontent';break;
						case 'type': $names[] = 'c_type';break;
						case 'mode': $names[] = 'c_mode';break;
						case 'language': $names[] = 'c_language';break;
						case 'condition': $names[] = 'c_condition';break;
						case 'operator': $names[] = 'c_operator';break;
						case 'condition': $names[] = 'c_condition';break;
						case 'readnormalizedmeasure': $names[] = 'readnormalmeasure';break;
						case 'writenormalizedmeasure': $names[] = 'writenormalmeasure';break;
						case 'minnormalizedmeasure': $names[] = 'minnormalmeasure';break;
						case 'primary': $names[] = 'c_primary';break;
						case 'minnormalizedmeasure': $names[] = 'minnormalmeasure';break;
						case 'primary': $names[] = 'c_primary';break;						
						case 'persistpreviousattempts': $names[] = 'persistprevattempts';break;						
						case 'identifier': $names[] = 'c_identifier';break;
						case 'settings': $names[] = 'c_settings';break;
						case 'activityabsolutedurationlimit': $names[] = 'activityabsdurlimit';break;
						case 'activityexperienceddurationlimit': $names[] = 'activityexpdurlimit';break;
						case 'attemptabsolutedurationlimit': $names[] = 'c_primary';break;
						case 'measuresatisfactionifactive': $names[] = 'measuresatisfactive';break;
						case 'objectivemeasureweight': $names[] = 'objectivemeasweight';break;
						case 'requiredforcompleted': $names[] = 'requiredcompleted';break;
						case 'requiredforincomplete': $names[] = 'requiredforincomplete';break;
						case 'requiredfornotsatisfied': $names[] = 'requirednotsatisfied';break;
						case 'rollupobjectivesatisfied': $names[] = 'rollupobjectivesatis';break;
						case 'rollupprogresscompletion': $names[] = 'rollupprogcompletion';break;
						case 'usecurrentattemptobjectiveinfo': $names[] = 'usecurattemptobjinfo';break;
						case 'usecurrentattemptprogressinfo': $names[] = 'usecurattemptproginfo';break;
		
		/*				case 'requiredforincomplete': $names[] = 'requiredforincomplete';break;
						case 'requiredforincomplete': $names[] = 'requiredforincomplete';break;
		*/			
						default:$names[] = strtolower($attr->name);
						break;
					}	
					
				//	$names[] = strtolower($attr->name);
					$values[] = $attr->value;
					
					if( $attr->name == 'objectivesglobtosys')
						$types[] = 'integer';
					else
						$types[] = 'text';
						
					$sql_str[] = '%s'; 	
						
				}
				//ilSCORM13DB::setRecord('cp_' . $node->nodeName, $a);
				$query = 'INSERT INTO cp_'.$node->nodeName.'
				('. implode(',', $names).')
				VALUES ('. implode (', ',$sql_str).')';

				$statement = $ilDB->manipulateF($query,$types,$values);
	
				$node->setAttribute('foreignId', $cp_node_id);
				$this->idmap[$node->getAttribute('id')] = $cp_node_id;

				// run sub nodes
				foreach($node->childNodes as $child)
				{
					$this->dbImport($child, $lft, $depth+1, $cp_node_id); // RECURSION
				}

				// update cp_tree (rgt value for pre order walk in sql tree)

				$statement = $ilDB->manipulateF('
					UPDATE cp_tree SET rgt = %s
					 WHERE child = %s', 
				array('integer', 'integer'), 
				array($lft++, $cp_node_id));
		
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

		$statement = $ilDB->manipulateF('
		INSERT INTO cp_package 
		(obj_id, xmldata, jsdata) 
		VALUES (%s,%s,%s)',
		array('integer','text','text'), 
		array($this->packageId,$x->asXML(),json_encode($j)));
		
		return true;
	}


	public function removeCMIData()
	{
		global $ilDB;

		//cmi nodes
		$cmi_nodes = array();
		$set_cmi = $ilDB->queryF('
			SELECT cmi_node.cmi_node_id 
			FROM cmi_node, cp_node 
			WHERE(	cp_node.slm_id = %s
			AND cmi_node.cp_node_id = cp_node.cp_node_id)',
		array('integer'),array($this->packageId, ));
		
		while ($data = $ilDb->fetcAssoc($set_cmi)) 
		{
		//	array_push($cmi_nodes,$data['cmi_node_id']);
			$cmi_node_values[] = $data['cmi_node_id'];
			$cmi_node_types[] = 'integer';
			$cmi_node_str[] = '%s';
		}
		//$cmi_nodes_impl = implode(",",ilUtil::quoteArray($cmi_nodes));		
		
		//cmi interaction nodes
		$cmi_inodes = array();
		$set_icmi = $ilDB->queryF('
			SELECT cmi_interaction.cmi_interaction_id 
			FROM cmi_interaction, cmi_node, cp_node 
			WHERE(cp_node.slm_id = %s
			AND cmi_node.cp_node_id=cp_node.cp_node_id
			AND cmi_node.cmi_node_id=cmi_interaction.cmi_node_id)',
		array('integer'),
		array($this->packageId));
		
		while ($data = $ilDB->fetchAssoc($set_icmi)) 
		{
			$cmi_inode_values[] = $data['cmi_interaction_id'];
			$cmi_inode_types[] = 'integer';
			$cmi_inode_str[] = '%s';
		}
		
		//response
		$ilDB->manipulateF('
			DELETE FROM cmi_correct_response WHERE cmi_correct_response.cmi_interaction_id IN
			  ('.implode(',', $cmi_inode_str).')',
			array(implode(',', $cmi_inode_types)), 
			array(implode(',', $cmi_inode_values))
		);
			
		//objective interaction
		$ilDB->manipulateF('DELETE FROM cmi_objective WHERE cmi_objective.cmi_interaction_id IN 
			  ('.implode(',', $cmi_inode_str).')',
			array(implode(',', $cmi_inode_types)), 
			array(implode(',', $cmi_inode_values))
		);	
			
		//objective
		$ilDB->manipulateF('DELETE FROM cmi_objective WHERE cmi_objective.cmi_node_id IN 
			  ('.implode(',', $cmi_node_str).')',
			array(implode(',', $cmi_node_types)), 
			array(implode(',', $cmi_node_values))
		);	
				
		//interaction
		$ilDB->manipulateF('DELETE FROM cmi_interaction WHERE cmi_interaction.cmi_node_id IN
			  ('.implode(',', $cmi_node_str).')',
			array(implode(',', $cmi_node_types)), 
			array(implode(',', $cmi_node_values))
		);	
			
		//comment
		$ilDB->manipulateF('DELETE FROM cmi_comment WHERE cmi_comment.cmi_node_id IN
			  ('.implode(',', $cmi_node_str).')',
			array(implode(',', $cmi_node_types)), 
			array(implode(',', $cmi_node_values))
		);	
					
		//node
		$ilDB->manipulateF('DELETE FROM cmi_node WHERE cmi_node.cmi_node_id IN
			  ('.implode(',', $cmi_node_str).')',
			array(implode(',', $cmi_node_types)), 
			array(implode(',', $cmi_node_values))
		);	
		
	}
	
	
	public function removeCPData()
	{
		global $ilDB,$ilLog;
		
		
		//get relevant nodes
	
		$cp_nodes = array();
		$set_cp = $ilDB->queryF('SELECT cp_node.cp_node_id FROM cp_node WHERE cp_node.slm_id = %s',
		array('integer'), array($this->packageId));
		
		while ($data = $ilDB->fetchAssoc($set_cp)) 
		{
			//array_push($cp_nodes,$data['cp_node_id']);
			$cp_nodes_values[] = $data['cp_node_id'];
			$cp_nodes_types[] = 'integer';
			$cp_nodes_str[] = '%s';
		}		
		
		//remove package data
		foreach (self::$elements['cp'] as $t)
		{
			$t = 'cp_' . $t;
			//$ilDB->query("DELETE FROM $t WHERE $t.cp_node_id IN ($cp_nodes_impl);");
			$statement = $ilDB->manipulateF('DELETE FROM '.$t.' WHERE '.$t.'.cp_node_id IN 
			('.implode( ',', $cp_nodes_str).')',
			array(implode( ',', $cp_nodes_types)), 
			array(implode( ',', $cp_nodes_values))
			);
		}
		
		// remove CP structure entries in tree and node
		$ilDB->manipulateF('DELETE FROM cp_tree WHERE cp_tree.obj_id=%s',
		array('integer'), array($this->packageId));

		$ilDB->manipulateF('DELETE FROM cp_node WHERE cp_node.slm_id=%s',
		array('integer'), array($this->packageId));
		
		// remove general package entry
		$ilDB->manipulateF('DELETE FROM cp_package WHERE cp_package.obj_id=%s',
		array('integer'), array($this->packageId));
		
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
