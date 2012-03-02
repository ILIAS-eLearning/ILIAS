<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Xml/classes/class.ilXML2DOM.php";

/**
* Class NestedSetXML
* functions for storing XML-Data into nested-set-database-strcture
*
* @author Aresch Yavari <ay@databay.de>
* @author Jens Conze <jc@databay.de>
* @version $Id: class.ilNestedSetXML.php
*
* @access   public
*/
class ilNestedSetXML
{
    // {{{ Vars

    /**
    *   Datenbank-handle
    */
    var $db;

    /**
    *   Left and right edge tags
    */
    var $LEFT = 0;
    var $RIGHT = 0;
    
    /**
    *   Nesting level of the tags. 
    *   stored in database
    */ 
    var $DEPTH = 0;
    
    /**
    *   book-Obj-ID
    */
    var $obj_id;
    
    /**
    *   The type of the data to those this entry belongs.
    */
    var $obj_type;
    
    /**
    *   SAX-Parser-Handle
    */
    var $xml_parser;
    
    /**
    *   last Tag-Name found
    */
    var $lastTag = "";
    
    /**
	* ilias object
	* @var object ilias
	* @access public
	*/
    var $ilias;
	
    /**
	* dom-Object 
	* @var object dom
	* @access public
	*/
	var $dom;
		
	/**
	* unique id for import
	* @var string
	* @access private
	*/
	private $unique_import_id = '';
	
    // }}}

    /**
	* Constructor
	* initilize netsed-set variables 
	* @access	public
	*/
    function ilNestedSetXML() 
	{
        global $ilias,$ilDB;

		$this->ilias =& $ilias;
        
        $this->db =& $ilDB;
        $this->LEFT = 0;
        $this->RIGHT = 0;
        $this->DEPTH = 0;
		$this->unique_import_id = '';

		$this->param_modifier = "";
    }


    /**
    *   Method is called, at an introductory TAG
    *   @access private
    *
    *   @param  parser  parser      xml-parser-handle  
    *   @param  string  name        the tag-name
    *   @param  array   attrs       assoziativ-array of all attributes inside the tag
    *
    *   @return integer pk          Primary-Key of inserted xmltag          
    */
    function startElement($parser, $name, $attrs) 
	{
        // {{{
        
        $this->lastTag = $name;
        $this->LEFT += 1;
        $this->RIGHT = $this->LEFT + 1;
        $this->DEPTH++;
        
        /**
        *   Insert Tag-Name 
        */

        $nextId = $this->db->nextId('xmltags');
		$this->db->manipulateF('INSERT INTO xmltags ( tag_pk,tag_name,tag_depth ) VALUES (%s,%s,%s)',
		array('integer','text','integer'), array($nextId, $name, $this->DEPTH));
        
        $pk = $nextId;
        $this->db->manipulateF('
	        UPDATE xmlnestedsettmp SET ns_r = ns_r+2 
    	    WHERE ns_r >= %s  AND ns_book_fk = %s AND ns_unique_id = %s',
        array('integer','integer', 'text'), array($this->LEFT, $this->obj_id, $this->unique_import_id));

      $this->db->manipulateF('
	        INSERT INTO xmlnestedsettmp (ns_unique_id, ns_book_fk, ns_type, ns_tag_fk, ns_l, ns_r) VALUES (%s,%s,%s,%s,%s,%s)',
        array('text','integer','text','integer','integer','integer'), 
        array($this->unique_import_id, $this->obj_id, $this->obj_type, $pk, $this->LEFT, $this->RIGHT));
        
		$this->clean($attrs);
        if (is_array($attrs) && count($attrs)>0)
		{
            reset ($attrs);
            while (list ($key, $val) = each ($attrs)) 
			{
                  $this->db->manipulateF('INSERT INTO xmlparam ( tag_fk,param_name,param_value ) VALUES (%s,%s,%s)',
                  array('integer','text','text'),
                  array($pk,$key,addslashes($val)));
            }
        }
        
        return($pk);
        // }}}
    }

