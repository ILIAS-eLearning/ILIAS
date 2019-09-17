<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	const VALIDATE_XSD  = './libs/ilias/Scorm2004/xsd/op/op-scorm13.xsd';
	
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
		$this->packagesFolder = ''; // #25372
		$this->load($packageId);
		// $this->userId = $GLOBALS['DIC']['USER']['usr_id'];	  	
	}
	
	public function load($packageId)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		
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


	/**
	 * Export as internal XML
	 */
	public function exportXML()
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		
		header('content-type: text/xml');
		header('content-disposition: attachment; filename="manifest.xml"');

		$res = $ilDB->queryF(
			'SELECT xmldata FROM cp_package WHERE obj_id = %s', 
			array('integer'),
			array($this->packageId)
		);
		$row = $ilDB->fetchAssoc($res);
		
		print($row['xmldata']);
	}


	/**
	* Imports an extracted SCORM 2004 module from ilias-data dir into database
	*
	* @access       public
	* @return       string title of package
	*/
	public function il_import($packageFolder,$packageId,$ilias,$validate,$reimport=false){
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$ilLog = $DIC['ilLog'];
		$ilErr = $DIC['ilErr'];
		
		$title = "";

		if ($reimport === true) {
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

		//step 2 tranform
	  	$this->manifest = $this->transform($this->imsmanifest, self::DB_ENCODE_XSL);
  
	  	if (!$this->manifest)
	  	{
	  		$this->diagnostic[] = 'Cannot transform into normalized manifest';
	  		return false;
	  	}
		//setp 2.5 if only a single item, make sure the scormType of it's linked resource is SCO
		$path = new DOMXpath($this->manifest);
		$path->registerNamespace("scorm","http://www.openpalms.net/scorm/scorm13");
		$items = $path->query("//scorm:item");
		if($items->length == 1){
			$n = $items->item(0);
			$resource = $path->query("//scorm:resource");//[&id='"+$n->getAttribute("resourceId")+"']");
			foreach($resource as $res){
				if($res->getAttribute('id') == $n->getAttribute("resourceId")){
					$res->setAttribute('scormType','sco');
				}
			}
		}
		//$this->manifest->save("C:\Users\gratat\after.xml");
	  	//step 3 validation -just for normalized XML
		if ($validate=="y") {
	  		if (!$this->validate($this->manifest, self::VALIDATE_XSD))
	  		{
			
				$ilErr->raiseError("<b>The uploaded SCORM 1.2 / SCORM 2004 is not valid. You can try to import the package without the validation option checked on your own risk. </b><br><br>Validation Error(s):</b><br> Normalized XML is not conform to ". self::VALIDATE_XSD,
				$ilErr->MESSAGE);
			}
		}
		$this->dbImport($this->manifest);

		if(file_exists($this->packageFolder . '/' . 'index.xml'))
		{
			$doc = simplexml_load_file($this->packageFolder . '/' . 'index.xml');
			$l = $doc->xpath ("/ContentObject/MetaData" );
			if($l[0])
			{
				include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
				$mdxml = new ilMDXMLCopier($l[0]->asXML(),$packageId,$packageId,ilObject::_lookupType($packageId));
				$mdxml->startParsing();
				$mdxml->getMDObject()->update();
			}
		}
		else
		{
			include_once("./Modules/Scorm2004/classes/class.ilSCORM13MDImporter.php");
			$importer = new ilSCORM13MDImporter($this->imsmanifest, $packageId);
			$importer->import();
			$title = $importer->getTitle();
			$description = $importer->getDescription();
			if ($description != "") {
				ilObject::_writeDescription($packageId, $description);
			}
		}

		//step 5
	  	$x = simplexml_load_string($this->manifest->saveXML());
	  	$x['persistPreviousAttempts'] = $this->packageData['persistprevattempts'];  	
	  	// $x['online'] = !$this->getOfflineStatus();//$this->packageData['c_online'];
	  	
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
		$ilDB->update('cp_package',
			array(
				'xmldata'			=> array('clob', $x->asXML()),
				'jsdata'			=> array('clob', json_encode($j)),
				'activitytree'		=> array('clob', json_encode($adl_tree['tree'])),
				'global_to_system'	=> array('integer', (int)$adl_tree['global']),
				'shared_data_global_to_system' => array('integer', (int)$adl_tree['dataglobal'])
			),
			array(
				'obj_id'			=> array('integer', (int)$this->packageId)
			)
		);

		// title retrieved by importer
		if ($title != "") {
			return $title;
		}

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
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$ilLog = $DIC['ilLog'];
		
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
	  	
	  	// import sco.xml
	  	$sco_xml_file = $this->packageFolder . '/sco.xml';
	  	if (is_file($sco_xml_file))
	  	{
	  		$scodoc = new DOMDocument;
	  		$scodoc->async = false;
			if (!@$scodoc->load($sco_xml_file))
			{
				$this->diagnostic[] = 'XML of sco.xml not wellformed';
				return false;
			}
			//$doc = new SimpleXMLElement($scodoc->saveXml());
			//$l = $doc->xpath("/sco/objective");
			$xpath = new DOMXPath($scodoc);
			$nodes = $xpath->query("/sco/objective");
			foreach($nodes as $node)
			{
				$t_node = $node->firstChild;
				if (is_object($t_node))
				{
					$objective_text = $t_node->textContent;
					if (trim($objective_text) != "")
					{
						$objs = $sco->getObjectives();
						foreach ($objs as $o)
						{
							$mappings = $o->getMappings();
							if ($mappings == null)
							{
								$ob = new ilScorm2004Objective($sco->getId(), $o->getId());
								$ob->setObjectiveID($objective_text);
								$ob->updateObjective();
							}
						}
					}
				}
			}
		}
		return "";
	}

	  /**
	* Imports an extracted SCORM 2004 module from ilias-data dir into database
	*
	* @access       public
	* @return       string title of package
	*/
	public function il_importAss($packageId, $sco_id, $packageFolder)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$ilLog = $DIC['ilLog'];
		
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
	  	$sco = new ilSCORM2004Asset($slm,$sco_id);
	  	$this->dbImportSco($slm,$sco, true);
	  	
	  	// import sco.xml
/*
	  	$sco_xml_file = $this->packageFolder . '/sco.xml';
	  	if (is_file($sco_xml_file))
	  	{
	  		$scodoc = new DOMDocument;
	  		$scodoc->async = false;
			if (!@$scodoc->load($sco_xml_file))
			{
				$this->diagnostic[] = 'XML of sco.xml not wellformed';
				return false;
			}
			//$doc = new SimpleXMLElement($scodoc->saveXml());
			//$l = $doc->xpath("/sco/objective");
			$xpath = new DOMXPath($scodoc);
			$nodes = $xpath->query("/sco/objective");
			foreach($nodes as $node)
			{
				$t_node = $node->firstChild;
				if (is_object($t_node))
				{
					$objective_text = $t_node->textContent;
					if (trim($objective_text) != "")
					{
						$objs = $sco->getObjectives();
						foreach ($objs as $o)
						{
							$mappings = $o->getMappings();
							if ($mappings == null)
							{
								$ob = new ilScorm2004Objective($sco->getId(), $o->getId());
								$ob->setObjectiveID($objective_text);
								$ob->updateObjective();
							}
						}
					}
				}
			}
		}
*/
		return "";
	}

	public function il_importLM($slm, $packageFolder, $a_import_sequencing = false)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$ilLog = $DIC['ilLog'];

	  	$this->packageFolder=$packageFolder;
	  	$this->packageId=$slm->getId();
	  	$this->imsmanifestFile = $this->packageFolder . '/' . 'imsmanifest.xml';
	  	$this->imsmanifest = new DOMDocument;
	  	$this->imsmanifest->async = false;
	  	$this->imsmanifest->formatOutput = false;
	  	$this->imsmanifest->preserveWhiteSpace = false;
	  	$this->slm = $slm;
	  	if (!@$this->imsmanifest->load($this->imsmanifestFile))
	  	{
	  		$this->diagnostic[] = 'XML not wellformed';
	  		return false;
	  	}
	  	
	  	$this->mani_xpath = new DOMXPath($this->imsmanifest);
	  	$this->mani_xpath->registerNamespace("d", "http://www.imsproject.org/xsd/imscp_rootv1p1p2");
	  	$this->mani_xpath->registerNamespace("imscp", "http://www.imsglobal.org/xsd/imscp_v1p1");
	  	$this->mani_xpath->registerNamespace("imsss", "http://www.imsglobal.org/xsd/imsss");

	  	
		$this->dbImportLM(simplexml_import_dom($this->imsmanifest->documentElement), "",
			$a_import_sequencing);
		
		if(is_dir($packageFolder."/glossary"))
		{
			$this->importGlossary($slm,$packageFolder."/glossary");
		}
	  	//die($slm->title);

		return $slm->title;
	}
	
	function importGlossary($slm, $packageFolder)
	{
		// create and insert object in objecttree
		include_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
		$newObj = new ilObjGlossary();
		$newObj->setType('glo');
		$newObj->setTitle('');
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		
		$xml_file = $packageFolder."/glossary.xml";

		// check whether xml file exists within zip file
		if (!is_file($xml_file))
		{
			return;
		}

		include_once ("./Modules/LearningModule/classes/class.ilContObjParser.php");
		$contParser = new ilContObjParser($newObj, $xml_file, $packageFolder);
		$contParser->startParsing();
		$newObj->update();
		//ilObject::_writeImportId($newObj->getId(), $newObj->getImportId());
		$slm->setAssignedGlossary($newObj->getId());
		$slm->update();
	}
	
	function dbImportLM($node, $parent_id = "", $a_import_sequencing = false)
	{
	
		switch($node->getName())
		{
			case "manifest":
				$this->slm_tree = new ilTree($this->slm->getId());
				$this->slm_tree->setTreeTablePK("slm_id");
				$this->slm_tree->setTableNames('sahs_sc13_tree', 'sahs_sc13_tree_node');
				$this->slm_tree->addTree($this->slm->getId(), 1);
				
				//add seqinfo for rootNode
				include_once ("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Sequencing.php");
				$seq_info = new ilSCORM2004Sequencing($this->slm->getId(),true);
				
				// get original sequencing information
				$r = $this->mani_xpath->query("/d:manifest/d:organizations/d:organization/imsss:sequencing");
				$this->imsmanifest->formatOutput = false;
				if ($r)
				{
					$this->setSequencingInfo($r->item(0), $seq_info, $a_import_sequencing);
					if ($a_import_sequencing)
					{
						$seq_info->initDom();
					}
				}
				$seq_info->insert();
				
				if(file_exists($this->packageFolder . '/' . 'index.xml'))
				{
					$doc = simplexml_load_file($this->packageFolder . '/' . 'index.xml');
					$l = $doc->xpath ( "/ContentObject/MetaData" );
					if($l[0])
					{
						include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
						$mdxml = new ilMDXMLCopier($l[0]->asXML(),$this->slm->getId(),$this->slm->getId(),$this->slm->getType());
						$mdxml->startParsing();
						$mdxml->getMDObject()->update();
					}
				}
				break;
			case "organization":
				$this->slm->title=$node->title;
				break;
			case "item":
				$a = $node->attributes();
				if(preg_match("/il_\d+_chap_\d+/",$a['identifier']))
				{
					$chap= new ilSCORM2004Chapter($this->slm);
					$chap->setTitle($node->title);
					$chap->setSLMId($this->slm->getId());
					$chap->create(true);
			
					// save sequencing information
					$r = $this->mani_xpath->query("//d:item[@identifier='".$a['identifier']."']/imsss:sequencing");
					if ($r)
					{
						$seq_info = new ilSCORM2004Sequencing($chap->getId());
						$this->setSequencingInfo($r->item(0), $seq_info, $a_import_sequencing);
						$seq_info->initDom();
						$seq_info->insert();
					}

					ilSCORM2004Node::putInTree($chap, $parent_id, "");
					$parent_id = $chap->getId();
					$doc = simplexml_load_file($this->packageFolder . '/' . 'index.xml');
			  		$l = $doc->xpath ( "/ContentObject/StructureObject/MetaData[General/Identifier/@Entry='".$a['identifier']."']" );
					if($l[0])
				  	{
				  		include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
				  		$mdxml = new ilMDXMLCopier($l[0]->asXML(),$this->slm->getId(),$chap->getId(),$chap->getType());
						$mdxml->startParsing();
						$mdxml->getMDObject()->update();
				  	}
				}
				if(preg_match("/il_\d+_sco_(\d+)/",$a['identifier'], $match))
				{
					$sco = new ilSCORM2004Sco($this->slm);
					$sco->setTitle($node->title);
					$sco->setSLMId($this->slm->getId());
					$sco->create(true);
					
					// save sequencing information
					$r = $this->mani_xpath->query("//d:item[@identifier='".$a['identifier']."']/imsss:sequencing");
					if ($r)
					{
						$seq_info = new ilSCORM2004Sequencing($sco->getId());
						$this->setSequencingInfo($r->item(0), $seq_info, $a_import_sequencing,
							"local_obj_".$sco->getID()."_0");
						$seq_info->initDom();
						$seq_info->insert();
					}
					
					ilSCORM2004Node::putInTree($sco, $parent_id, "");
					$newPack = new ilSCORM13Package();
					$newPack->il_importSco($this->slm->getId(),$sco->getId(),$this->packageFolder."/".$match[1]);
					$parent_id = $sco->getId();
				}
				if(preg_match("/il_\d+_ass_(\d+)/",$a['identifier'], $match))
				{
					$ass = new ilSCORM2004Asset($this->slm);
					$ass->setTitle($node->title);
					$ass->setSLMId($this->slm->getId());
					$ass->create(true);
					
					// save sequencing information
					$r = $this->mani_xpath->query("//d:item[@identifier='".$a['identifier']."']/imsss:sequencing");
					if ($r)
					{
						$seq_info = new ilSCORM2004Sequencing($ass->getId());
						$this->setSequencingInfo($r->item(0), $seq_info, $a_import_sequencing,
							"local_obj_".$ass->getID()."_0");
						$seq_info->initDom();
						$seq_info->insert();
					}
					
					ilSCORM2004Node::putInTree($ass, $parent_id, "");
					$newPack = new ilSCORM13Package();
					$newPack->il_importAss($this->slm->getId(),$ass->getId(),$this->packageFolder."/".$match[1]);
					$parent_id = $ass->getId();
				}
				
				break;
		}
		//if($node->nodeType==XML_ELEMENT_NODE)
		{
			foreach($node->children() as $child)
			{
				 $this->dbImportLM($child, $parent_id, $a_import_sequencing);
			}
		}
	}

	/**
	 * Save sequencing ingo
	 *
	 * @param
	 * @return
	 */
	function setSequencingInfo($a_node, $a_seq_info, $a_import_sequencing, $a_fix_obj_id = "")
	{
		$seq_xml = trim(str_replace("imsss:", "", $this->imsmanifest->saveXML($a_node)));
		if ($seq_xml != "")
		{
			$a_seq_info->setImportSeqXml('<?xml version="1.0"?>'.$seq_xml);
		}
		if ($a_import_sequencing)
		{
			if ($a_fix_obj_id != "")
			{
				$seq_xml = preg_replace("/local_obj_[0-9]*_0/", $a_fix_obj_id, $seq_xml);
			}
			$a_seq_info->setSeqXml('<?xml version="1.0"?>'.$seq_xml);
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

	public function dbImportSco($slm,$sco, $asset = false)
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
					
		// This creates a lot of invalid repository objects for each question
		// question are not repository objects (see e.g. table object_data), alex 29 Sep 2009
					
//					$newObj->setType ( $qp ['type'] );
//					$newObj->setTitle ( $qp ['title'] );
//					$newObj->create ( true );
//					$newObj->createReference ();
//					$newObj->putInTree ($_GET ["ref_id"]);
//					$newObj->setPermissions ( $sco->getId ());
//					$newObj->notify ("new", $_GET["ref_id"], $sco->getId (), $_GET["ref_id"], $newObj->getRefId () );
//					$newObj->mark_schema->flush ();
					$qtiParser = new ilQTIParser ( $this->packageFolder . "/" . $f [entry], IL_MO_PARSE_QTI, 0, "" );
					$qtiParser->setTestObject ( $newObj );
					$result = $qtiParser->startParsing ();
//					$newObj->saveToDb ();
					$qtis = array_merge($qtis, $qtiParser->getImportMapping());

				}
			}
		}
