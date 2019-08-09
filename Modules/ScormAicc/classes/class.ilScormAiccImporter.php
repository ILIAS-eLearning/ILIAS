<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Export/classes/class.ilXmlImporter.php");
class ilScormAiccImporter extends ilXmlImporter
{
	public function __construct()
	{
		require_once "./Modules/ScormAicc/classes/class.ilScormAiccDataSet.php";
		$this->dataset = new ilScormAiccDataSet ();
		//todo: at the moment restricted to one module in xml file, extend?
		$this->moduleProperties = [];
		$this->manifest = [];
	}

	public function init()
	{
	}

	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_import_dirname, $a_mapping)
	{
		global $DIC;
		$ilLog = $DIC['ilLog'];

		if ($this->handleEditableLmXml($a_entity, $a_id, $a_import_dirname, $a_mapping))
		{
			return true;
		}

		$result = false;
		if (file_exists($a_import_dirname))
		{
			$manifestFile = $a_import_dirname . "/manifest.xml";
			if (file_exists($manifestFile))
			{
				$manifest = file_get_contents ($manifestFile);
				$manifestRoot = simplexml_load_string($manifest);
				$this->manifest["scormFile"] = $manifestRoot->scormFile;
				$this->manifest["properties"] = $manifestRoot->properties;
				if(isset ($manifest))
				{
					$propertiesFile = $a_import_dirname . "/" . $this->manifest["properties"][0];
					$xml = file_get_contents ($propertiesFile);
					if(isset ($xml))
					{
						$xmlRoot = simplexml_load_string($xml);
						//todo: extend for import of multiple modules in one file ??
						foreach ($this->dataset->properties as $key => $value)
						{
							$this->moduleProperties[$key] = $xmlRoot->$key;
						}
						$this->moduleProperties["Title"] = $xmlRoot->Title;
						$this->moduleProperties["Description"] = $xmlRoot->Description;
						$result = true;
					}
					else
					{
						$ilLog->write("error parsing xml file for scorm import");
						//error xml parsing
					}
				}
				else
				{
					$ilLog->write("error reading manifest file");
				}
			}
			else
			{
				$ilLog->write("error no manifest file found");
			}
		}
		else
		{
			$ilLog->write("error file lost while importing");
			//error non existing file
		}
		return $result;
	}

	public function writeData($a_entity, $a_version, $a_id)
	{
		$this->dataset->writeData($a_entity, $a_version, $a_id, $this->moduleProperties);
	}

	/**
	 * Handle editable (authoring) scorm lms
	 *
	 * @param string $a_entity entity
	 * @param string $a_id id
	 * @param string $a_xml xml
	 * @param ilImportMapping $a_mapping import mapping object
	 * @return bool success
	 */
	function handleEditableLmXml($a_entity, $a_id, $a_xml, $a_mapping)
	{
		// if editable...
		if (is_int(strpos($a_xml, "<Editable>1</Editable>")))
		{
			// ...use ilScorm2004DataSet for import
			include_once("./Modules/Scorm2004/classes/class.ilScorm2004DataSet.php");
			$dataset = new ilScorm2004DataSet();
			$dataset->setDSPrefix("ds");
			$dataset->setImportDirectory($this->getImportDirectory());

			include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
			$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(),
				$a_xml, $dataset, $a_mapping);
			return true;
		}
		return false;
	}


}
?>
