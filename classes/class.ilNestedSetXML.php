<?php

require_once "./classes/class.ilXML2DOM.php";

class ilNestedSetXML 
{
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
	
	var $dom;
	
    // }}}
    
    function ilNestedSetXML() 
	{
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
    function startElement($parser, $name, $attrs) 
	{
        // {{{
        
        $this->lastTag = $name;
        $this->LEFT += 1;
        $this->RIGHT = $this->LEFT + 1;
        $this->DEPTH++;
        
        /**
        *   Eintragen des TAG-Names. Die hier erzeugte PK ist wichtig für die Parameter.
        *   Daher kann der Tag-Name nicht mit in die NestedSet-Tabelle, da diese zunächst nur Temporär angelegt wird und keine PKs hat. 
        */
        $this->db->query("INSERT INTO xmltags ( tag_name,tag_depth ) VALUES ('".$name."','".$this->DEPTH."') ");
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
        
        
        if (is_array($attrs) && count($attrs)>0) 
		{
            reset ($attrs);
            while (list ($key, $val) = each ($attrs)) 
			{
            
                  $this->db->query("INSERT INTO xmlparam ( tag_fk,param_name,param_value ) VALUES ('".$pk."','$key','".addslashes($val)."') ");
            
            }
            
        }
        
        //vd(array($name,$this->LEFT,$this->RIGHT));
        
        return($pk);
        // }}}
    }

    /**
    *   Methode die aufgerufen wird, bei einem textblock
    */
    function characterData($parser, $data) 
	{
        // {{{

        static $value_pk;
        
        if(trim($data)!="") {
            //vd(array("Text",$data));
            
            if ($this->lastTag == "TAGVALUE") 
			{
                
                $this->db->query("UPDATE xmlvalue SET tag_value=concat(tag_value,'".addslashes($data)."') WHERE tag_value_pk='".$value_pk."' ");
                
            } else {
                $tag_pk = $this->startElement($this->xml_parser,"TAGVALUE",array());
                $this->endElement($this->xml_parser,"TAGVALUE");
            
                $this->db->query("INSERT INTO xmlvalue (tag_fk,tag_value) VALUES ('".$tag_pk."','".addslashes($data)."') ");
                // $value_pk = mysql_insert_id();
				$r = $this->db->query("SELECT LAST_INSERT_ID()");
				$row = $r->fetchRow();
				$value_pk = $row[0];

                $this->lastTag = "TAGVALUE";
            }
            
        }
        // }}}
    }

    /**
    *   Methode die aufgerufen wird, bei einem ausleitenden Tag
    */
    function endElement($parser, $name)
	{
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
    function import($xmldata, $obj_id, $obj_type)
	{
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
		$this->DEPTH = 0;
		$this->LEFT = 0;
		$this->RIGHT = 0;

        /*
        $this->db->query("DELETE FROM xmlnestedset");
        $this->db->query("DELETE FROM xmltags");
        $this->db->query("DELETE FROM xmlparam");
        $this->db->query("DELETE FROM xmlvalue");
        */

        $this->db->query("DELETE FROM NestedSetTemp");

        $this->xml_parser = xml_parser_create("UTF-8");
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->xml_parser,$this);
        xml_set_element_handler($this->xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($this->xml_parser, "characterData");

        if (!xml_parse($this->xml_parser, $xmldata)) {
            die(sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($xml_parser)),xml_get_current_line_number($xml_parser)));
        }
        xml_parser_free($this->xml_parser);

        $this->db->query("INSERT INTO xmlnestedset SELECT * FROM NestedSetTemp");
        $this->db->query("DROP TABLE IF EXISTS NestedSetTemp");
        // }}}
    }

