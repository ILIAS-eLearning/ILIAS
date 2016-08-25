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
	function importXmlRepresentation($a_entity, $a_id, $a_xml_filename, $a_mapping)
	{
		$result = false;
		if (file_exists($a_xml_filename))
		{
			$xml = file_get_contents ($a_xml_filename);
			if(isset ($xml))
			{
				$xmlRoot = simplexml_load_string($xml);
				if (isset ($xmlRoot->Modules))
				{
					if (isset ($xmlRoot->Modules["type"]))
					{
						if ($xmlRoot->Modules["type"] == "sahs")
						{
							//todo: extend for import of multiple modules in one file ??
							foreach ($this->dataset->properties as $key => $value)
							{
								$this->moduleProperties[$key] = $xmlRoot->Modules->Module->$key;
							}
							$this->moduleProperties["zipfile"] = $xmlRoot->Modules->Module->zipfile;
							$this->moduleProperties["Title"] = $xmlRoot->Modules->Module->Title;
							$this->moduleProperties["Description"] = $xmlRoot->Modules->Module->Description;
							$result = true;
						}
					}
				}
			}
			else
			{
				 $GLOBALS['ilLog']->write("error parsing xml file for scorm import");
				//error xml parsing
			}
		}
		else
		{
			$GLOBALS['ilLog']->write("error file lost while importing");
			//error non existing file
		}
		return $result;
	}

/*for future use
 *public function getZipFileLocation ($a_xml_filename)
	{
		$positions = ["startPosition" => -1, "endPosition" => 1, "startTagLength" => 0];
		$zipTag = "zipBase64enc";
		$positions [] = strlen 
		$bufferSize = 4000000;

		$xmlFile = fopen ($a_xml_filename);
		$offset = 0
		$buffer0 = fread ($zip, $bufferSize);
		while (!feof ($zip)) {                       
			$buffer1 = fread ($zip, $bufferSize);
			$combinedBuffer = $buffer0 + $buffer1;
			$position = 
	//		$encBuffer = base64_encode ($buffer);
	//		fwrite ($xml, $encBuffer);
			$offset += $bufferSize;
		}
//		fwrite ($xml, "<zipfile encoding=\"base64\">\n");
	}
 */

	public function writeData($a_entity, $version, $a_id)
	{
		$this->dataset->writeData($a_entity, $a_version, $a_id, $this->moduleProperties);
	}
}
?>