    /**
    *   Method to insert tag-content
    *
    *   @access private
    *
    *   @param  parser  parser  xml-parser-handle
    *   @param  string  data    text-content between opening and closing tag
    */
    function characterData($parser, $data) 
	{
        // {{{
        /**
        *   primary-key of last-content-block
        *   @var    integer    value_pk    Primary-Key of last Content-Blocks
        *   @access private
        */
        static $value_pk;
        
		// we don't need this trim since expression like ' ABC < > ' will be parsed to ' ABC <>'
        if(1 or trim($data)!="") {


            if ($this->lastTag == "TAGVALUE")
			{
                $this->db->manipulateF('UPDATE xmlvalue SET tag_value = %s WHERE tag_value_pk = %s',
                array('text','integer'), array(concat(tag_value,addslashes($data)),$value_pk));
            } 
            else 
            {
                $tag_pk = $this->startElement($this->xml_parser,"TAGVALUE",array());
                $this->endElement($this->xml_parser,"TAGVALUE");
            
                $nextId = $this->db->nextId('xmlvalue');
                
                
                $this->db->manipulateF('INSERT INTO xmlvalue (tag_value_pk, tag_fk, tag_value) VALUES (%s,%s,%s)',
                array('integer','integer','text'), array($nextId,$tag_pk,addslashes($data)));

				$value_pk = $nextId;

                $this->lastTag = "TAGVALUE";
            }
            
        }
        // }}}
    }

    /**
    *   method called at a closing tag
    *   @access private
    *
    *   @param  parser  parser  xml-parser-handle
    *   @param  string  name    name of the closing tag
    */
    function endElement($parser, $name)
	{
        // {{{
        $this->DEPTH--;
        $this->LEFT += 1;
        $this->lastTag = "";
        // }}}
    }

    /**
    *   Import-Function.
    *   @param  String  xmldata     xml-structure
    *   @param  int     obj_id      book-ID
    *   @param  string  obj_type    Object-Type
    *
	*   @access	public
    */
    function import($xmldata, $obj_id, $obj_type)
	{
		global $ilUser;
		
        // {{{
        /**
        *   drop temporary table
        */		

        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;
		$this->DEPTH = 0;
		$this->LEFT = 0;
		$this->RIGHT = 0;
		$this->unique_import_id = $ilUser->getId();
		
		$this->db->manipulateF(
			"DELETE FROM xmlnestedsettmp WHERE ns_unique_id = %s",
			array('text'),
			array($this->unique_import_id)
		);    

        /**
        *   initialize XML-Parser
        */

        $this->xml_parser = xml_parser_create("UTF-8");
        xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_object($this->xml_parser,$this);
        xml_set_element_handler($this->xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($this->xml_parser, "characterData");

        if (!xml_parse($this->xml_parser, $xmldata)) {
            die(sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($this->xml_parser)),xml_get_current_line_number($this->xml_parser)));
        }
        xml_parser_free($this->xml_parser);

        /**
        *   transfer nested-set-structure ito table and drop temp-Table
        */
        $this->deleteAllDbData();

        $this->db->manipulateF(
			"INSERT INTO xmlnestedset (SELECT ns_book_fk, ns_type, ns_tag_fk, ns_l, ns_r FROM xmlnestedsettmp WHERE ns_unique_id = %s)",
			array('text'),
			array($this->unique_import_id)
		);
		