    /**
    *   Export-Funktion.
    *   @param  obj_id  int Buchid
    *   @return String  Die Xml-Struktur als Text
    */
    function export($obj_id, $type)
	{
        // {{{
		$query = "SELECT * FROM xmlnestedset,xmltags WHERE ns_tag_fk=tag_pk AND ns_book_fk='$obj_id' AND ns_type='$type' ORDER BY ns_l";

        $result = $this->db->query($query);
		if (DB::isError($result))
		{
       	    die($this->className."::checkTable(): ".$result->getMessage().":<br>".$q);
		}

        $xml = "";
		$lastDepth = -1;

        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) ) {

			// {{{ Anfang & Endtag
            $Anfang = "<".$row[tag_name];
            $result_param = $this->db->query("SELECT * FROM xmlparam WHERE tag_fk='$row[tag_pk]'");
            while (is_array($row_param = $result_param->fetchRow(DB_FETCHMODE_ASSOC) ) ) {
                $Anfang .= " ".$row_param[param_name]."=\"".$row_param[param_value]."\"";
            }

            $Anfang .= ">";
            $Ende = "</".$row[tag_name].">";
            // }}}

			// {{{ TagValue
            if ($row[tag_name]=="TAGVALUE") {
                $result_value = $this->db->query("SELECT * FROM xmlvalue WHERE tag_fk='$row[tag_pk]' ");
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
			// }}}

			/*
            if ( $row[tag_depth] == $lastDepth ) {
                $xml .= $E[$lastDepth];
                unset($E[$lastDepth]);
            } else if ( $row[tag_depth] < $lastDepth ) {
                $xml .= $E[$lastDepth];
                unset($E[$lastDepth]);

                $xml .= $E[$row[tag_depth]];
                unset($E[$row[tag_depth]]);
            }
            */


			$D = $row[tag_depth];

			if ($D==$lastDepth) {
				$xml .= $xmlE[$D];
				$xml .= $Anfang;
				$xmlE[$D] = $Ende;
			} else if ($D>$lastDepth) {
				$xml .= $Anfang;
				$xmlE[$D] = $Ende;
			} else {
				for ($i=$lastDepth;$i>=$D;$i--) {
					$xml .= $xmlE[$i];
				}
				$xml .= $Anfang;
				$xmlE[$D] = $Ende;
			}
			
			
			//$xmlE[$D] = $Ende.$xmlE[$D];
			
			
            //$xml .= $Anfang;
            
            $lastDepth = $D;
            
            //$E[$lastDepth] = $Ende . $E[$lastDepth]; 
                
        }

		for ($i=$lastDepth;$i>0;$i--) {
			$xml .= $xmlE[$i];
		}
        
        /*
        for ($i=count($E);$i>=0;$i--) {
            $xml .= $E[$i];
        }
		*/
		/*
		$X = str_replace("</","\n</",$xml);
		echo nl2br(htmlspecialchars($X));
		exit;
		*/
        return($xml);
        // }}}
    }
    
    // ------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------------
    // {{{  Zusätzliche Funktionen
    function init($obj_id,$obj_type) 
	{
        // {{{
		$query = "SELECT * FROM xmlnestedset,xmltags WHERE ns_book_fk='".$obj_id."' AND ns_type='".$obj_type."' AND ns_tag_fk=tag_pk ORDER BY ns_l LIMIT 1";
        $result = $this->db->query($query);
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
    function getTagName() 
	{

        $query = "SELECT * FROM xmlnestedset,xmltags WHERE ns_book_fk='".$this->obj_id."' AND ns_type='".$this->obj_type."' AND ns_l='".$this->LEFT."' AND ns_r='".$this->RIGHT."' AND ns_tag_fk=tag_pk LIMIT 1";
		$result = $this->db->query($query);
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        
        return($row["tag_name"]);
        
    }

    // ------------------------------------------------------------------------------------------------------------------------------
    function setTagName($tagName) 
	{
        
		$query = "SELECT * FROM xmlnestedset WHERE ns_book_fk='".$this->obj_id."' AND ns_type='".$this->obj_type."' AND ns_l='".$this->LEFT."' AND ns_r='".$this->RIGHT."' LIMIT 1";
        $result = $this->db->query($query);
        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        
		$query = "UPDATE xmltags SET tag_name='$tagName' WHERE tag_pk='".$row["ns_tag_fk"]."'";
        $this->db->query($query);
        
        return($row["tagName"]);
        
    }
    
    
    // ------------------------------------------------------------------------------------------------------------------------------
    function getTagValue() 
	{
        
        $V = array();
        
        $query = "SELECT * FROM xmlnestedset,xmltags WHERE ns_tag_fk=tag_pk AND ns_book_fk='".$this->obj_id."' AND ns_type='".$this->obj_type."' AND ns_l>='".$this->LEFT."' AND ns_r<='".$this->RIGHT."' AND tag_depth='".($this->DEPTH+1)."' ORDER BY ns_l";
		$result = $this->db->query($query);
        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) ) 
		{
            if ($row[tag_name]=="TAGVALUE") 
			{
				$query = "SELECT * FROM xmlvalue WHERE tag_fk='".$row[tag_pk]."' ";
                $result2 = $this->db->query($query);
                $row2 = $result2->fetchRow(DB_FETCHMODE_ASSOC);
                $V[] = $row2[tag_value];
            } 
			else 
			{
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
	
	function setTagValue($value) 
	{
        $V = array();

        $query = "SELECT * FROM xmlnestedset,xmltags
						LEFT JOIN xmlvalue ON xmltags.tag_pk=xmlvalue.tag_fk
						WHERE ns_tag_fk=tag_pk AND 
							ns_book_fk='".$this->obj_id."' AND 
							ns_type='".$this->obj_type."' AND 
							ns_l>='".$this->LEFT."' AND 
							ns_r<='".$this->RIGHT."' AND 
							tag_depth='".($this->DEPTH+1)."' AND
							tag_name = 'TAGVALUE'
							ORDER BY ns_l";
		$result = $this->db->query($query);
		
        if (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC) ) ) 
		{
			
			$query = "UPDATE xmlvalue SET tag_value='".addslashes($value)."' WHERE tag_value_pk='".$row["tag_value_pk"]."' ";
			$this->db->query($query);
			
		} else {
			
			/**
			*	Neu hinzufügen.
			*/
			
		}
	}
