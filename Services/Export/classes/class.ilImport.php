<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilImport
{
	protected $install_id = "";
	protected $install_url = "";
	protected $entities = "";

	protected $mapping = null;
	protected $skip_entity = array();

	/**
	 * Constructor
	 *
	 * @param int id of parent container
	 * @return
	 */
	function __construct($a_target_id = 0)
	{
		include_once("./Services/Export/classes/class.ilImportMapping.php");
		$this->mapping = new ilImportMapping();
		$this->mapping->setTagetId($a_target_id);
	}
	
	/**
	 * Get mapping object
	 * @return ilImportMapping ilImportMapping 
	 */
	public function getMapping()
	{
		return $this->mapping;
	}

	/**
	 * Set entity types
	 *
	 * @param	array	entity types
	 */
	final function setEntityTypes($a_val)
	{
		$this->entity_types = $a_val;
	}
	
	/**
	 * Get entity types
	 *
	 * @return	array	entity types
	 */
	final function getEntityTypes()
	{
		return $this->entity_types;
	}
	
	/**
	 * Add skip entity
	 *	
	 * @param string $a_val component
	 * @param string $a_val entity
	 */
	function addSkipEntity($a_component, $a_entity, $skip = true)
	{
		$this->skip_entity[$a_component][$a_entity] = $skip;
	}
	
	/**
	 * Set currrent dataset
	 *
	 * @param	object	currrent dataset
	 */
	function setCurrentDataset($a_val)
	{
		$this->current_dataset = $a_val;
	}
	
	/**
	 * 
	 * Get currrent dataset
	 *
	 * @return	object	currrent dataset
	 */
	function getCurrentDataset()
	{
		return $this->current_dataset;
	}
	
	/**
	 * After entity types are parsed
	 */
	function afterEntityTypes()
	{
		$this->getCurrentDataset()->setImport($this);
	}

	/**
	 * After entity types are parsed
	 *
	 * @param
	 */
	function importRecord($a_entity, $a_types, $a_record)
	{
		$this->getCurrentDataset()->importRecord($a_entity, $a_types, $a_record);
	}
	
	
	/**
	 * Import entity
	 */
	final public function importEntity($a_tmp_file, $a_filename,
		$a_entity, $a_component, $a_copy_file = false)
	{
		$this->importObject(null, $a_tmp_file, $a_filename, $a_entity, $a_component, $a_copy_file);
	}
	
	
	/**
	 * Import repository object export file
	 *
	 * @param	string		absolute filename of temporary upload file
	 */
	final public function importObject($a_new_obj, $a_tmp_file, $a_filename, $a_type,
		$a_comp = "", $a_copy_file = false)
	{
		// create temporary directory
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
		if ($a_copy_file)
		{
			copy($a_tmp_file, $tmpdir."/".$a_filename);
		}
		else
		{
			ilUtil::moveUploadedFile($a_tmp_file, $a_filename, $tmpdir."/".$a_filename);
		}
		ilUtil::unzip($tmpdir."/".$a_filename);
		$dir = $tmpdir."/".substr($a_filename, 0, strlen($a_filename) - 4);
		
		$GLOBALS['ilLog']->write(__METHOD__.': do import with dir '.$dir);
		$new_id = $this->doImportObject($dir, $a_type, $a_comp, $tmpdir);
		
		// delete temporary directory
		ilUtil::delDir($tmpdir);
		
		return $new_id;
	}
	
	
	/**
	 * Import repository object export file
	 *
	 * @param	string		absolute filename of temporary upload file
	 */
	protected function doImportObject($dir, $a_type, $a_component = "", $a_tmpdir = "")
	{
		global $objDefinition, $tpl;

		if ($a_component == "")
		{
			$comp = $objDefinition->getComponentForType($a_type);
			$class = $objDefinition->getClassName($a_type);
		}
		else
		{
			$comp = $a_component;
			$c = explode("/", $comp);
			$class = $c[count($c) - 1];
		}

		$this->comp = $comp;

		// get import class
		$success = true;
		
		// process manifest file
		include_once("./Services/Export/classes/class.ilManifestParser.php");
		if (!is_file($dir."/manifest.xml"))
		{
			include_once("./Services/Export/exceptions/class.ilManifestFileNotFoundImportException.php");
			$e = new ilManifestFileNotFoundImportException('Manifest file not found: "'.$dir."/manifest.xml".'".');
			$e->setManifestDir($dir);
			$e->setTmpDir($a_tmpdir);
			throw $e;
		}
		$parser = new ilManifestParser($dir."/manifest.xml");
		$this->mapping->setInstallUrl($parser->getInstallUrl());
		$this->mapping->setInstallId($parser->getInstallId());

		// process export files
		$expfiles = $parser->getExportFiles();
		
		include_once("./Services/Export/classes/class.ilExportFileParser.php");
		$all_importers = array();
		foreach ($expfiles as $expfile)
		{
			$comp = $expfile["component"];
			$comp_arr = explode("/", $comp);
			$import_class_file = "./".$comp."/classes/class.il".$comp_arr[1]."Importer.php";
			$class = "il".$comp_arr[1]."Importer";
			include_once($import_class_file);
			$this->importer = new $class();
			$all_importers[] = $this->importer;
			$this->importer->setImportDirectory($dir);
			$this->importer->init();
			$this->current_comp = $comp;
			
			try {
				$parser = new ilExportFileParser($dir."/".$expfile["path"],$this, "processItemXml");
			}
			catch(Exception $e)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Import failed with message: '.$e->getMessage());
				#$GLOBALS['ilLog']->write(__METHOD__.': '.file_get_contents($dir.'/'.$expfile['path']));
				throw $e;
			}
		}

		// final processing
		foreach ($all_importers as $imp)
		{
			$imp->finalProcessing($this->mapping);
		}

		// we should only get on mapping here
		$top_mapping = $this->mapping->getMappingsOfEntity($this->comp, $a_type);
		$new_id = (int) current($top_mapping);

		return $new_id;
	}

	/**
	 * Process item xml
	 *
	 * @global ilObjectDefinition $objDefinition
	 */
	function processItemXml($a_entity, $a_schema_version, $a_id, $a_xml,$a_install_id, $a_install_url)
	{
		global $objDefinition;
		
		// skip
		if ($this->skip_entity[$this->current_comp][$a_entity])
		{
			return;
		}

		if($objDefinition->isRBACObject($a_entity) &&
			$this->getMapping()->getMapping('Services/Container', 'imported', $a_id))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Ignoring referenced '.$a_entity.' with id '.$a_id);
			return;
		}
		$this->importer->setInstallId($a_install_id);
		$this->importer->setInstallUrl($a_install_url);
		$this->importer->setSchemaVersion($a_schema_version);
		$this->importer->setSkipEntities($this->skip_entity);
		$new_id = $this->importer->importXmlRepresentation($a_entity, $a_id, $a_xml, $this->mapping);

		// Store information about imported obj_ids in mapping to avoid double imports of references
		if($objDefinition->isRBACObject($a_entity))
		{
			$this->getMapping()->addMapping('Services/Container', 'imported', $a_id, 1);
		}

		// @TODO new id is not always set
		if($new_id)
		{
			$this->mapping->addMapping($this->comp ,$a_entity, $a_id, $new_id);
		}
	}
	
}
?>