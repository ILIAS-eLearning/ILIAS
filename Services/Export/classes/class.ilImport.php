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
	protected $mappings = array();
	
	/**
	 * Begin new dataset
	 */
	final protected function beginDataset()
	{
		$this->install_id = "";
		$this->install_url = "";
		$this->entities = array();
	}

	/**
	 * Begin new dataset
	 */
	final protected function endDataset()
	{
	}
	
	/**
	 * Set Installation ID
	 *
	 * @param	string	Installation ID
	 */
	final function setInstallId($a_val)
	{
		$this->install_id = $a_val;
	}
	
	/**
	 * Get Installation ID
	 *
	 * @return	string	Installation ID
	 */
	final function getInstallId()
	{
		return $this->install_id;
	}
	
	/**
	 * Set Installation Url
	 *
	 * @param	string	Installation Url
	 */
	final function setInstallUrl($a_val)
	{
		$this->install_url = $a_val;
	}
	
	/**
	 * Get Installation Url
	 *
	 * @return	string	Installation Url
	 */
	final function getInstallUrl()
	{
		return $this->install_url;
	}
	
	/**
	 * Add mapping
	 *
	 * @param	string		entity
	 * @param	string		old id
	 * @param	string		new id
	 */
	function addMapping($a_entity, $a_old_id, $a_new_id)
	{
		$this->mappings[$a_entity][$a_old_id] = $a_new_id;
	}
	
	/**
	 * Get a mapping
	 *
	 * @param	string		entity
	 * @param	string		old id
	 * 
	 * @return	string		new id, or false if no mapping given
	 */
	function getMapping($a_entity, $a_old_id)
	{
		if (isset($this->mappings[$a_entity][$a_old_id]))
		{
			return $this->mappings[$a_entity][$a_old_id];
		}
	
		return false;	
	}
	
	/**
	 * Get mapping
	 *
	 * @return	array	mapping
	 */
	function getAllMappings()
	{
		return $this->mappings;
	}
	
	/**
	 * Get mappings for entity
	 *
	 * @param	string	entity
	 * @return
	 */
	function getMappingsOfEntity($a_entity)
	{
		if (isset($this->mappings[$a_entity]))
		{
			return $this->mappings[$a_entity];
		}
		return array();
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
	 * Set currrent dataset
	 *
	 * @param	object	currrent dataset
	 */
	function setCurrentDataset($a_val)
	{
		$this->current_dataset = $a_val;
	}
	
	/**
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
	 * Import repository object export file
	 *
	 * @param	string		absolute filename of temporary upload file
	 */
	final static function _importObject($a_new_obj, $a_tmp_file, $a_type)
	{
		global $objDefinition, $tpl;
				
		$comp = $objDefinition->getComponentForType($a_type);
		$class = $objDefinition->getClassName($a_type);

		// get import class
		$success = true;
		$import_class_file = "./".$comp."/classes/class.il".$class."Import2.php";
		if (!is_file($import_class_file))
		{
			$success = false;
		}
		if ($success)
		{
			$class = "il".$class."Import2";
			include_once($import_class_file);
			$import = new $class();
		
			// create temporary directory
			$tmpdir = ilUtil::ilTempnam();
			ilUtil::makeDir($tmpdir);
			
			// move import file into temporary directory
$a_filename = basename($a_tmp_file);
copy($a_tmp_file, $tmpdir."/".$a_filename);
//			ilUtil::moveUploadedFile($a_tmp_file, $a_filename, $tmpdir."/".$a_filename);
			
			// unzip file
			ilUtil::unzip($tmpdir."/".$a_filename);
			$dir = $tmpdir."/".substr($a_filename, 0, strlen($a_filename) - 4);
			
			// process manifest file
			include_once("./Services/Export/classes/class.ilManifestParser.php");
			$parser = new ilManifestParser($dir."/manifest.xml");
	
			// process xml files
			$xmlfiles = $parser->getXmlFiles();
			include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
			foreach ($xmlfiles as $xmlfile)
			{
				$import->beginDataset();
				$parser = new ilDataSetImportParser($import, $dir."/".$xmlfile["path"],
					$xmlfile["component"]);
				$import->endDataset();
			} 
			
			// delete temporary directory
			ilUtil::delDir($tmpdir);
			
			return $import;
		}
		
	}
	
}
?>