//exit;
		include_once 'Modules/Scorm2004/classes/class.ilSCORM2004Page.php';
		$doc = new SimpleXMLElement($this->imsmanifest->saveXml());
		$l = $doc->xpath ( "/ContentObject/MetaData" );
		if($l[0])
	  	{
	  		include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
	  		$mdxml = new ilMDXMLCopier($l[0]->asXML(),$slm->getId(),$sco->getId(),$sco->getType());
			$mdxml->startParsing();
			$mdxml->getMDObject()->update();
	  	}
		$l = $doc->xpath("/ContentObject/PageObject");
		foreach ( $l as $page_xml ) 
		{
			$tnode = $page_xml->xpath ( 'MetaData/General/Title' );
			$page = new ilSCORM2004PageNode ( $slm );
			$page->setTitle ( $tnode [0] );
			$page->setSLMId ( $slm->getId () );
			$page->create (true);
//			ilSCORM2004Node::putInTree ( $page, $sco->getId (), $target );
            ilSCORM2004Node::putInTree ( $page, $sco->getId (), "" );
			$pmd = $page_xml->xpath ("MetaData");
			if($pmd[0])
		  	{
		  		include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
		  		$mdxml = new ilMDXMLCopier($pmd[0]->asXML(),$slm->getId(),$page->getId(),$page->getType());
				$mdxml->startParsing();
				$mdxml->getMDObject()->update();
		  	}
			$tnode = $page_xml->xpath("//MediaObject/MediaAlias | //InteractiveImage/MediaAlias");
			foreach($tnode as $ttnode)
			{
				include_once './Services/MediaObjects/classes/class.ilObjMediaObject.php';
				$OriginId = $ttnode[OriginId];
				$medianodes = $doc->xpath("//MediaObject[MetaData/General/Identifier/@Entry='".$OriginId ."']");
				$medianode = $medianodes[0];
				if($medianode)
				{
					$media_object = new ilObjMediaObject ( );
					$media_object->setTitle ($medianode->MetaData->General->Title);
					$media_object->setDescription ($medianode->MetaData->General->Description);
					$media_object->create (false);
					$mmd = $medianode->xpath ("MetaData");
					if($mmd[0])
				  	{
				  		include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';
				  		$mdxml = new ilMDXMLCopier($mmd[0]->asXML(),0,$media_object->getId(),$media_object->getType());
						$mdxml->startParsing();
						$mdxml->getMDObject()->update();
				  	}
					// determine and create mob directory, move uploaded file to directory
					$media_object->createDirectory ();
					$mob_dir = ilObjMediaObject::_getDirectory ( $media_object->getId () );
					foreach ( $medianode->MediaItem as $xMediaItem ) 
					{	
						$media_item = new ilMediaItem ( );
						$media_object->addMediaItem ( $media_item );
						$media_item->setPurpose($xMediaItem[Purpose]);
						$media_item->setFormat($xMediaItem->Format );
						$media_item->setLocation($xMediaItem->Location);
						$media_item->setLocationType($xMediaItem->Location[Type]);
						$media_item->setWidth ( $xMediaItem->Layout[Width]);
						$media_item->setHeight ( $xMediaItem->Layout[Height]);
						$media_item->setHAlign($xMediaItem->Layout[HorizontalAlign]);
						$media_item->setCaption($xMediaItem->Caption);
						$media_item->setTextRepresentation($xMediaItem->TextRepresentation);
						$nr = 0;
						
						// add map areas (external links only)
						foreach ($xMediaItem->MapArea as $n => $v)
						{
							
							if ($v->ExtLink[Href] != "")
							{
								include_once("./Services/MediaObjects/classes/class.ilMapArea.php");
								$ma = new ilMapArea();
								
								$map_area = new ilMapArea();
								$map_area->setShape($v[Shape]);
								$map_area->setCoords($v[Coords]);
								$map_area->setLinkType(IL_EXT_LINK);
								$map_area->setTitle($v->ExtLink);
								$map_area->setHref($v->ExtLink[Href]);
								
								$media_item->addMapArea($map_area);
							}
						}
						
						if($media_item->getLocationType()=="LocalFile")
						{
//							$tmp_name = $this->packageFolder."/objects/".$OriginId."/".$xMediaItem->Location;
//							copy($tmp_name,  $mob_dir."/".$xMediaItem->Location);
						}
					}
					
					// copy whole directory
					ilUtil::rCopy($this->packageFolder."/objects/".$OriginId, $mob_dir);

					
					// alex: fixed media import: these lines have been
					// behind the next curly bracket which makes it fail
					// when no medianode is given. (id=0 -> fatal error)
					ilUtil::renameExecutables ( $mob_dir );
					$media_object->update(true);
					$ttnode [OriginId] = "il__mob_" . $media_object->getId ();
				}
			}
			include_once("./Modules/File/classes/class.ilObjFile.php");
			include_once("./Services/Utilities/classes/class.ilFileUtils.php");
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			
			$intlinks = $page_xml->xpath("//IntLink");
			//die($intlinks);
			//if($intlinks )
			{
				foreach ( $intlinks as $intlink ) 
				{	
					if($intlink[Type]!="File") continue;
					$path = $this->packageFolder."/objects/".str_replace('dfile','file',$intlink[Target]);
					if(!is_dir($path )) continue;
					$ffiles = array();
					ilFileUtils::recursive_dirscan($path,$ffiles);
					$filename = $ffiles[file][0]; 
					$fileObj = new ilObjFile();
					$fileObj->setType("file");
					$fileObj->setTitle(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
					$fileObj->setFileName(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
				
					// better use this, mime_content_type is deprecated
					$fileObj->setFileType(ilObjMediaObject::getMimeType($path. "/" . $filename));
					
					$fileObj->setFileSize(filesize($path. "/" . $filename));
					$fileObj->create();
					$fileObj->createReference();
					//$fileObj->putInTree($_GET["ref_id"]);
					//$fileObj->setPermissions($slm->getId ());
					$fileObj->createDirectory();
					$fileObj->storeUnzipedFile($path."/" .$filename,ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
					$intlink[Target]="il__dfile_".$fileObj->getId();
					
				}
			}
			$fileitems = $page_xml->xpath("//FileItem/Identifier");
			//if($intlinks )
			{
				foreach ( $fileitems as $fileitem ) 
				{	
					$path = $this->packageFolder."/objects/".$fileitem[Entry];
					if(!is_dir($path )) continue;
					$ffiles = array();
					ilFileUtils::recursive_dirscan($path,$ffiles);
					$filename = $ffiles[file][0]; 
					$fileObj = new ilObjFile();
					$fileObj->setType("file");
					$fileObj->setTitle(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
					$fileObj->setFileName(ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
				
					// better use this, mime_content_type is deprecated
					$fileObj->setFileType(ilObjMediaObject::getMimeType($path. "/" . $filename));
					
					$fileObj->setFileSize(filesize($path. "/" . $filename));
					$fileObj->create();
					$fileObj->createReference();
					//$fileObj->putInTree($_GET["ref_id"]);
					//$fileObj->setPermissions($slm->getId ());
					$fileObj->createDirectory();
					$fileObj->storeUnzipedFile($path."/" .$filename,ilFileUtils::utf8_encode(ilUtil::stripSlashes($filename)));
					$fileitem[Entry]="il__file_".$fileObj->getId();
					
				}
			}
			$pagex = new ilSCORM2004Page($page->getId());
			
			$ddoc = new DOMDocument();
			$ddoc->async = false;
			$ddoc->preserveWhiteSpace = false;
			$ddoc->formatOutput = false;
	  		$ddoc->loadXML($page_xml->asXML());
	  		$xpath  = new DOMXPath($ddoc);
			$tnode = $xpath->query('PageContent');
			$t = "<PageObject>";
			foreach($tnode as $ttnode)
			{
				$t .= str_replace("&amp;", "&", $ddoc->saveXML($ttnode));
			}
			$t .="</PageObject>";
			foreach ($qtis as $old=>$q)
				$t = str_replace($old,'il__qst_'.$q['pool'], $t);
			$pagex->setXMLContent($t);
			$pagex->updateFromXML();
	  	}
	}
	
	public function dbImport($node, &$lft=1, $depth=1, $parent=0)
	{
		global $DIC;
		$ilDB = $DIC['ilDB'];
		
		switch ($node->nodeType)
		{
			case XML_DOCUMENT_NODE:

				// insert into cp_package

				$res = $ilDB->queryF(
					'SELECT * FROM cp_package WHERE obj_id = %s AND c_identifier = %s',
					array('integer', 'text'),
					array($this->packageId, $this->packageName)
				);				
				if($num_rows = $ilDB->numRows($res))
				{
					$query = 'UPDATE cp_package '
						   . 'SET persistprevattempts = %s, c_settings = %s '
						   . 'WHERE obj_id = %s AND c_identifier= %s';							
					$ilDB->manipulateF(
						$query,
						array('integer', 'text', 'integer', 'text'), 
						array(0, NULL, $this->packageId, $this->packageName)
					);				
				}
				else
				{				
					$query = 'INSERT INTO cp_package (obj_id, c_identifier, persistprevattempts, c_settings) '
						   . 'VALUES (%s, %s, %s, %s)';
					$ilDB->manipulateF(
						$query,
						array('integer','text','integer', 'text'), 
						array($this->packageId, $this->packageName, 0, NULL)
					);					
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

				$cp_node_id = $ilDB->nextId('cp_node');
				
				$query = 'INSERT INTO cp_node (cp_node_id, slm_id, nodename) ' 
					   . 'VALUES (%s, %s, %s)';
				$ilDB->manipulateF(
					$query,
					array('integer', 'integer', 'text'), 
					array($cp_node_id, $this->packageId, $node->nodeName)
				);			
				
				$query = 'INSERT INTO cp_tree (child, depth, lft, obj_id, parent, rgt) ' 
					   . 'VALUES (%s, %s, %s, %s, %s, %s)';
				$ilDB->manipulateF(
					$query,
					array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
					array($cp_node_id, $depth, $lft++, $this->packageId, $parent, 0) 
				);			

				// insert into cp_*
				//$a = array('cp_node_id' => $cp_node_id);
				$names = array('cp_node_id');				
				$values = array($cp_node_id);
				$types = array('integer');
		
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
						case 'persistpreviousattempts': $names[] = 'persistprevattempts';break;						
						case 'identifier': $names[] = 'c_identifier';break;
						case 'settings': $names[] = 'c_settings';break;
						case 'activityabsolutedurationlimit': $names[] = 'activityabsdurlimit';break;
						case 'activityexperienceddurationlimit': $names[] = 'activityexpdurlimit';break;
						case 'attemptabsolutedurationlimit': $names[] = 'attemptabsdurlimit';break;
						case 'measuresatisfactionifactive': $names[] = 'measuresatisfactive';break;
						case 'objectivemeasureweight': $names[] = 'objectivemeasweight';break;
						case 'requiredforcompleted': $names[] = 'requiredcompleted';break;
						case 'requiredforincomplete': $names[] = 'requiredincomplete';break;
						case 'requiredfornotsatisfied': $names[] = 'requirednotsatisfied';break;
						case 'rollupobjectivesatisfied': $names[] = 'rollupobjectivesatis';break;
						case 'rollupprogresscompletion': $names[] = 'rollupprogcompletion';break;
						case 'usecurrentattemptobjectiveinfo': $names[] = 'usecurattemptobjinfo';break;
						case 'usecurrentattemptprogressinfo': $names[] = 'usecurattemptproginfo';break;
						default: $names[] = strtolower($attr->name);break;
					}
					
					if(in_array($names[count($names) - 1],
							    array('flow', 'completionbycontent',
								      'objectivebycontent', 'rollupobjectivesatis',
									  'tracked', 'choice',
									  'choiceexit', 'satisfiedbymeasure',
									  'c_primary', 'constrainchoice',
									  'forwardonly', 'global_to_system',
									  'writenormalmeasure', 'writesatisfiedstatus',
									  'readnormalmeasure', 'readsatisfiedstatus',
									  'preventactivation', 'measuresatisfactive',
									  'reorderchildren', 'usecurattemptproginfo',
									  'usecurattemptobjinfo', 'rollupprogcompletion',
									  'read_shared_data', 'write_shared_data',
									  'shared_data_global_to_system', 'completedbymeasure')))
					{
						if($attr->value == 'true')
							$values[] = 1;
						else if ($attr->value == 'false')
							$values[] = 0;
						else
							$values[] = (int)$attr->value;
					}
					else
					{
						$values[] = $attr->value;	
					}
										
					if( in_array($names[count($names) - 1], 
								 array('objectivesglobtosys', 'attemptlimit', 
								       'flow', 'completionbycontent', 
									   'objectivebycontent', 'rollupobjectivesatis',
									   'tracked', 'choice',
									   'choiceexit', 'satisfiedbymeasure',
									   'c_primary', 'constrainchoice',
									   'forwardonly', 'global_to_system',
									   'writenormalmeasure', 'writesatisfiedstatus',
									   'readnormalmeasure', 'readsatisfiedstatus',
									   'preventactivation', 'measuresatisfactive',
									   'reorderchildren', 'usecurattemptproginfo',
									   'usecurattemptobjinfo', 'rollupprogcompletion',
									   'read_shared_data', 'write_shared_data',
									   'shared_data_global_to_system')))
						$types[] = 'integer';
					else if ( in_array($names[count($names) - 1],
									   array('jsdata', 'xmldata', 'activitytree', 'data')))
						$types[] = 'clob';
					else if ( in_array($names[count($names) - 1],
									   array('objectivemeasweight')))
						$types[] = 'float';
					else
						$types[] = 'text';			
				}
				
				if($node->nodeName==='datamap')
                {
                    $names[] = 'slm_id';
					$values[] = $this->packageId;
					$types[] = 'integer';
					
					$names[] = 'sco_node_id';
					$values[] = $parent;
					$types[] = 'integer';
				}
				
				// we have to change the insert method because of clob fields ($ilDB->manipulate does not work here)
				$insert_data = array();
				foreach($names as $key => $db_field)
				{
					$insert_data[$db_field] = array($types[$key], trim($values[$key]));
				}
				$ilDB->insert('cp_'.strtolower($node->nodeName), $insert_data);			
	
				$node->setAttribute('foreignId', $cp_node_id);
				$this->idmap[$node->getAttribute('id')] = $cp_node_id;

				// run sub nodes
				foreach($node->childNodes as $child)
				{
					$this->dbImport($child, $lft, $depth + 1, $cp_node_id); // RECURSION
				}

				// update cp_tree (rgt value for pre order walk in sql tree)
				$query = 'UPDATE cp_tree SET rgt = %s WHERE child = %s';
				$ilDB->manipulateF(
					$query, 
					array('integer', 'integer'), 
					array($lft++, $cp_node_id)
				);
		
				break;
		}
	}

	/**
	 * add new sahs and package record
     * NOT USED
	 */
//	public function dbAddNew()
//	{
//		global $DIC;
//		$ilDB = $DIC['ilDB'];
//
//		$ilDB->insert('cp_package', array(
//			'obj_id'		=> array('integer', $this->packageId),
//			'xmldata'		=> array('clob', $x->asXML()),
//			'jsdata'		=> array('clob', json_encode($j))
//		));
//
//		return true;
//	}


	public function removeCMIData()
	{
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004DeleteData.php");
		ilSCORM2004DeleteData::removeCMIDataForPackage($this->packageId);

		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_refreshStatus($this->packageId);
	}
	
	public function removeCPData()
	{
		global $DIC;		
		$ilDB = $DIC['ilDB'];
		$ilLog = $DIC['ilLog'];
		
		//get relevant nodes	
		$cp_nodes = array();
		
		$res = $ilDB->queryF(
			'SELECT cp_node.cp_node_id FROM cp_node WHERE cp_node.slm_id = %s',
			array('integer'),
			array($this->packageId)
		);		
		while($data = $ilDB->fetchAssoc($res)) 
		{
			$cp_nodes[] = $data['cp_node_id'];
		}		
		
		//remove package data
		foreach(self::$elements['cp'] as $t)
		{
			$t = 'cp_' . $t;
			
			$in = $ilDB->in(strtolower($t).'.cp_node_id', $cp_nodes, false, 'integer');			
			$ilDB->manipulate('DELETE FROM '.strtolower($t).' WHERE '.$in);
		}
		
		// remove CP structure entries in tree and node
		$ilDB->manipulateF(
			'DELETE FROM cp_tree WHERE cp_tree.obj_id = %s',
			array('integer'),
			array($this->packageId)
		);

		$ilDB->manipulateF(
			'DELETE FROM cp_node WHERE cp_node.slm_id = %s',
			array('integer'),
			array($this->packageId)
		);
		
		// remove general package entry
		$ilDB->manipulateF(
			'DELETE FROM cp_package WHERE cp_package.obj_id = %s',
			array('integer'),
			array($this->packageId)
		);		
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
		$prc->registerPHPFunctions();
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
					$this->diagnostic[] = "XSLT $level (Line $error->line) $message";
				}
			}
			libxml_clear_errors();
		}
		libxml_use_internal_errors(false);
		return $return;
	}
	
	//to be called from IlObjUser
	public static function _removeTrackingDataForUser($user_id) {

		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004DeleteData.php");
		ilSCORM2004DeleteData::removeCMIDataForUser($user_id);
		//missing updatestatus
	}
}
?>