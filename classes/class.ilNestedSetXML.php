<?php

class ilNestedSetXML {
    // {{{ Vars
    
    /**
    *   Datenbank-handle
    */
    var $db;
    
    /**
    *   Linker und Rechter Rand eines Tags
    */
    var $LEFT = 0;
    var $RIGHT = 0;
    
    /**
    *   Verschachtelungstiefe der Tags. 
    *   Wird mit in der DB gespeichert.
    */ 
    var $DEPTH = 0;
    
    /**
    *   Die Buch-Obj-ID
    */
    var $obj_id;
    
    /**
    *   Der Typ der Daten zu denen dieser Eintrag gehört.
    */
    var $obj_type;
    
    /**
    *   SAX-Parser-Handle
    */
    var $xml_parser;
    
    /**
    *   Hier steht der letzte TAG-Name drin. Wird benötigt um Textblöcke die zusammengehören, 
    *   vom SAX-Parser aber einzeln übergeben werden zusammenhängend zu speichern
    */
    var $lastTag = "";
    
    var $ilias;
    // }}}
    
    function ilNestedSetXML() {
        global $ilias;

		$this->ilias =& $ilias;
        
        $this->db =& $this->ilias->db;
        $this->LEFT = 0;
        $this->RIGHT = 0;
        $this->DEPTH = 0;
    }

    
    /**
    *   Methode die aufgerufen wird, bei einem einleitenden Tag
    */
    function startElement($parser, $name, $attrs) {
        // {{{
        
        $this->lastTag = $name;
        $this->LEFT += 1;
        $this->RIGHT = $this->LEFT + 1;
        $this->DEPTH++;
        
        /**
        *   Eintragen des TAG-Names. Die hier erzeugte PK ist wichtig für die Parameter.
        *   Daher kann der Tag-Name nicht mit in die NestedSet-Tabelle, da diese zunächst nur Temporär angelegt wird und keine PKs hat. 
        */
        $this->db->query("INSERT INTO XmlTags ( tag_name,tag_depth ) VALUES ('".$name."','".$this->DEPTH."') ");
        // $pk = mysql_insert_id();
        $r = $this->db->query("SELECT LAST_INSERT_ID()");
        $row = $r->fetchRow();
        
        $pk = $row[0];
        
        /**
        *   Verschieben der Rechten Ränder der schon eingetragenen TAGs
        *   Es müssen beim Import nur Rechte Ränder verschoben werden, da von Links nach Rechts der NestedSetBaum aufgespalten wird.
        */
        $Q = "UPDATE NestedSetTemp SET ns_r=ns_r+2 WHERE ns_r>='".($this->LEFT)."' AND ns_book_fk='".$this->obj_id."' ";
        $this->db->query($Q);

        /**
        *   Eintragen des neues NestedSet eintrags mit den neuen Rändern.
        */
        $Q = "INSERT INTO NestedSetTemp (ns_book_fk,ns_type,ns_tag_fk,ns_l,ns_r) VALUES ('".$this->obj_id."','".$this->obj_type."','".$pk."',".$this->LEFT.",".$this->RIGHT.") ";
        
        $this->db->query($Q);
        
        
        if (is_array($attrs) && count($attrs)>0) {
            reset ($attrs);
            while (list ($key, $val) = each ($attrs)) {
            
                  $this->db->query("INSERT INTO XmlParam ( tag_fk,param_name,param_value ) VALUES ('".$pk."','$key','".addslashes($val)."') ");
            
            }
            
        }
        
        //vd(array($name,$this->LEFT,$this->RIGHT));
        
        return($pk);
        // }}}
    }

    /**
    *   Methode die aufgerufen wird, bei einem textblock
    */
    function characterData($parser, $data) {
        // {{{
        
        static $value_pk;
        
        if(trim($data)!="") {
            //vd(array("Text",$data));
            
            if ($this->lastTag == "TAGVALUE") {
                
                $this->db->query("UPDATE XmlValue SET tag_value=concat(tag_value,'".addslashes($data)."') WHERE tag_value_pk='".$value_pk."' ");
                
            } else {
                $tag_pk = $this->startElement($this->xml_parser,"TAGVALUE",array());
                $this->endElement($this->xml_parser,"TAGVALUE");
            
                $this->db->query("INSERT INTO XmlValue (tag_fk,tag_value) VALUES ('".$tag_pk."','".addslashes($data)."') ");
                $value_pk = mysql_insert_id();
                
                $this->lastTag = "TAGVALUE";
            }
            
        }
        // }}}
    }

