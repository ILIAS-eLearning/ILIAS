<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of Dictionary
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDDictionary implements Dictionary {
	const WBD_NAME = "toWBDName";
	const INTERNAL_NAME = "toInternalName";

	const SERACH_IN_GENDER = "gender";
	const SERACH_IN_COURSE_TYPE = "course_type";
	const SEARCH_IN_STUDY_CONTENT = "study_content";
	const SEARCH_IN_WBD_TYPE = "wbd_type";
	const SERACH_IN_AGENT_STATUS = "agent_status";
	const SERACH_IN_ADDRESS_TYPE = "address_type";
	const SEARCH_IN_CERTIFICATION_PERIOD = "certification_period";

	static $mappings = array("toWBDName" 		=> array("gender" => array("m" => "001"
																	,"f" => "002"
																	,"w" => "002"
															)
														,"course_type" => array("Präsenzveranstaltung" => "001"
																	,"Präsenztraining" => "001"
																	,"Präsenz" => "001"
																	,"Selbstlernkurs" => "005"
																	,"gesteuertes E-Learning" => "004"
																	,"Webinar" => "004"
																	,"gesteuertes E-learning" => "004"
																	,"gesteuertes e-learning" => "004"
																	,"Virtuelles Training" => "004"
																	,"Virtuelle Sitzung" => "004"
																	,"Onlinetraining" => "005"
																	,"Blended Learning" => "003"
																	,"XX" => "002"
															)
														,"study_content" => array('Privat-Vorsorge-Lebens-/Rentenversicherung' => '001'
																	,'Privat-Vorsorge-Kranken-/Pflegeversicherung' => '002'
																	,'Firmenkunden-Sach-/Schadensversicherung' => '005'
																	,'Firmenkunden-Sach-/Schadenversicherung' => '005'
																	,'Spartenübergreifend' => '006'
																	,'Sparten-übergreifend' => '006'
																	,'Firmenkunden-Vorsorge (bAV/Personenversicherung)' => '004'
																	,'Beratungskompetenz' => '007'
																	,'Privat-Sach-/Schadenversicherung' => '003'
															)
														,"wbd_type" => array("3 - TP-Service" => "ja"
																	,"2 - TP-Basis" => "nein"
															)
														,"agent_status" => array("0 - aus Stellung" => "006"
																	,"0 - aus Rolle" => "006"
																	,"1 - Angestellter Außendienst" => "002"
																	,"2 - Ausschließlichkeitsvermittler" => "001"
																	,"3 - Makler" => "003"
																	,"4 - Mehrfachagent" => "004"
																	,"5 - Mitarbeiter eines Vermittlers" => "005"
																	,"6 - Sonstiges" => "006"
																	,"7 - keine Zuordnung"  => "006"
																	  
																	,"aus Stellung" => "006"
																	,"aus Rolle" => "006"
																	,"Angestellter Außendienst" => "002"
																	,"Ausschließlichkeitsvermittler" => "001"
																	,"Makler" => "003"
																	,"Mehrfachagent" => "004"
																	,"Mitarbeiter eines Vermittlers" => "005"
																	,"Sonstiges" => "006"
																	,"keine Zuordnung"  => "006"
															)
														,"address_type" => array("privat" => "001"
																	,"geschäftlich" => "002"
																	,"sonstiges" => "010"
															)
														,"certification_period" => array(
																	"Selektiert nicht stornierte Weiterbildungsmaßnahmen aus der aktuelle Zertifizierungsperiode." => "001"
																	,"Selektiert alle nicht stornierte Weiterbildungsmaßnahmen." => "002"
																	,"Selektiert alle Weiterbildungsmaßnahmen." => "003"
															)
								),
							  "toInternalName" 	=> array("gender" => array("001" => "m"
																	,"002" => "f"
															)
														,"course_type" => array("001" => "Präsenz-Veranstaltung"
																	,"002" => "Einzeltraining"
																	,"003" => "Blended Learning"
																	,"004" => "gesteuertes E-Learning"
																	,"005" => "selbstgesteuertes E-Learning"
															)
														,"study_content" => array("001" => "Privat-Vorsorge-Lebens-/Rentenversicherung"
																	,"002" => "Privat-Vorsorge-Kranken-/Pflegeversicherung"
																	,"003" => "Privat-Sach-/Schadenversicherung"
																	,"004" => "Firmenkunden-Vorsorge (bAV/Personenversicherung)"
																	,"005" => "Firmenkunden-Sach-/Schadenversicherung"
																	,"006" => "Sparten-übergreifend"
																	,"007" => "Beratungskompetenz"
															)
														,"wbd_type" => array("ja" => "3 - TP-Service"
																	,"nein" => "2 - TP-Basis"
															)
														,"agent_status" => array("001" => "Ausschließlichkeitsvermittler"
																	,"002" => "Angestellter Außendienst"
																	,"003" => "Makler"
																	,"004" => "Mehrfachagent"
																	,"005" => "Mitarbeiter eines Vermittlers"
																	,"006" => "Sonstiges"
															)
														,"address_type" => array("001" => "privat"
																	,"002" => "geschäftlich"
																	,"010" => "sonstige"
															)
														,"certification_period" => array(
																	"001" => "Selektiert nicht stornierte Weiterbildungsmaßnahmen aus der aktuelle Zertifizierungsperiode."
																	,"002" => "Selektiert alle nicht stornierte Weiterbildungsmaßnahmen."
																	,"003" => "Selektiert alle Weiterbildungsmaßnahmen."
															)
								)
							);
	
	public function getWBDName($key, $section) {
		$name = $this->getName($key, $section, self::WBD_NAME); 
		
		if($name == "") {
			throw new LogicException("wbd value not found for: ".$key." db coloumn: ".$section);
		}

		return $name;
	}

	function getInternalName($key, $section) {
		$name = $this->getName($key, $section, self::INTERNAL_NAME); 
		
		if($name == "") {
			throw new LogicException("internal value not found for: ".$key." db coloumn: ".$section);
		}

		return $name;
	}

	/**
	* Gets the mapped name for $key
	*
	* @param $key 			string
	* @param $section 		string
	* @param $direction 	string
	*
	* @return $name 		string
	*/
	private function getName($key, $section, $direction) {
		if(!array_key_exists($direction,self::$mappings)) {
			return "";
		}

		if(!array_key_exists($section,self::$mappings[$direction])) {
			return "";
		}

		if(!array_key_exists($key,self::$mappings[$direction][$section])) {
			return "";
			
		}

		return self::$mappings[$direction][$section][$key];
	}
}