<?php                   
/**
* class for converting xml files in object and vice versa
*
* XML-Datei in Objekt-Struktur überführen.
* Der Funktion databay_XML2OBJ wird der Inhalt einer XML-Datei übergeben.
* Diese definiert die nötigen XML-Handler für die einleitenden, ausleiteten Tags und die
* das Datum dazwischen.
* Es wird ein Objekt-Baum mit der Klasse databay-XMLObj erzeugt.
* die Funktion databay_XML2OBJ liefert das Root-Objekt des Baumes zurück.
*
* $Root = databay_XML2OBJ($data);
*
* $Root->Depth ist ein Array auf alle Objekt der Hauptebene.
* mit count($Root->Depth) ermittelnt man die Anzahl der Objekt der Hauptebene.
* mit $Root-ChildNodes[1]->ChildNodes[...] greift mal im ersten HAuptebeneobjekt auf die Objekte
* der Ebene darunter zu.
*
* @author Aresch Yavari <ay@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class databay_XMLobj
{
	var $Name = "";
	var $ParentNode;
	var $attr;
	var $ChildNodes;
	var $Depth;
	var $Data;
	function databay_XMLobj($name, $attr) {
		$this->Name = $name;
		$this->attr = $attr;
	}
 
 /* returns the Name of this node */
 function getName() {
         return($this->Name);
 }
 
 function getAttr($key) {
         return($this->attr[$key]);
 }
 
 function getAttrs()
 {
 	return $this->attr;
 }
 
 function countElements($Name="") {
     if ($Name=="") return(count($this->ChildNodes));
     
     $C = 0;
     for ($i=0;$i<count($this->ChildNodes);$i++) {
         if (strtolower($this->ChildNodes[$i]->Name) == strtolower($Name)) $C++;
     }
     return($C);
 }
 
 function addElement($Name,$Data="") {

    $N = new databay_XMLobj($Name, "");
	$N->ParentNode = &$this;
	$N->Depth = $this->depth + 1;
    $N->Data = $Data;
    $this->ChildNodes[] = &$N;
 }
function getID($Name) {
	 for ($i=0;$i<$this->countElements("") ;$i++) {
		 $N = $this->ChildNodes[$i]->Name;
		 if (strtolower($N)==strtolower($Name)) {
			 return $i;
		 }
	 } 
	 return( -1 );
 }
 
 function getData($Name) {
	 for ($i=0;$i<$this->countElements("") ;$i++) {
		 $N = $this->ChildNodes[$i]->Name;
		 if (strtolower($N)==strtolower($Name)) {
			 return $this->ChildNodes[$i]->Data;
		 }
	 } 
	 return("");
 }
 
 function updateElement($Name,$Data="") {
	 
	 for ($i=0;$i<$this->countElements("") ;$i++) {
		 $N = $this->ChildNodes[$i]->Name;
		 if (strtolower($N)==strtolower($Name)) {
			 $this->ChildNodes[$i]->Data = $Data;
			 return;
		 }
	 } 
	 $this->addElement($Name,$Data);
	 
 }
 
}

$databay_AktObj = new databay_XMLobj("root", "");
$databay_Root = &$databay_AktObj;

function databay_startElement($parser, $name, $attrs) {
	global $depth;
	$depth[$parser]++;
	$N = new databay_XMLobj($name, $attrs);
	$N->ParentNode = &$GLOBALS["databay_AktObj"];
	$N->Depth = $depth[$parser];
	$GLOBALS["databay_AktObj"]->ChildNodes[] = &$N;
	$GLOBALS["databay_AktObj"] = &$N;
}

function databay_endElement($parser, $name) {
	global $depth;
	$depth[$parser]--;
	$GLOBALS["databay_AktObj"] = &$GLOBALS["databay_AktObj"]->ParentNode;
}
function databay_characterData($parser, $data) {
    	global $depth;
    	if (trim($data)!="") {
		$GLOBALS["databay_AktObj"]->Data = $data;
	}
}

function databay_XML2OBJ($xmldata) {
	$GLOBALS["databay_AktObj"] = new databay_XMLobj("root", "");
	$GLOBALS["databay_Root"] = &$GLOBALS["databay_AktObj"];

	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "databay_startElement", "databay_endElement");
	xml_set_character_data_handler($xml_parser, "databay_characterData");

	if (!xml_parse($xml_parser, $xmldata)) {
		die(sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($xml_parser)),xml_get_current_line_number($xml_parser)));
	}
	xml_parser_free($xml_parser);
	                                         
	return($GLOBALS["databay_Root"]);
}	

function databay_OBJ2XML($O) {

	$j = count($O->ChildNodes);
	for ($i=0;$i<$j;$i++) {
		$X .= "\n<".$O->ChildNodes[$i]->Name.">";
		$X .= $O->ChildNodes[$i]->Data;
		$X .= databay_OBJ2XML($O->ChildNodes[$i]);
		$X .= "</".$O->ChildNodes[$i]->Name.">\n";
	}
	return($X);
}

?>