    /**
    *   Methode die aufgerufen wird, bei einem ausleitenden Tag
    */
    function endElement($parser, $name) {
        // {{{
        
        $this->DEPTH--;
        $this->LEFT += 1;
        $this->lastTag = "";
        //vd(array("/".$name,$this->LEFT));
        // }}}
    }
    
    /**
    *   Import-Funktion.
    *   @param  String  xmldata Die XML-Struktur als Text
    *   @obj_id int     Die Buch-ID
    */
    function import($xmldata, $obj_id, $obj_type) {
        // {{{
        $this->db->query("DROP TABLE IF EXISTS NestedSetTemp");
        $Q = "CREATE TEMPORARY TABLE NestedSetTemp (
          ns_book_fk int(11)  NOT NULL,
          ns_type char(50) NOT NULL,
          ns_tag_fk int(11)  NOT NULL,
          ns_l int(11)  NOT NULL,
          ns_r int(11)  NOT NULL,
          KEY ns_tag_fk (ns_tag_fk),
          KEY ns_l (ns_l),
          KEY ns_r (ns_r),
          KEY ns_book_fk (ns_book_fk)
        ) TYPE=MyISAM ";
        $this->db->query($Q);
        
        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;

        /*
        $this->db->query("DELETE FROM XmlNestedSet");
        $this->db->query("DELETE FROM XmlTags");
        $this->db->query("DELETE FROM XmlParam");
        $this->db->query("DELETE FROM XmlValue");
        */
        
        $this->db->query("DELETE FROM NestedSetTemp");