       $this->db->manipulateF(
			"DELETE FROM xmlnestedsettmp WHERE ns_unique_id = %s",
			array('text'),
			array($this->unique_import_id)
		); 
        // }}}
    }

    /**
    *
    *   @param  obj     a_object
    *   @param  String  a_method    Function-Name
    */
	function setParameterModifier(&$a_object, $a_method)
	{
		$this->param_modifier =& $a_object;
		$this->param_modifier_method = $a_method;
	}

    /**
    *   Export-Function.
    *   creates xml out of nested-set-structure   
    *
    *   @param  int     obj_id  book-id
    *   @param  string  type    Object-Type of XML-Struktur
    *
    *   @return String  xml-Structur
    
	*   @access    public
    */
	function export($obj_id, $type)
	{
		// {{{

		$result = $this->db->queryF('
			SELECT * FROM xmlnestedset,xmltags 
			WHERE ns_tag_fk = tag_pk 
			AND ns_book_fk = %s
			AND ns_type = %s 
			ORDER BY ns_l',
		array('integer','text'),
		array($obj_id,$type));
		
		if (ilDB::isDbError($result))
		{
			die($this->className."::checkTable(): ".$result->getMessage().":<br>".$q);
		}

		$xml = "";
		$lastDepth = -1;

		while (is_array($row = $this->db->fetchAssoc($result)))
        {

			// {{{ tags
			$Anfang = "<".$row[tag_name];

			$result_param = $this->db->queryF('SELECT * FROM xmlparam WHERE tag_fk = %s',array('integer'),array($row[tag_pk]));
			while (is_array($row_param = $this->db->fetchAssoc($result_param)))
			{
				$param_value = $row_param[param_value];
				if (is_object($this->param_modifier))
				{
					$obj =& $this->param_modifier;
					$method = $this->param_modifier_method;
					$param_value = $obj->$method($row[tag_name], $row_param[param_name], $param_value);
				}
				$Anfang .= " ".$row_param[param_name]."=\"".$param_value."\"";
			}

			$Anfang .= ">";
			$Ende = "</".$row[tag_name].">";
			// }}}

			// {{{ TagValue
			if ($row[tag_name]=="TAGVALUE") 
            {
				$result_value = $this->db->queryF('SELECT * FROM xmlvalue WHERE tag_fk = %s', array('integer'),array($row[tag_pk]));
				$row_value = $this->db->fetchAssoc($result_value); 
				
				$Anfang = $row_value["tag_value"];
				$Ende = "";

				$Anfang = htmlspecialchars($Anfang);
				// $Anfang = utf8_encode($Anfang);
			}
			// }}}

			$D = $row[tag_depth];

			if ($D==$lastDepth) 
            {
				$xml .= $xmlE[$D];
				$xml .= $Anfang;
				$xmlE[$D] = $Ende;
			} 
            else if ($D>$lastDepth)
            {
				$xml .= $Anfang;
				$xmlE[$D] = $Ende;
			} 
            else 
            {
				for ($i=$lastDepth;$i>=$D;$i--) 
                {
					$xml .= $xmlE[$i];
				}
				$xml .= $Anfang;
				$xmlE[$D] = $Ende;
			}

			$lastDepth = $D;

		}

		for ($i=$lastDepth;$i>0;$i--) 
        {
			$xml .= $xmlE[$i];
		}

		return($xml);
		// }}}
	}

    /**
    *   initilialize Nested-Set-Structur
    *
    *   @param  integer     obj_id      object-id
    *   @param  string      obj_type    type of object
	*   @access	public
    */
    function init($obj_id,$obj_type)
	{
        // {{{
		$this->db->setLimit(1);

		$result = $this->db->queryF('
			SELECT * FROM xmlnestedset,xmltags 
			WHERE ns_book_fk = %s
			AND ns_type = %s
			AND ns_tag_fk = tag_pk 
			ORDER BY ns_l',
		array('integer','text'),
		array($obj_id, $obj_type));
		        
        $row = $this->db->fetchAssoc($result); 

        $this->LEFT = $row["ns_l"];
        $this->RIGHT = $row["ns_r"];
        $this->DEPTH = $row["tag_depth"];
        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;
        // }}}
    }

    /**
    *   find first tag-name
    *
    *   @return string tagname
    *
    *   @access public
    */
    function getTagName()
	{

		$this->db->setLimit(1);

		$result = $this->db->queryF('
			SELECT * FROM xmlnestedset,xmltags 
			WHERE ns_book_fk = %s
			AND ns_type = %s 
 			AND ns_l = %s 
 			AND ns_r = %s 
 			AND ns_tag_fk = tag_pk',
		array('integer','text','integer','integer'),
		array($this->obj_id,$this->obj_type,$this->LEFT,$this->RIGHT));
		
		$row = $this->db->fetchAssoc($result);
		
        return($row["tag_name"]);
        
    }

    /**
    *   set tag-name
    *
    *   @param  string  tagName name of tag to be changed
    *
    *   @return string  old tagname
    *
        @access public
    */
    function setTagName($tagName)
	{
        
		$this->db->setLimit(1);

        $result = $this->db->queryF('
        	SELECT * FROM xmlnestedset 
        	WHERE ns_book_fk = %s 
        	AND ns_type = %s 
        	AND ns_l = %s 
        	AND ns_r = %s', 
		array('integer','text','integer','integer'),
        array($this->obj_id,$this->obj_type,$this->LEFT,$this->RIGHT));
        
		$row = $this->db->fetchAssoc($result);
        
		$this->db->manipulateF('UPDATE xmltags SET tag_name = %s WHERE tag_pk = %s',
		array('text','integer'), array($tagName,$row["ns_tag_fk"]));
		
		
        return($row["tagName"]);
        
    }
    
    
    /**
    *   get tag content
    *
    *   @return     array   Content or sub-tags inbetween $this->LEFT and $this->RIGHT.
  	*   @access	public
    */
    function getTagValue() 
	{
        
        $V = array();
        
		$result = $this->db->queryF('
        	SELECT * FROM xmlnestedset,xmltags 
        	WHERE ns_tag_fk = tag_pk 
        	AND ns_book_fk = %s
        	AND ns_type = %s
        	AND ns_l >= %s
        	AND ns_r <= %s
        	AND tag_depth = %s
        	ORDER BY ns_l',
		array('integer','text','integer','integer','integer'),
		array($this->obj_id,$this->obj_type,$this->LEFT,$this->RIGHT,$this->DEPTH+1));

        while (is_array($row = $this->db->fetchAssoc($result) ) )
		{
            if ($row[tag_name]=="TAGVALUE") 
			{
                $result2 = $this->db->queryF('SELECT * FROM xmlvalue WHERE tag_fk = %s', array('integer'),array($row[tag_pk]));
                $row2 = $this->db->fetchAssoc($result2);
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
	
    /**
    *   set tag-content
    *
    *   @param      string  value
	*   @access    public
    */
	function setTagValue($value) 
	{
        $V = array();

          
		$result = $this->db->queryF('
			SELECT * FROM xmlnestedset,xmltags
			LEFT JOIN xmlvalue ON xmltags.tag_pk = xmlvalue.tag_fk
			WHERE ns_tag_fk = tag_pk
			AND ns_book_fk = %s
			AND ns_type = %s
			AND ns_l >= %s
			AND ns_r <= %s
			AND tag_depth = %s
			AND tag_name = %s
			AND ORDER BY ns_l',
		array('integer','text','integer','integer','integer','text'),
		array($this->obj_id, $this->obj_type, $this->LEFT, $this->RIGHT, $this->DEPTH+1,'TAGVALUE')
	);

        if (is_array($row = $this->db->fetchAssoc($result) ) )
		{
			$this->db->manipulateF('UPDATE xmlvalue SET tag_value = %s WHERE tag_value_pk = %s',
			array('text','integer'), array($value, $row["tag_value_pk"]));
			
		} 
        else 
        {
			
			/**
			*	add new
			*/
			
		}
	}

    /**
    *   get node in dom-structure
    *
    *   @param  object  doc
    *   @param  string  qry     path to node
    *
    *   @return object  nodeset
	*   @access    public
    */
	function getXpathNodes(&$doc, $qry)
	{
		if (is_object($doc))
		{
			$xpath = $doc->xpath_init();
			$ctx = $doc->xpath_new_context();
//echo "<br><b>ilNestedSetXML::getXpathNodes</b>";
			$result = $ctx->xpath_eval($qry);
			if (is_array($result->nodeset))
			{
				return($result->nodeset);
			}
		}
		return Null;
	}

	/**
	*	inits dom-object from given xml-content
    *
    *   @return boolean
	*   @access    public
	*/
	function initDom()
	{
		$xml = $this->export($this->obj_id, $this->obj_type);

/*
        for testing
		$xml_test = '
		<MetaData>
			<General Structure="Atomic">
				<Identifier Catalog="ILIAS" Entry="34">Identifier 34 in ILIAS</Identifier>
				<Identifier Catalog="ILIAS" Entry="45">Identifier 45 in ILIAS</Identifier>
				<Identifier Catalog="ILIAS" Entry="67">Identifier 67 in ILIAS</Identifier>
			</General>
		</MetaData>
		';

		$xml = $xml_test;
*/

		if ($xml=="")
        {
			return(false);
		}
        else 
        {
			$this->dom = domxml_open_mem($xml);
			return(true);
		}
	}

	/**
	*	parse XML code and add it to a given DOM object as a new node
    *
    *   @param  string  xPath   path
    *   @param  string  xml     xml to add
    *   @param  integer index   index to add
    *
    *   @return boolean
	*   @access    public
	*/
	function addXMLNode($xPath, $xml, $index = 0)
	{
		$newDOM = new XML2DOM($xml);
//echo "<br>addXMLNode:-".htmlspecialchars($this->dom->dump_mem(0));
		$nodes = $this->getXpathNodes($this->dom, $xPath);

		if (count($nodes) > 0)
		{
			$newDOM->insertNode($this->dom, $nodes[$index]);
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*	returns first content of this node
    *
    *   @param  string  xPath   path
    *
    *   @return string  content of node
	*   @access    public
	*/
	function getFirstDomContent($xPath)
	{
//echo "<br>ilNestedSetXML::getFirstDomContent-start-$xPath-"; flush();
		$content = "";
		if (is_object($this->dom))
		{
			$node = $this->getXpathNodes($this->dom,$xPath);
			if (is_array($node))
			{
				if (is_object($node[0]))
				{
					$c = $node[0]->children();
					//$content = $c[0]->content;		// ## changed
					if (is_object($c[0]))
					{
						$content = $c[0]->get_content();		// ## changed
					}
				}
			}
		}
//echo "<br>ilNestedSetXML::getFirstDomContent-stop-$content-"; flush();
		return($content);
	}	
	
	/**
	*	deletes node
    *
    *   @param  string  xPath   path
    *   @param  string  name    name
    *   @param  integer index   index
    *
    *   @return boolean
	*   @access    public
	*/
	function deleteDomNode($xPath, $name, $index = 0) 
	{
		if ($index == "")
		{
			$index = 0;
		}
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
						if ($children[$i]->node_name() == $name)
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
	*	adds node to DOM-Structure
    *
    *   @param  string  xPath
    *   @param  string  name    
    *   @param  string  value
    *   @param  array   attributes
    *   @param  integer index
    *
    *   @return boolean
	*   @access    public
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
	
	function clean(&$meta)
	{
		if(is_array($meta))
		{
			foreach($meta as $key => $value)
			{
				if(is_array($meta[$key]))
				{
					$this->clean($meta[$key]);
				}
				else
				{
					$meta[$key] = ilUtil::stripSlashes($meta[$key]);
					$meta[$key] = preg_replace("/&(?!amp;|lt;|gt;|quot;)/","&amp;",$meta[$key]);
					$meta[$key] = preg_replace("/\"/","&quot;",$meta[$key]);
					$meta[$key] = preg_replace("/</","&lt;",$meta[$key]);
					$meta[$key] = preg_replace("/>/","&gt;",$meta[$key]);
				}
			}
		}
		return true;
	}
	/**
	*	updates dom node
    *
    *   @param  string  xPath
    *   @param  string  meta
    *   @param  integer no
	*   @access    public
	*/
	function updateDomNode($xPath, $meta, $no = 0)
	{
		$this->clean($meta);
		$update = false;
		if ($xPath == "//Bibliography")
		{
			$nodes = $this->getXpathNodes($this->dom, $xPath . "/BibItem[" . ($no+1) . "]");
		}
		else
		{
			$nodes = $this->getXpathNodes($this->dom, $xPath);
		}
		if (count($nodes) > 0)
		{

			/* BibItem */
			if ($nodes[0]->node_name() == "BibItem")
			{
				$xml = '<BibItem Type="' . ilUtil::stripSlashes($meta["Type"]) . '" Label="' . ilUtil::stripSlashes($meta["Label"]["Value"]) . '">';
				$xml .= '<Identifier Catalog="' . ilUtil::stripSlashes($meta["Identifier"]["Catalog"]) . '" Entry="' .  str_replace("\"", "", ilUtil::stripSlashes($meta["Identifier"]["Entry"])) . '"/>';
				for ($i = 0; $i < count($meta["Language"]); $i++)
				{
					$xml .= '<Language Language="' . ilUtil::stripSlashes($meta["Language"][$i]["Language"]) . '"/>';
				}
				for ($i = 0; $i < count($meta["Author"]); $i++)
				{
					$xml .= '<Author>';
#					for ($j = 0; $j < count($meta["Author"][$i]["FirstName"]); $j++)
#					{
						$xml .= '<FirstName>' . ilUtil::stripSlashes($meta["Author"][$i]["FirstName"]) . '</FirstName>';
#					}
#					for ($j = 0; $j < count($meta["Author"][$i]["MiddleName"]); $j++)
#					{
						$xml .= '<MiddleName>' . ilUtil::stripSlashes($meta["Author"][$i]["MiddleName"]) . '</MiddleName>';
#					}
#					for ($j = 0; $j < count($meta["Author"][$i]["LastName"]); $j++)
#					{
						$xml .= '<LastName>' . ilUtil::stripSlashes($meta["Author"][$i]["LastName"]) . '</LastName>';
#					}
					$xml .= '</Author>';
				}
				$xml .= '<Booktitle Language="' . ilUtil::stripSlashes($meta["Booktitle"]["Language"]) . '">' . ilUtil::stripSlashes($meta["Booktitle"]["Value"]) . '</Booktitle>';
				for ($i = 0; $i < count($meta["CrossRef"]); $i++)
				{
					$xml .= '<CrossRef>' . ilUtil::stripSlashes($meta["CrossRef"][$i]["Value"]) . '</CrossRef>';
				}
				$xml .= '<Edition>' . ilUtil::stripSlashes($meta["Edition"]["Value"]) . '</Edition>';
				for ($i = 0; $i < count($meta["Editor"]); $i++)
				{
					$xml .= '<Editor>' . ilUtil::stripSlashes($meta["Editor"][$i]["Value"]) . '</Editor>';
				}
				$xml .= '<HowPublished Type="' . ilUtil::stripSlashes($meta["HowPublished"]["Type"]) . '"/>';
				for ($i = 0; $i < count($meta["WherePublished"]); $i++)
				{
					$xml .= '<WherePublished>' . ilUtil::stripSlashes($meta["WherePublished"][$i]["Value"]) . '</WherePublished>';
				}
				for ($i = 0; $i < count($meta["Institution"]); $i++)
				{
					$xml .= '<Institution>' . ilUtil::stripSlashes($meta["Institution"][$i]["Value"]) . '</Institution>';
				}
				if (is_array($meta["Journal"]))
				{
					$xml .= '<Journal Note="' . ilUtil::stripSlashes($meta["Journal"]["Note"]) . '" Number="' . ilUtil::stripSlashes($meta["Journal"]["Number"]) . '" Organization="' . ilUtil::stripSlashes($meta["Journal"]["Organization"]) . '"/>';
				}
				for ($i = 0; $i < count($meta["Keyword"]); $i++)
				{
					$xml .= '<Keyword Language="' . ilUtil::stripSlashes($meta["Keyword"][$i]["Language"]) . '">' . ilUtil::stripSlashes($meta["Keyword"][$i]["Value"]) . '</Keyword>';
				}
				if (is_array($meta["Month"]))
				{
					$xml .= '<Month>' . ilUtil::stripSlashes($meta["Month"]["Value"]) . '</Month>';
				}
				if (is_array($meta["Pages"]))
				{
					$xml .= '<Pages>' . ilUtil::stripSlashes($meta["Pages"]["Value"]) . '</Pages>';
				}
				$xml .= '<Publisher>' . ilUtil::stripSlashes($meta["Publisher"]["Value"]) . '</Publisher>';
				for ($i = 0; $i < count($meta["School"]); $i++)
				{
					$xml .= '<School>' . ilUtil::stripSlashes($meta["School"][$i]["Value"]) . '</School>';
				}
				if (is_array($meta["Series"]))
				{
					$xml .= '<Series>';
					$xml .= '<SeriesTitle>' . ilUtil::stripSlashes($meta["Series"]["SeriesTitle"]) . '</SeriesTitle>';
#					for ($i = 0; $i < count($meta["Series"]["SeriesEditor"]); $i++)
					if (isset($meta["Series"]["SeriesEditor"]))
					{
#						$xml .= '<SeriesEditor>' . ilUtil::stripSlashes($meta["Series"]["SeriesEditor"][$i]) . '</SeriesEditor>';
						$xml .= '<SeriesEditor>' . ilUtil::stripSlashes($meta["Series"]["SeriesEditor"]) . '</SeriesEditor>';
					}
					if (isset($meta["Series"]["SeriesVolume"]))
					{
						$xml .= '<SeriesVolume>' . ilUtil::stripSlashes($meta["Series"]["SeriesVolume"]) . '</SeriesVolume>';
					}
					$xml .= '</Series>';
				}
				$xml .= '<Year>' . ilUtil::stripSlashes($meta["Year"]["Value"]) . '</Year>';
				if ($meta["URL_ISBN_ISSN"]["Type"] == "URL")
				{
					$xml .= '<URL>' . ilUtil::stripSlashes($meta["URL_ISBN_ISSN"]["Value"]) . '</URL>';
				}
				else if ($meta["URL_ISBN_ISSN"]["Type"] == "ISBN")
				{
					$xml .= '<ISBN>' . ilUtil::stripSlashes($meta["URL_ISBN_ISSN"]["Value"]) . '</ISBN>';
				}
				else if ($meta["URL_ISBN_ISSN"]["Type"] == "ISSN")
				{
					$xml .= '<ISSN>' . ilUtil::stripSlashes($meta["URL_ISBN_ISSN"]["Value"]) . '</ISSN>';
				}
				$xml .= '</BibItem>';
#				echo htmlspecialchars($xml);

				$update = true;
			}

			/* General */
			else if ($nodes[0]->node_name() == "General")
			{

				$xml = '<General Structure="' . ilUtil::stripSlashes($meta["Structure"]) . '">';
				for ($i = 0; $i < count($meta["Identifier"]); $i++)
				{
					$xml .= '<Identifier Catalog="' . ilUtil::stripSlashes($meta["Identifier"][$i]["Catalog"]) . '" Entry="' .  
						str_replace("\"", "", ilUtil::stripSlashes($meta["Identifier"][$i]["Entry"])) . '"/>';
				}

				$xml .= '<Title Language="' . 
					ilUtil::stripSlashes($meta["Title"]["Language"]) . '">' . 
					ilUtil::stripSlashes($meta["Title"]["Value"]) . '</Title>';
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
//echo "<br><br>".htmlspecialchars($xml);

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

				if ($xPath != "//Bibliography")
				{
					$xPath = "//MetaData";
				}
//echo "<br><br>savedA:".htmlspecialchars($this->dom->dump_mem(0));
				$this->addXMLNode($xPath, $xml);
//echo "<br><br>savedB:".htmlspecialchars($this->dom->dump_mem(0));
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
    *
    *   @param  string  xPath
    *   @param  string  name
    *   @param  integer index
    *
    *   @return string  content
	*   @access    public
	*/
	function getDomContent($xPath, $name = "", $index = 0)
	{
		if ($index == "")
		{
			$index = 0;
		}
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
//echo "<br>ilNestedSetXML::getDomContent-".$children[$i]->node_name()."-".$name;
					if ($name == "" ||
						$children[$i]->node_name() == $name)
					{
						$content[$k]["value"] = $children[$i]->get_content();
						$a = $children[$i]->attributes();
						for ($j = 0; $j < count($a); $j++)
						{
							$content[$k][$a[$j]->name()] = $a[$j]->value();
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
	*	updates content of this node
    *   @param  string  xPath
    *   @param  string  name
    *   @param  integer index
    *   @param  array   newNode
	*   @access    public
	*/
	function replaceDomContent($xPath, $name = "", $index = 0, $newNode) 
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
					if ($children[$i]->node_name() == $name &&
						is_array($newNode))
					{
						foreach ($newNode as $key => $val)
						{
							if ($key == "value")
							{
								$this->replace_content($children[$i], $val);
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
	
/**
 * Replace node contents
 *
 * Needed as a workaround for bug/feature of set_content
 * This version puts the content
 * as the first child of the new node.
 * If you need it somewhere else, simply
 * move $newnode->set_content() where
 * you want it.
 */
	function replace_content( &$node, &$new_content )
	{
		$newnode =& $this->dom->create_element( $node->tagname() );
		$newnode->set_content( $new_content );
		$atts =& $node->attributes();
		foreach ( $atts as $att )
		{
			$newnode->set_attribute( $att->name(), $att->value() );
		}
		$kids =& $node->child_nodes();
		foreach ( $kids as $kid )
		{
			if ( $kid->node_type() != XML_TEXT_NODE )
			{
				$newnode->append_child( $kid );
			}
		}
		$node->replace_node( $newnode );
	}

	/**
	*	updates content of this node
    *   @param  string  xPath
    *   @param  string  name
    *   @param  integer index
    *   @param  array   newNode
	*   @access    public
	*/
	function updateDomContent($xPath, $name = "", $index = 0, $newNode) 
	{
//		echo "Index: " . $index . " | Path: " . $xPath . " | Name: " . $name . "<br>\n";
		$nodes = $this->getXpathNodes($this->dom, $xPath);
		if (count($nodes) > 0)
		{
			$children = $nodes[$index]->child_nodes();
			if (count($children) > 0)
			{
				for ($i = 0; $i < count($children); $i++)
				{
					if ($children[$i]->node_name() == $name &&
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

    /**
    *   first dom-node
    *
    *   @param  string  xPath   path
    *   @return object  node    first node
	*   @access    public
    */
	function getFirstDomNode($xPath)
	{
		$node = $this->getXpathNodes($this->dom,$xPath);
		return($node[0]);
	}

	/**
	*	imports new xml-data from dom into nested set
	*   @access    public
	*/
	function updateFromDom()
	{
		$this->deleteAllDbData();
		$xml = $this->dom->dump_mem(0);
		$this->import($xml,$this->obj_id,$this->obj_type);

	}

	/**
	*	deletes current db-data of $this->obj_id and $this->obj_type
	*   @access    private
	*/
	function deleteAllDbData()
	{
		global $ilBench;

		#$ilBench->start('NestedSet','deleteAllDBData');
		$res = $this->db->queryF('
			SELECT * FROM xmlnestedset WHERE ns_book_fk = %s AND ns_type = %s ',
		array('integer','text'), array($this->obj_id,$this->obj_type));
		
		while ($row = $this->db->fetchAssoc($res))
		{
			$this->db->manipulateF('DELETE FROM xmlparam WHERE tag_fk = %s',array('integer'), array($row["ns_tag_fk"]));
			$this->db->manipulateF('DELETE FROM xmlvalue WHERE tag_fk = %s',array('integer'), array($row["ns_tag_fk"]));			
			$this->db->manipulateF('DELETE FROM xmltags WHERE tag_pk = %s',array('integer'), array($row["ns_tag_fk"]));				
		}
		$this->db->manipulateF('DELETE FROM xmlnestedset WHERE ns_book_fk = %s  AND ns_type = %s',
		array('integer','text'), array($this->obj_id,$this->obj_type));				
		#$ilBench->stop('NestedSet','deleteAllDBData');

	}

	/**
	*	Delete meta data of a content object (pages, chapters)
	*   @access    public
	*	@param array of child ids
	*	@see _getAllChildIds()
	*	@return boolean
	*/
	function _deleteAllChildMetaData($a_ids)
	{
		global $ilBench,$ilDB;

		#$ilBench->start('NestedSet','deleteAllChildMetaData');

		// STEP TWO: DELETE ENTRIES IN xmlnestedset GET ALL tag_fks
		$in = " IN ('";
		$in .= implode("','", $a_ids);
		$in .= "')";

		$query = "SELECT ns_tag_fk FROM xmlnestedset ".
			"WHERE ns_book_fk ".$in;
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$tag_fks[$row->ns_tag_fk] = $row->ns_tag_fk;
		}
		$ilDB->manipulate("DELETE FROM xmlnestedset WHERE ns_book_fk ".$in);


		// FINALLY DELETE
		// BEGIN WebDAV: Object deletion failed if no tag_fks was present.
		if ($tag_fks != null)
		{
			$in = " IN ('";
			$in .= implode("','", $tag_fks);
			$in .= "')";

			$ilDB->manipulate("DELETE FROM xmlparam WHERE tag_fk ".$in);
			$ilDB->manipulate("DELETE FROM xmlvalue WHERE tag_fk ".$in);
			$ilDB->manipulate("DELETE FROM xmltags  WHERE tag_pk ".$in);
		}
		// END WebDAV Object deletion failed if no tag_fks was present.

		#$ilBench->stop('NestedSet','deleteAllChildMetaData');
		return true;
	} 

	/**
	*	Get all child ids of a content object
	*   @access    public
	*	@param int obj_id
	*	@return boolean
	*/
	function _getAllChildIds($a_obj_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT obj_id FROM lm_data WHERE lm_id = %s',array('integer'),array($a_obj_id));
		while($row = $ilDB->fetchObject($res))
		{
			$ids[$row->obj_id] = $row->obj_id;
		}
		$ids[$a_obj_id] = $a_obj_id;

		return $ids ? $ids : array();
	}
	
    // }}}
    
}

?>