/*	
	function countSubTags($filter="") 
	{
		// {{{
		if ( $filter == "") 
		{
			$query = "SELECT * FROM xmlnestedset,xmltags WHERE
							ns_tag_fk=tag_pk AND 
							ns_book_fk='".$this->obj_id."' AND 
							ns_l>='".$this->LEFT."' AND 
							ns_r<='".$this->RIGHT."' AND 
							tag_depth='".($this->DEPTH+1)."' ORDER BY ns_l";
		} 
		else 
		{
			$query = "SELECT * FROM xmlnestedset,xmltags WHERE
							ns_tag_fk=tag_pk AND 
							ns_book_fk='".$this->obj_id."' AND 
							ns_l>='".$this->LEFT."' AND 
							ns_r<='".$this->RIGHT."' AND 
							tag_depth='".($this->DEPTH+1)."' AND 
							tag_name='".$filter."' ORDER BY ns_l";
		}
		$result = $this->db->query($query);
		
		$num = $result->numRows();
		return($num);
		// }}}
	}
	
    function getValue($path, $l=-1, $r=-1, $depth=1) 
	{
        // {{{
        if (is_string($path)) 
		{
			$path = explode("->",$path);
		}
		
        if ($l==-1 && $r==-1) 
		{
			$path[] = "TAGVALUE";
			$l = $this->LEFT;
			$r = $this->RIGHT;
		}
		
		
        $ret = "";
        if ($depth<count($path)) 
		{
            if (count($path)==$depth+1) 
			{
                // Angekommen beim letzten Element wird jetzt der Join auf die Values und Parameter erweitert
                $Q = "SELECT Na.*,B.*,xmlvalue.* FROM
                        xmlnestedset AS Na,
                        xmlnestedset AS Nb,
                        xmltags AS A,
                        xmltags AS B
						LEFT JOIN xmlvalue ON xmlvalue.tag_fk=B.tag_pk
                       WHERE 
					    Na.ns_book_fk='".$this->obj_id."' AND Na.ns_type='".$this->obj_type."' AND
                        Na.ns_l>=$l AND Na.ns_r<=$r AND
                        Na.ns_tag_fk=A.tag_pk AND 
                        A.tag_name='".$path[$depth-1]."' AND    
                        B.tag_depth=A.tag_depth+1 AND 
                        B.tag_name='".$path[$depth]."' AND 
                        B.tag_pk=Nb.ns_tag_fk AND 
                        Nb.ns_l>Na.ns_l AND Nb.ns_r<Na.ns_r AND
						Nb.ns_book_fk='".$this->obj_id."' AND Nb.ns_type='".$this->obj_type."' 

                        
                       ";	//AND V.tag_value='".$needle."'
                $res = $this->db->query($Q);
                
            } 
			else 
			{
                // Solange man nicht am Ende der Kette angekommen ist, werden nur Tag mit Parent-Tag vergleichen.
                $Q = "SELECT Nb.* FROM 
                        xmlnestedset AS Na,
                        xmlnestedset AS Nb,
                        xmltags AS A,
                        xmltags AS B
                       WHERE 
					    Na.ns_book_fk='".$this->obj_id."' AND Na.ns_type='".$this->obj_type."' AND
                        Na.ns_l>=$l AND Na.ns_r<=$r AND 
                        Na.ns_tag_fk = A.tag_pk AND 
                        A.tag_name='".$path[$depth-1]."' AND 
                        B.tag_depth=A.tag_depth+1 AND 
                        B.tag_name='".$path[$depth]."' AND 
                        B.tag_pk=Nb.ns_tag_fk AND
						Nb.ns_book_fk='".$this->obj_id."' AND Nb.ns_type='".$this->obj_type."' AND
                        Nb.ns_l>$l AND Nb.ns_r<$r
						
                       ";
                $res = $this->db->query($Q);
                
            }
			
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) 
			{

                if (count($path)==$depth+1) 
				{
                    // $ret[] = $row;
					$ret .= $row["tag_value"]; 
                } 
				else 
				{
                    $ret = $this->getValue($path,$row["ns_l"],$row["ns_r"],$depth+1);
                }
            }
        }            
        return($ret);
        // }}}
    }

    function getParameter($path, $parameter, $l=-1, $r=-1, $depth=1) 
	{
        // {{{
        if (is_string($path)) 
		{
			$path = explode("->",$path);
		}
		
        if ($l==-1 && $r==-1) 
		{
			$l = $this->LEFT;
			$r = $this->RIGHT;
		}
		
		
        $ret = "";
        if ($depth<count($path)) 
		{
            if (count($path)==$depth+1) 
			{
                // Angekommen beim letzten Element wird jetzt der Join auf die Values und Parameter erweitert
                $Q = "SELECT Na.*,B.*,xmlparam.* FROM
                        xmlnestedset AS Na,
                        xmlnestedset AS Nb,
                        xmltags AS A,
                        xmltags AS B
						LEFT JOIN xmlparam ON (xmlparam.tag_fk=B.tag_pk AND xmlparam.param_name='$parameter')
                       WHERE 
					    Na.ns_book_fk='".$this->obj_id."' AND Na.ns_type='".$this->obj_type."' AND
                        Na.ns_l>=$l AND Na.ns_r<=$r AND
                        Na.ns_tag_fk=A.tag_pk AND 
                        A.tag_name='".$path[$depth-1]."' AND    
                        B.tag_depth=A.tag_depth+1 AND 
                        B.tag_name='".$path[$depth]."' AND 
                        B.tag_pk=Nb.ns_tag_fk AND 
                        Nb.ns_l>Na.ns_l AND Nb.ns_r<Na.ns_r AND
						Nb.ns_book_fk='".$this->obj_id."' AND Nb.ns_type='".$this->obj_type."' 
                          
                        
                       ";	//AND V.tag_value='".$needle."'
                $res = $this->db->query($Q);
                
            } 
			else 
			{
                // Solange man nicht am Ende der Kette angekommen ist, werden nur Tag mit Parent-Tag vergleichen.
                $Q = "SELECT Nb.* FROM 
                        xmlnestedset AS Na,
                        xmlnestedset AS Nb,
                        xmltags AS A,
                        xmltags AS B
                       WHERE 
					    Na.ns_book_fk='".$this->obj_id."' AND Na.ns_type='".$this->obj_type."' AND
                        Na.ns_l>=$l AND Na.ns_r<=$r AND 
                        Na.ns_tag_fk = A.tag_pk AND 
                        A.tag_name='".$path[$depth-1]."' AND 
                        B.tag_depth=A.tag_depth+1 AND 
                        B.tag_name='".$path[$depth]."' AND 
                        B.tag_pk=Nb.ns_tag_fk AND
						Nb.ns_book_fk='".$this->obj_id."' AND Nb.ns_type='".$this->obj_type."' AND
                        Nb.ns_l>$l AND Nb.ns_r<$r
						
                       ";
                $res = $this->db->query($Q);
                
            }
			
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) 
			{
                
                if (count($path)==$depth+1) 
				{
                    // $ret[] = $row;
					$ret .= $row["param_value"]; 
                } 
				else 
				{
                    $ret = $this->getParameter($path,$parameter,$row["ns_l"],$row["ns_r"],$depth+1);
                }
            }
        }            
        return($ret);
        // }}}
    }    	
	
    function getNode($path, $l=-1, $r=-1, $depth=1) 
	{
        // {{{
        if (is_string($path)) 
		{
			$path = explode("->",$path);
		}
		
        if ($l==-1 && $r==-1) 
		{
			
			$l = $this->LEFT;
			$r = $this->RIGHT;
		}
		
		
        $ret = "";
        if ($depth<count($path)) 
		{
			// Solange man nicht am Ende der Kette angekommen ist, werden nur Tag mit Parent-Tag vergleichen.
			$Q = "SELECT Nb.*,B.* FROM 
					xmlnestedset AS Na,
					xmlnestedset AS Nb,
					xmltags AS A,
					xmltags AS B
				   WHERE 
					Na.ns_book_fk='".$this->obj_id."' AND Na.ns_type='".$this->obj_type."' AND
					Na.ns_l>=$l AND Na.ns_r<=$r AND 
					Na.ns_tag_fk = A.tag_pk AND 
					A.tag_name='".$path[$depth-1]."' AND 
					B.tag_depth=A.tag_depth+1 AND 
					B.tag_name='".$path[$depth]."' AND 
					B.tag_pk=Nb.ns_tag_fk AND
					Nb.ns_book_fk='".$this->obj_id."' AND Nb.ns_type='".$this->obj_type."' AND
					Nb.ns_l>$l AND Nb.ns_r<$r
					
				   ";
			$res = $this->db->query($Q);
                
            while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) 
			{
                if (count($path)==$depth+1) 
				{
                    $N = new ilNestedSetXML();
					$N->init($this->obj_id,$this->obj_type);
					$N->LEFT = $row["ns_l"];
					$N->RIGHT = $row["ns_r"];
					$N->DEPTH = $row["tag_depth"];
					
					$ret[] = $N;
                } 
				else 
				{
                    $ret = $this->getNode($path,$row["ns_l"],$row["ns_r"],$depth+1);
                }
            }
        }            
        return($ret);
        // }}}
    }
*/	
	function getXpathNodes(&$dom, $expr) 
	{
		if (is_object($dom))
		{
			$xpth = $dom->xpath_new_context();
			$xnode = xpath_eval($xpth,$expr);
			if (is_array ($xnode->nodeset)) 
			{
				return($xnode->nodeset);
			}
		}
		return Null;
	}	
	
	/**
	*	inits dom-object from given xml-content
	*/
	function initDom() 
	{
		$xml = $this->export($this->obj_id, $this->obj_type);

		$xml_test = '
		<MetaData>
			<General Structure="Atomic">
				<Identifier Catalog="ILIAS" Entry="34">Identifier 34 in ILIAS</Identifier>
				<Identifier Catalog="ILIAS" Entry="45">Identifier 45 in ILIAS</Identifier>
				<Identifier Catalog="ILIAS" Entry="67">Identifier 67 in ILIAS</Identifier>
			</General>
		</MetaData>
		';
		
//		$xml = $xml_test;
		
		if ($xml=="") {
			return(false);
		} else {
#		echo "<pre>".htmlspecialchars($xml)."</pre>";
			$this->dom = domxml_open_mem($xml);
			return(true);
		}
	}

	/**
	*	parse XML code and add it to a given DOM object as a new node
	*/
	function addXMLNode($xPath, $xml, $index = 0) 
	{
		$newDOM = new XML2DOM($xml);
		$nodes = $this->getXpathNodes($this->dom, $xPath);
#		echo "addXMLNode() -> Index: " . $index . " | Path: " . $xPath . "<br>\n";
#		echo htmlspecialchars($xml) . "<br>\n";
		if (count($nodes) > 0)
		{
			$newDOM->insertNode($this->dom, $nodes[$index]);
#			echo htmlspecialchars($this->dom->dump_mem(0));
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*	returns first content of this node
	*/
	function getFirstDomContent($xPath) 
	{
		$content = "";
		if (is_object($this->dom))
		{
#			echo $xPath;
#			echo htmlspecialchars($this->dom->dump_mem(0));
			$node = $this->getXpathNodes($this->dom,$xPath);
			$c = $node[0]->children();
			$content = $c[0]->content;
		}
		return($content);
	}	
	
	/**
	*	deletes node
	*/
	function deleteDomNode($xPath, $name, $index) 
	{
		if (strpos($index, ","))
		{
			$indices = explode(",", $index);
			$nodes = $this->getXpathNodes($this->dom, $xPath);
			if (count($nodes) > 0)
			{
				$children = $nodes[$indices[0]]->child_nodes();
				if (count($children) > 0)
				{
					$j = 0;
					for ($i = 0; $i < count($children); $i++)
					{
						if ($children[$i]->tagname == $name)
						{
							if ($j == $indices[1])
							{
								$children[$i]->unlink_node();
								return true;
							}
							$j++;
						}
					}
				}
			}
		}
		else
		{
			$nodes = $this->getXpathNodes($this->dom, $xPath . "/" . $name);
			if (count($nodes) > 0)
			{
				$nodes[$index]->unlink_node();
				return true;
			}
		}
		return false;
	}	
	
	/**
	*	adds node
	*/
	function addDomNode($xPath, $name, $value = "", $attributes = "", $index = 0) 
	{
		$nodes = $this->getXpathNodes($this->dom, $xPath);
		if (count($nodes) > 0)
		{
			$node = $this->dom->create_element($name);
			if ($value != "")
			{
				$node->set_content(utf8_encode($value));
			}
			if (is_array($attributes))
			{
				for ($i = 0; $i < count($attributes); $i++)
				{
					$node->set_attribute($attributes[$i]["name"], utf8_encode($attributes[$i]["value"]));
				}
			}
			$nodes[$index]->append_child($node);
			return true;
		}
		else
		{
			return false;
		}
	}	
	
	/**
	*	updates dom node
	*/
	function updateDomNode($xPath, $meta, $no = 0) 
	{
		$update = false;

		$nodes = $this->getXpathNodes($this->dom, $xPath);
#		echo "Path: " . $xPath . "<br>\n";
#		var_dump("<pre>", $meta, "</pre>");
#		var_dump("<pre>", $nodes, "</pre>");
		if (count($nodes) > 0)
		{

#echo $nodes[0]->node_name();
			/* General */
			if ($nodes[0]->node_name() == "General")
			{

				$xml = '<General Structure="' . ilUtil::stripSlashes($meta["Structure"]) . '">';
				for ($i = 0; $i < count($meta["Identifier"]); $i++)
				{
					$xml .= '<Identifier Catalog="' . ilUtil::stripSlashes($meta["Identifier"][$i]["Catalog"]) . '" Entry="' .  str_replace("\"", "", ilUtil::stripSlashes($meta["Identifier"][$i]["Entry"])) . '"/>';
				}
				$xml .= '<Title Language="' . ilUtil::stripSlashes($meta["Title"]["Language"]) . '">' . ilUtil::stripSlashes($meta["Title"]["Value"]) . '</Title>';
				for ($i = 0; $i < count($meta["Language"]); $i++)
				{
					$xml .= '<Language Language="' . ilUtil::stripSlashes($meta["Language"][$i]["Language"]) . '"/>';
				}
				for ($i = 0; $i < count($meta["Description"]); $i++)
				{
					$xml .= '<Description Language="' . ilUtil::stripSlashes($meta["Description"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["Description"][$i]["Value"]) . '</Description>';
				}
				for ($i = 0; $i < count($meta["Keyword"]); $i++)
				{
					$xml .= '<Keyword Language="' . ilUtil::stripSlashes($meta["Keyword"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["Keyword"][$i]["Value"]) . '</Keyword>';
				}
				if ($meta["Coverage"] != "")
				{
					$xml .= '<Coverage Language="' . ilUtil::stripSlashes($meta["Coverage"]["Language"]) . '">' . ilUtil::stripSlashes($meta["Coverage"]["Value"]) . '</Coverage>';
				}
				$xml .= '</General>';
#				echo htmlspecialchars($xml);

				$update = true;
			}

			/* Lifecycle */
			else if ($nodes[0]->node_name() == "Lifecycle")
			{
				$xml = '<Lifecycle Status="' . $meta["Status"] . '">';
				$xml .= '<Version Language="' . ilUtil::stripSlashes($meta["Version"]["Language"]) . '">' . ilUtil::stripSlashes($meta["Version"]["Value"]) . '</Version>';
				for ($i = 0; $i < count($meta["Contribute"]); $i++)
				{
					$xml .= '<Contribute Role="' . ilUtil::stripSlashes($meta["Contribute"][$i]["Role"]) . '">';
					$xml .= '<Date>' . ilUtil::stripSlashes($meta["Contribute"][$i]["Date"]) . '</Date>';
					for ($j = 0; $j < count($meta["Contribute"][$i]["Entity"]); $j++)
					{
						$xml .= '<Entity>' . ilUtil::stripSlashes($meta["Contribute"][$i]["Entity"][$j]) . '</Entity>';
					}
					$xml .= '</Contribute>';
				}
				$xml .= '</Lifecycle>';
#				echo htmlspecialchars($xml);

				$update = true;
			}

			/* Meta-Metadata */
			else if ($nodes[0]->node_name() == "Meta-Metadata")
			{

				$xml = '<Meta-Metadata MetadataScheme="LOM v 1.0" Language="' . ilUtil::stripSlashes($meta["Language"]) . '">';
				for ($i = 0; $i < count($meta["Identifier"]); $i++)
				{
					$xml .= '<Identifier Catalog="' . ilUtil::stripSlashes($meta["Identifier"][$i]["Catalog"]) . '" Entry="' .  str_replace("\"", "", ilUtil::stripSlashes($meta["Identifier"][$i]["Entry"])) . '"/>';
				}
				for ($i = 0; $i < count($meta["Contribute"]); $i++)
				{
					$xml .= '<Contribute Role="' . ilUtil::stripSlashes($meta["Contribute"][$i]["Role"]) . '">';
					$xml .= '<Date>' . ilUtil::stripSlashes($meta["Contribute"][$i]["Date"]) . '</Date>';
					for ($j = 0; $j < count($meta["Contribute"][$i]["Entity"]); $j++)
					{
						$xml .= '<Entity>' . ilUtil::stripSlashes($meta["Contribute"][$i]["Entity"][$j]) . '</Entity>';
					}
					$xml .= '</Contribute>';
				}
				$xml .= '</Meta-Metadata>';
#				echo htmlspecialchars($xml);

				$update = true;
			}

			/* Technical */
			else if ($nodes[0]->node_name() == "Technical")
			{

				$xml = '<Technical>';
				for ($i = 0; $i < count($meta["Format"]); $i++)
				{
					$xml .= '<Format>' . ilUtil::stripSlashes($meta["Format"][$i]) . '</Format>';
				}
				if ($meta["Size"] != "")
				{
					$xml .= '<Size>' . ilUtil::stripSlashes($meta["Size"]) . '</Size>';
				}
				for ($i = 0; $i < count($meta["Location"]); $i++)
				{
					$xml .= '<Location Type="' . ilUtil::stripSlashes($meta["Location"][$i]["Type"]) . '">' . ilUtil::stripSlashes($meta["Location"][$i]["Value"]) . '</Location>';
				}
				if (is_array($meta["Requirement"]))
				{
					for ($i = 0; $i < count($meta["Requirement"]); $i++)
					{
						$xml .= '<Requirement>';
						$xml .= '<Type>';
						if (is_array($meta["Requirement"][$i]["Type"]["OperatingSystem"]))
						{
							$xml .= '<OperatingSystem Name="' . ilUtil::stripSlashes($meta["Requirement"][$i]["Type"]["OperatingSystem"]["Name"]) . '" MinimumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["Requirement"][$i]["Type"]["OperatingSystem"]["MinimumVersion"])) . '" MaximumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["Requirement"][$i]["Type"]["OperatingSystem"]["MaximumVersion"])) . '"/>';
						}
						if (is_array($meta["Requirement"][$i]["Type"]["Browser"]))
						{
							$xml .= '<Browser Name="' . ilUtil::stripSlashes($meta["Requirement"][$i]["Type"]["Browser"]["Name"]) . '" MinimumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["Requirement"][$i]["Type"]["Browser"]["MinimumVersion"])) . '" MaximumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["Requirement"][$i]["Type"]["Browser"]["MaximumVersion"])) . '"/>';
						}
						$xml .= '</Type>';
						$xml .= '</Requirement>';
					}
				}
				else if (is_array($meta["OrComposite"]))
				{
					for ($j = 0; $j < count($meta["OrComposite"]); $j++)
					{
						$xml .= '<OrComposite>';
						for ($i = 0; $i < count($meta["OrComposite"][$j]["Requirement"]); $i++)
						{
							$xml .= '<Requirement>';
							$xml .= '<Type>';
							if (is_array($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["OperatingSystem"]))
							{
								$xml .= '<OperatingSystem Name="' . ilUtil::stripSlashes($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["OperatingSystem"]["Name"]) . '" MinimumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["OperatingSystem"]["MinimumVersion"])) . '" MaximumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["OperatingSystem"]["MaximumVersion"])) . '"/>';
							}
							if (is_array($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["Browser"]))
							{
								$xml .= '<Browser Name="' . ilUtil::stripSlashes($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["Browser"]["Name"]) . '" MinimumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["Browser"]["MinimumVersion"])) . '" MaximumVersion="' . str_replace("\"", "", ilUtil::stripSlashes($meta["OrComposite"][$j]["Requirement"][$i]["Type"]["Browser"]["MaximumVersion"])) . '"/>';
							}
							$xml .= '</Type>';
							$xml .= '</Requirement>';
						}
						$xml .= '</OrComposite>';
					}
				}
				if (is_array($meta["InstallationRemarks"]))
				{
					$xml .= '<InstallationRemarks Language="' . ilUtil::stripSlashes($meta["InstallationRemarks"]["Language"]) . '">' . ilUtil::stripSlashes($meta["InstallationRemarks"]["Value"]) . '</InstallationRemarks>';
				}
				if (is_array($meta["OtherPlattformRequirements"]))
				{
					$xml .= '<OtherPlattformRequirements Language="' . ilUtil::stripSlashes($meta["OtherPlattformRequirements"]["Language"]) . '">' . ilUtil::stripSlashes($meta["OtherPlattformRequirements"]["Value"]) . '</OtherPlattformRequirements>';
				}
				if ($meta["Duration"] != "")
				{
					$xml .= '<Duration>' . ilUtil::stripSlashes($meta["Duration"]) . '</Duration>';
				}
				$xml .= '</Technical>';
#				echo htmlspecialchars($xml);

				$update = true;
			}

			/* Educational */
			else if ($nodes[0]->node_name() == "Educational")
			{

				$xml = '<Educational InteractivityType="' . ilUtil::stripSlashes($meta["InteractivityType"]) . '" LearningResourceType="' . ilUtil::stripSlashes($meta["LearningResourceType"]) . '" InteractivityLevel="' . ilUtil::stripSlashes($meta["InteractivityLevel"]) . '" SemanticDensity="' . ilUtil::stripSlashes($meta["SemanticDensity"]) . '" IntendedEndUserRole="' . ilUtil::stripSlashes($meta["IntendedEndUserRole"]) . '" Context="' . ilUtil::stripSlashes($meta["Context"]) . '" Difficulty="' . ilUtil::stripSlashes($meta["Difficulty"]) . '">';
				$xml .= '<TypicalLearningTime>' . ilUtil::stripSlashes($meta["TypicalLearningTime"]) . '</TypicalLearningTime>';
				for ($i = 0; $i < count($meta["TypicalAgeRange"]); $i++)
				{
					$xml .= '<TypicalAgeRange Language="' . ilUtil::stripSlashes($meta["TypicalAgeRange"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["TypicalAgeRange"][$i]["Value"]) . '</TypicalAgeRange>';
				}
				for ($i = 0; $i < count($meta["Description"]); $i++)
				{
					$xml .= '<Description Language="' . ilUtil::stripSlashes($meta["Description"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["Description"][$i]["Value"]) . '</Description>';
				}
				for ($i = 0; $i < count($meta["Language"]); $i++)
				{
					$xml .= '<Language Language="' . ilUtil::stripSlashes($meta["Language"][$i]["Language"]) . '"/>';
				}
				$xml .= '</Educational>';

				$update = true;
			}

			/* Rights */
			else if ($nodes[0]->node_name() == "Rights")
			{

				$xml = '<Rights Cost="' . ilUtil::stripSlashes($meta["Cost"]) . '" CopyrightAndOtherRestrictions="' . ilUtil::stripSlashes($meta["CopyrightAndOtherRestrictions"]) . '">';
				for ($i = 0; $i < count($meta["Description"]); $i++)
				{
					$xml .= '<Description Language="' . ilUtil::stripSlashes($meta["Description"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["Description"][$i]["Value"]) . '</Description>';
				}
				$xml .= '</Rights>';

				$update = true;
			}

			/* Relation */
			else if ($nodes[0]->node_name() == "Relation")
			{

#				for ($j = 0; $j < count($meta["Relation"]); $j++)
#				{
					$meta["Relation"][0] = $meta;
					$j = 0;
					$xml = '<Relation Kind="' . ilUtil::stripSlashes($meta["Relation"][$j]["Kind"]) . '">';
					$xml .= '<Resource>';
					for ($i = 0; $i < count($meta["Relation"][$j]["Resource"]["Identifier"]); $i++)
					{
						$xml .= '<Identifier_ Catalog="' . ilUtil::stripSlashes($meta["Relation"][$j]["Resource"]["Identifier"][$i]["Catalog"]) . '" Entry="' . str_replace("\"", "", ilUtil::stripSlashes($meta["Relation"][$j]["Resource"]["Identifier"][$i]["Entry"])) . '"/>';
					}
					for ($i = 0; $i < count($meta["Relation"][$j]["Resource"]["Description"]); $i++)
					{
						$xml .= '<Description Language="' . ilUtil::stripSlashes($meta["Relation"][$j]["Resource"]["Description"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["Relation"][$j]["Resource"]["Description"][$i]["Value"]) . '</Description>';
					}
					$xml .= '</Resource>';
					$xml .= '</Relation>';
#					echo htmlspecialchars($xml);
#				}

				$update = true;
			}

			/* Annotation */
			else if ($nodes[0]->node_name() == "Annotation")
			{

#				for ($i = 0; $i < count($meta["Annotation"]); $i++)
#				{
					$meta["Annotation"][0] = $meta;
					$i = 0;
					$xml = '<Annotation>';
					$xml .= '<Entity>' . ilUtil::stripSlashes($meta["Annotation"][$i]["Entity"]) . '</Entity>';
					$xml .= '<Date>' . ilUtil::stripSlashes($meta["Annotation"][$i]["Date"]) . '</Date>';
					$xml .= '<Description Language="' . ilUtil::stripSlashes($meta["Annotation"][$i]["Description"]["Language"]) . '">' . ilUtil::stripSlashes($meta["Annotation"][$i]["Description"]["Value"]) . '</Description>';
					$xml .= '</Annotation>';
#					echo htmlspecialchars($xml);
#				}

				$update = true;
			}

			/* Classification */
			else if ($nodes[0]->node_name() == "Classification")
			{

#				for ($j = 0; $j < count($meta["Classification"]); $j++)
#				{
					$meta["Classification"][0] = $meta;
					$j = 0;
					$xml = '<Classification Purpose="' . ilUtil::stripSlashes($meta["Classification"][$j]["Purpose"]) . '">';
					for ($k = 0; $k < count($meta["Classification"][$j]["TaxonPath"]); $k++)
					{
						$xml .= '<TaxonPath>';
						$xml .= '<Source Language="' . ilUtil::stripSlashes($meta["Classification"][$j]["TaxonPath"][$k]["Source"]["Language"]) . '">' . ilUtil::stripSlashes($meta["Classification"][$j]["TaxonPath"][$k]["Source"]["Value"]) . '</Source>';
						for ($i = 0; $i < count($meta["Classification"][$j]["TaxonPath"][$k]["Taxon"]); $i++)
						{
							$xml .= '<Taxon Language="' . ilUtil::stripSlashes($meta["Classification"][$j]["TaxonPath"][$k]["Taxon"][$i]["Language"]) . '" Id="' . str_replace("\"", "", ilUtil::stripSlashes($meta["Classification"][$j]["TaxonPath"][$k]["Taxon"][$i]["Id"])) . '">' . ilUtil::stripSlashes($meta["Classification"][$j]["TaxonPath"][$k]["Taxon"][$i]["Value"]) . '</Taxon>';
						}
						$xml .= '</TaxonPath>';
					}
					$xml .= '<Description Language="' . ilUtil::stripSlashes($meta["Classification"][$j]["Description"]["Language"]) . '">' . ilUtil::stripSlashes($meta["Classification"][$j]["Description"]["Value"]) . '</Description>';
					for ($i = 0; $i < count($meta["Classification"][$j]["Keyword"]); $i++)
					{
						$xml .= '<Keyword Language="' . ilUtil::stripSlashes($meta["Classification"][$j]["Keyword"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["Classification"][$j]["Keyword"][$i]["Value"]) . '</Keyword>';
					}
					$xml .= '</Classification>';
#					echo htmlspecialchars($xml);
#				}

				$update = true;
			}

			if ($update)
			{
				$nodes[0]->unlink_node();

				$xPath = "//MetaData";
				$this->addXMLNode($xPath, $xml);
#				echo htmlspecialchars($this->dom->dump_mem(0));
			}

			return true;
		}
		else
		{
			return false;
		}
	}	
	
	/**
	*	returns all contents of this node
	*/
	function getDomContent($xPath, $name = "", $index = 0) 
	{
#		echo "Index: " . $index . " | Path: " . $xPath . " | Name: " . $name . "<br>\n";
		$nodes = $this->getXpathNodes($this->dom, $xPath);
		if (count($nodes) > 0)
		{
			$children = $nodes[$index]->child_nodes();
			if (count($children) > 0)
			{
				$k = 0;
				for ($i = 0; $i < count($children); $i++)
				{
					if ($name == "" ||
						$children[$i]->tagname == $name)
					{
						$content[$k]["value"] = $children[$i]->get_content();
						$a = $children[$i]->attributes();
						for ($j = 0; $j < count($a); $j++)
						{
							$content[$k][$a[$j]->name] = $a[$j]->value;
						}
						$k++;
					}
				}
#				vd($content);
				return($content);
			}
		}
		return false;
	}	
	
	/**
	*	returns all contents of this node
	*/
	function updateDomContent($xPath, $name = "", $index = 0, $newNode) 
	{
#		echo "Index: " . $index . " | Path: " . $xPath . " | Name: " . $name . "<br>\n";
		$nodes = $this->getXpathNodes($this->dom, $xPath);
		if (count($nodes) > 0)
		{
			$children = $nodes[$index]->child_nodes();
			if (count($children) > 0)
			{
				for ($i = 0; $i < count($children); $i++)
				{
					if ($children[$i]->tagname == $name &&
						is_array($newNode))
					{
						foreach ($newNode as $key => $val)
						{
							if ($key == "value")
							{
								$children[$i]->set_content($val);
							}
							else
							{
								$children[$i]->set_attribute($key, $val);
							}
						}
					}
				}
			}
		}
	}	
	
	function getFirstDomNode($xPath) 
	{
		
		$node = $this->getXpathNodes($this->dom,$xPath);
		return($node[0]);
		
	}
	
	/**
	*	imports xml-data from dom new into nestedSet
	*/
	function updateFromDom()
	{

		$this->deleteAllDbData();

		$xml = $this->dom->dump_mem(0);

		$this->import($xml,$this->obj_id,$this->obj_type);
		
		// echo htmlspecialchars($xml);
	}
	
	/**
	*	deletes current db-data
	*/
	function deleteAllDbData() 
	{
		
		$res = $this->db->query("SELECT * FROM xmlnestedset WHERE ns_book_fk='".$this->obj_id."' AND ns_type='".$this->obj_type."' ");
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) 
		{
			
			$this->db->query("DELETE FROM xmlparam WHERE tag_fk='".$row["ns_tag_fk"]."' ");
			$this->db->query("DELETE FROM xmlvalue WHERE tag_fk='".$row["ns_tag_fk"]."' ");
			$this->db->query("DELETE FROM xmltags WHERE tag_pk='".$row["ns_tag_fk"]."' ");
			
		}
		$this->db->query("DELETE FROM xmlnestedset WHERE ns_book_fk='".$this->obj_id."' AND ns_type='".$this->obj_type."' ");
		
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