        $this->xml_parser = xml_parser_create();
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->xml_parser,$this);
        xml_set_element_handler($this->xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($this->xml_parser, "characterData");
        
        if (!xml_parse($this->xml_parser, $xmldata)) {
            die(sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($xml_parser)),xml_get_current_line_number($xml_parser)));
        }
        xml_parser_free($this->xml_parser);
    
        $this->db->query("INSERT INTO XmlNestedSet SELECT * FROM NestedSetTemp");
        $this->db->query("DROP TABLE IF EXISTS NestedSetTemp");
        // }}}
    }
    
    /**
    *   Export-Funktion.
    *   @param  obj_id  int Buchid
    *   @return String  Die Xml-Struktur als Text
    */
    function export($obj_id, $type) {
        // {{{
        $result = $this->db->query("SELECT * FROM XmlNestedSet,XmlTags WHERE ns_tag_fk=tag_pk AND ns_book_fk='$obj_id' AND ns_type='$type' ORDER BY ns_l");
		if (DB::isError($result))
		{
       	    die($this->className."::checkTable(): ".$result->getMessage().":<br>".$q);
		}
        
        $xml = "";
        
        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) ) {
            //vd($row);
            
            $DS = substr("                          ",0,$row[tag_depth]);
            
            //$Anfang = "\n$DS<".$row[tag_name];
            $Anfang = "<".$row[tag_name];
            $result_param = $this->db->query("SELECT * FROM XmlParam WHERE tag_fk='$row[tag_pk]'");
            while (is_array($row_param = $result_param->fetchRow(DB_FETCHMODE_ASSOC) ) ) {
                $Anfang .= " ".$row_param[param_name]."=\"".$row_param[param_value]."\"";
            }

            $Anfang .= ">";
            $Ende = "</".$row[tag_name].">";
            
            if ($row[tag_name]=="TAGVALUE") {
                $result_value = $this->db->query("SELECT * FROM XmlValue WHERE tag_fk='$row[tag_pk]' ");
                $row_value = $result_value->fetchRow(DB_FETCHMODE_ASSOC);
                $Anfang = $row_value["tag_value"];
                $Ende = "";
                
                /*
                $Anfang = str_replace("<","&lt;",$Anfang);
                $Anfang = str_replace(">","&gt;",$Anfang);
                */
                $Anfang = htmlspecialchars($Anfang);
                // $Anfang = utf8_encode($Anfang);
            }
            
            if ( $row[tag_depth] == $lastDepth ) {
                $xml .= $E[$lastDepth];
                unset($E[$lastDepth]);
            } else if ( $row[tag_depth] < $lastDepth ) {
                $xml .= $E[$lastDepth];
                unset($E[$lastDepth]);
                
                $xml .= $E[$row[tag_depth]];
                unset($E[$row[tag_depth]]);
            } 
            
            $xml .= $Anfang;
            
            $lastDepth = $row[tag_depth];
            
            $E[$row[tag_depth]] = $Ende . $E[$row[tag_depth]]; 
                
        }
        
        
        for ($i=count($E);$i>=0;$i--) {
            $xml .= $E[$i];
        }

        return($xml);
        // }}}
    }
    
    // ------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------
    // {{{  Zusätzliche Funktionen
    function init($obj_id,$obj_type) {
        // {{{
        $result = $this->db->query("SELECT * FROM XmlNestedSet,XmlTags WHERE ns_book_fk='".$obj_id."' AND ns_type='".$obj_type."' AND ns_tag_fk=tag_pk ORDER BY ns_l LIMIT 1");
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        
        // vd($row);
        
        $this->LEFT = $row["ns_l"];
        $this->RIGHT = $row["ns_r"];
        $this->DEPTH = $row["tag_depth"];
        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;
        // }}}
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    function getTagName() {
        
        $result = $this->db->query("SELECT * FROM XmlNestedSet,XmlTags WHERE ns_book_fk='".$this->obj_id."' AND ns_type='".$this->obj_type."' AND ns_l='".$this->LEFT."' AND ns_r='".$this->RIGHT."' AND ns_tag_fk=tag_pk LIMIT 1");
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        
        return($row["tag_name"]);
        
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    function setTagName($tagName) {
        
        $result = $this->db->query("SELECT * FROM XmlNestedSet WHERE ns_book_fk='".$this->obj_id."' AND ns_type='".$this->obj_type."' AND ns_l='".$this->LEFT."' AND ns_r='".$this->RIGHT."' LIMIT 1");
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        
        $this->db->query("UPDATE XmlTags SET tag_name='$tagName' WHERE tag_pk='".$row["ns_tag_fk"]."'");
        
        return($row["tagName"]);
        
    }
    
    
    // ------------------------------------------------------------------------------------------------------------------------------
    function getTagValue() {
        
        $V = array();
        
        $result = $this->db->query("SELECT * FROM XmlNestedSet,XmlTags WHERE ns_tag_fk=tag_pk AND ns_book_fk='$obj_id' AND ns_l>='".$this->LEFT."' AND ns_r<='".$this->RIGHT."' AND tag_depth='".($this->DEPTH+1)."' ORDER BY ns_l");
        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) ) {
            if ($row[tag_name]=="TAGVALUE") {
                $result2 = $this->db->query("SELECT * FROM XmlValue WHERE tag_fk='".$row[tag_pk]."' ");
                $row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
                $V[] = $row2[tag_value];
            } else {
                $xml = new ilNestedSetXml();
                
                $xml->LEFT = $row["ns_l"];
                $xml->RIGHT = $row["ns_r"];
                $xml->DEPTH = $row["tag_depth"];
                $xml->obj_id = $obj_id;
                $xml->obj_type = $obj_type;
                
                $V[] = $xml;
                
            }
        }
        
        return($V);
        
    }
    // }}}
    // ------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------

    
}

/*
$xml = new nestedSet($db);
//$xmldata = implode(file("LO237.xml"));
$xmldata = implode(file("import1.xml"));
$xml->import($xmldata,$obj_id,"structure");

$value = $xml->export($obj_id);
$fp = fopen("export.xml","w");
fwrite($fp,$value);
fclose($fp);
*/

/*
$xml = new nestedSet();

$xml->init($obj_id, "structure");
$xml->setTagName("A".time());
$tagName = $xml->getTagName();
// vd($tagName);

$V = $xml->getTagValue();
//vd($text);
*/
?>
