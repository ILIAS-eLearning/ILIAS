<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");

/**
* Utility class for wysiwyg editing mode
*
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilWysiwygUtil
{
    
	var $tpl;
	var $lng;
	
	function ilWysiwygUtil() 
	{
		global $lng;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("content");
	}
	
	function show($ptype) 
	{
		$this->showHeader();
		switch ($ptype) {
			case "xtl" : {
							$this->showXtl();
							break;
						}
			case "itl" : {
							$this->showItl();
							break;
						}
			case "footnote" : {
							$this->showFootnote();
							break;
						}
			case "movecopytreenode" : {
							$this->showMoveCopyQuestion();
							break;
						}
		}
		
		$this->tpl->show();
		
	}
	
	function showMoveCopyQuestion() {
		
		$tempobj = ilObjectFactory::getInstanceByRefId($_GET["ref_id"]);
		$source_obj = ilLMObjectFactory::getInstance($tempobj, $_GET["sourceId"], true);
		$source_obj->setLMId($tempobj->getId());
		$target_obj = ilLMObjectFactory::getInstance($tempobj, $_GET["targetId"], true);
		$target_obj->setLMId($tempobj->getId());
		
		//vd($source_obj->getType());
		//vd($target_obj->getType());
		
		$this->tpl = new ilTemplate("tpl.wysiwyg_popup_movecopyquestion.html",false,true,"Services/COPage");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation() );
		
		if ($source_obj->getType() == "st" && $target_obj->getType() == "pg") {
			$this->tpl->setVariable("TXT_ST_ON_PG",$this->lng->txt("cont_st_on_pg"));
			$this->tpl->setVariable("BTN_CLOSE2", $this->lng->txt("close"));
		} else {
		
			if (($source_obj->getType() == "pg" && $target_obj->getType() == "pg") || ($source_obj->getType() == "st" && $target_obj->getType() == "st")) {
				$this->tpl->setVariable("TXT_SET_AFTER", $this->lng->txt("cont_set_after"));
				$this->tpl->setVariable("TXT_SET_BEFORE", $this->lng->txt("cont_set_before"));
			}
			if ($source_obj->getType() == "st" && $target_obj->getType() == "st") {
				$this->tpl->setVariable("TXT_SET_INTO", $this->lng->txt("cont_set_into"));
			}
			
			$this->tpl->setVariable("TXT_MOVE_OBJECT", $this->lng->txt("cont_move_object"));
			$this->tpl->setVariable("TXT_COPY_OBJECT", $this->lng->txt("cont_copy_object"));
			
			$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("save"));
			$this->tpl->setVariable("BTN_CLOSE", $this->lng->txt("close"));
		}
	}
	
	function showXtl() 
	{
		$this->tpl = new ilTemplate("tpl.wysiwyg_popup_xtl.html",false,false,"Services/COPage");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation() );
		
		
		$this->tpl->setVariable("TXT_EXTERNAL_URL", $this->lng->txt("cont_external_url"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("cont_title"));
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
		$this->tpl->setVariable("BTN_CLOSE", $this->lng->txt("close"));
		
		$this->tpl->setVariable("TXT_OR", $this->lng->txt("cont_or"));
		$this->tpl->setVariable("TXT_EXAMPLE", $this->lng->txt("cont_e_g_example"));
		
		$this->tpl->setVariable("DELETE_QUESTION", $this->lng->txt("cont_confirm_delete"));
	}
	
	function showItl() 
	{
		$this->tpl = new ilTemplate("tpl.wysiwyg_popup_itl.html",false,false,"Services/COPage");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation() );
	}
	
	function showFootnote() 
	{
		global $ilCtrl;
		
		$this->tpl = new ilTemplate("tpl.wysiwyg_popup_footnote.html",false,false,"Services/COPage");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation() );
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET_HTMLAREA", ILIAS_HTTP_PATH."/Services/COPage/css/content.css" );
		$this->tpl->setVariable("REF_ID", $_GET["ref_id"]);
		$ilCtrl->setParameterByClass("ilpcparagraphgui", "ptype", "footnote");
		$this->tpl->setVariable("FOOTNOTE_TARGET",
			$ilCtrl->getLinkTargetByClass("ilpcparagraphgui", "popup"));

		
		$this->tpl->setVariable("TXT_FOOTNOTES", $this->lng->txt("cont_title_footnotes"));
		$this->tpl->setVariable("TXT_INSERT_NEW_FOOTNOTES", $this->lng->txt("cont_insert_new_footnote"));
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		
		$this->tpl->setVariable("DELETE_QUESTION", $this->lng->txt("cont_confirm_delete"));
		$this->tpl->setVariable("BTN_DELETE", $this->lng->txt("delete"));
		
	}
	
	
	
	function showHeader() 
	{
		
	}
	
	
    var $struct = array();
    var $depth;
    var $newXml;
    function convertFromPost($content) 
	{
        
		
		$content = str_replace("&nbsp;"," ",$content);
		//vd(htmlspecialchars($content));
		
		$content = str_replace(" </span>","</span> ",$content);
		$content = str_replace("<span class=\"ilc_Strong\"></span>","",$content);
		$content = str_replace("<span class=\"ilc_Emph\"></span>","",$content);
		$content = str_replace("<span class=\"ilc_Quotation\"></span>","",$content);
		$content = str_replace("<span class=\"ilc_Comment\"></span>","",$content);
		
		$content = rawurlencode($content);
		$content = str_replace("%3Cspan%20class%3D%22ilc_Strong%22%3E%20%3C%2Fspan%3E","%20",$content);
		$content = str_replace("%3Cspan%20class%3D%22ilc_Emph%22%3E%20%3C%2Fspan%3E","%20",$content);
		$content = str_replace("%3Cspan%20class%3D%22ilc_Quotation%22%3E%20%3C%2Fspan%3E","%20",$content);
		$content = str_replace("%3Cspan%20class%3D%22ilc_Comment%22%3E%20%3C%2Fspan%3E","%20",$content);
		$content = rawurldecode($content);
		

		//echo(htmlspecialchars($content));
//		echo "<p>";
//		echo(rawurlencode($content));

		$content = str_replace("&","&amp;",$content);
		
		for($k=0;$k<2;$k++) {
			$xml_parser = xml_parser_create("UTF-8");
			xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);
			xml_set_object($xml_parser,$this);
			xml_set_element_handler($xml_parser, "startElement", "endElement");
			xml_set_character_data_handler($xml_parser, "characterData");
	
			$xml_data = "<xml>".$content."</xml>";
			
			$this->depth = 0;
			$this->struct = array();
			$this->newXml = "";
			
			if (!xml_parse($xml_parser, $xml_data)) 
			{
				die(sprintf("XML error: %s at line %d",	xml_error_string(xml_get_error_code($xml_parser)),xml_get_current_line_number($xml_parser)));
			}
			xml_parser_free($xml_parser);
			
			$this->newXml = str_replace("<xml>","",$this->newXml);
			$this->newXml = str_replace("</xml>","",$this->newXml);
			
			$content = $this->newXml;

			
			//echo htmlspecialchars($content)."<p>";
		}        
		//echo htmlspecialchars($this->newXml);
		//exit;
		
		$this->newXml = str_replace("[com][/com]","",$this->newXml);
		$this->newXml = str_replace("[emp][/emp]","",$this->newXml);
		$this->newXml = str_replace("[str][/str]","",$this->newXml);
		$this->newXml = str_replace("[code][/code]","",$this->newXml);
		$this->newXml = str_replace("[quot][/quot]","",$this->newXml);
		
		$this->newXml = str_replace("</p><p>","\n",$this->newXml);
		$this->newXml = str_replace("<p>","",$this->newXml);
		$this->newXml = str_replace("</p>","",$this->newXml);

		$this->newXml = str_replace("<p class=\"MsoNormal\">","\n",$this->newXml);
		$this->newXml = str_replace("<p class=\"MsoNormal\" style=\"MARGIN: 0cm 0cm 0pt\">","\n",$this->newXml);
		
		
		$this->newXml = str_replace("<Strong/>","",$this->newXml);
		$this->newXml = str_replace("<Emph/>","",$this->newXml);
		
		$this->newXml = str_replace("<span>","",$this->newXml);
		$this->newXml = str_replace("</span>","",$this->newXml);
		
		//vd(htmlspecialchars($this->newXml));
		
        return($this->newXml);
    }

    
    

    //{{{
    function startElement($parser, $name, $attrs) 
	{
        $new = array("name" => $name,
                                "attrs" => $attrs,
                                "convert" => "",
                                "convert2" => ""
                                );

        $new["convert"] = "<".$name;                
        if (is_array($attrs)) 
		{
            reset ($attrs);
            while (list ($key, $val) = each ($attrs)) 
			{
                $new["convert"] .= " ".$key."=\"".$val."\"";
            }
        }
        $new["convert"] .= ">";
        $new["convert2"] = "</".$name.">";
        
		
		if ($attrs["style"] == "font-style: italic;" || $name == "em") 
		{
            $new["convert"] = "[emp]";
            $new["convert2"] = "[/emp]";
		}
		if ($attrs["style"] == "font-weight: bold;" || $name == "strong") 
		{
            $new["convert"] = "[str]";
            $new["convert2"] = "[/str]";
		}
		
		// alex changed
		if ($attrs["class"] == "ilc_ExtLink") 
		{
			$new["convert"] = "[xln url=\"".str_replace("&","&amp;",$attrs[url])."\"]";
            $new["convert2"] = "[/xln]";
		}
		
		if ($attrs["class"] == "ilc_IntLink") 
		{
			reset ($attrs);
			$N = "";
			while (list ($key, $val) = each ($attrs)) 
			{
				if ($key!="class") 
				{
					$N .= " ".$key."=\"".$val."\"";
				}
			}
			$new["convert"] = "[iln".$N."]";
            $new["convert2"] = "[/iln]";
		}
		
        if ($attrs["class"] == "ilc_Strong") 
		{
            $new["convert"] = "[str]";
            $new["convert2"] = "[/str]";
        }
        if ($attrs["class"] == "ilc_Comment") 
		{
            $new["convert"] = "[com]";
            $new["convert2"] = "[/com]";
        }
        if ($attrs["class"] == "ilc_Emph") 
		{
            $new["convert"] = "[emp]";
            $new["convert2"] = "[/emp]";
        }
        
        if ($attrs["class"] == "ilc_Quotation") 
		{
            $new["convert"] = "[quot]";
            $new["convert2"] = "[/quot]";
        }
        
		if ($attrs["class"] == "footnote") 
		{
			//vd($attrs);

			$fn1 = explode("|**#",stripslashes($_POST["footnotelist"]));
			for ($i=0;$i<count($fn1);$i++) 
			{
				// {{{
				$fn2 = explode("|*#",$fn1[$i]);
				
				//echo htmlspecialchars(serialize($fn2[1]))."<p>";
				
				if($fn2[0] == $attrs["value"]) 
				{
					$new["convert"] = "[fn]".$fn2[1];
					$new["convert2"] = "[/fn]";
					break;
				}
				
				// }}}
			}
			
		}
		
        if ($name == "code") {
            $new["convert"] = "[code]";
            $new["convert2"] = "[/code]";
        }
        
        //echo htmlspecialchars($new[convert]);
                                
        $this->struct[$this->depth] = $new;
        $this->depth++;
        
        if ($name!="br") 
		{
            $this->newXml .= $new[convert];
        } 
		else 
		{
            $this->newXml .= "\n";
        }
        
//        vd($name);
//        vd($attrs);
    }
    
    function characterData($parser, $data) 
	{
//        vd($data);
		//if ($data == "[1]") $data = "";
		if (!stristr( $this->struct[$this->depth-1]["convert"], "[fn]" )) 
		{ 
			$this->newXml .= $data;
		}
		
    }
    
    function endElement($parser, $name)
    {
        $this->depth--;
        
        //$this->newXml .= "</".$name.">";
        if ($name!="br") 
		{
            $this->newXml .= $this->struct[$this->depth]["convert2"];
        }
        
//        vd($name);
    }
    // }}}  
    
}

?>
