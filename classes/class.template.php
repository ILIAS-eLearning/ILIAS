<?php
require_once('HTML/ITX.php');

/**
* special template class to simplify handling of ITX/PEAR
* @author Stefan Kesseler <skesseler@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
* @package application
*/
class Template extends IntegratedTemplateExtension
{
    /**
	* variablen die immer in jedem block ersetzt werden sollen
    * @var array
    */
    var $vars;

    /**
    * Aktueller Block
    * Der wird gemerkt bei der berladenen Funktion setCurrentBlock, damit beim ParseBlock
    * vorher ein replace auf alle Variablen gemacht werden kann, die mit dem BLockname anfangen.
    */
    var $activeBlock;

	/**
	* constructor
	* @param string $file templatefile (mit oder ohne pfad)
	* @param boolean $flag1 wie in IntegratedTemplate
	* @param boolean $flag1 wie in IntegratedTemplate
	* @param array $vars zu ersetzenden Variablen
	* @access public
	*/
	function Template($file,$flag1,$flag2,$vars="DEFAULT")
	{
        global $ilias;
		$this->activeBlock = "__global__";
		$this->vars = array();

        if (strpos($file,"/")===false)
		{
            $fname = $ilias->tplPath;
		    $fname .= $ilias->account->skin."/";
			$fname .= basename($file);
        }
		else
		{
            $fname = $file;
        }
		$this->tplName = basename($fname);
		$this->tplPath = dirname($fname);
		
        if (!file_exists($fname)) {
            $ilias->raiseError("template ".$fname." was not found.", $ilias->error_obj->FATAL);
            return false;
        }

        $this->IntegratedTemplateExtension(dirname($fname));
        $this->loadTemplatefile(basename($fname), $flag1, $flag2);

		//add tplPath to replacevars
		$this->vars["TPLPATH"] = $this->tplPath;

        return true;
    }

/**
* @param string
*/
    function get($part = "DEFAULT") {

        if ($part == "DEFAULT") {
            return parent::get();
        } else {
            return parent::get($part);
        }
    }

/**
* @param string
*/
    function show($part = "DEFAULT") {

		$this->fillVars();

        // ERROR HANDLER SETS $_GET["message"] IN CASE OF $error_obj->MESSAGE
		if ($_SESSION["message"] || $_SESSION["info"])
		{
		   $this->addBlockFile("MESSAGE", "message", "tpl.message.html");
		   $this->setCurrentBlock("message");
   
		   if($_SESSION["message"])
		   {
			  $this->setVariable("MSG", $_SESSION["message"]);
			  session_unregister("message");
		   }
		   else
		   {
			  $this->setVariable("INFO",$_SESSION["info"]);
			  session_unregister("info");
		   }
		   $this->parseCurrentBlock();
		}

        if ($part == "DEFAULT") {
            parent::show();
        } else {
            parent::show($part);
        }
		if (((substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "error.php")
			&& (substr(strrchr($_SERVER["PHP_SELF"],"/"),1) != "adm_menu.php")))
		{
			$_SESSION["referer"] = $_SERVER["REQUEST_URI"];
			$_SESSION["post_vars"] = $_POST;
		}
    }

	/**
	* added by pg
	*/
	function fillVars()
	{
        $count = 0;
		reset($this->vars);

        while(list($key, $val) = each($this->vars))
		{
			if (is_array($this->blockvariables[$this->activeBlock]))
			{
				if  (array_key_exists($key, $this->blockvariables[$this->activeBlock]))
				{
					$count++;
					
					$this->setVariable($key, $val);
				}
			}
        }
		
		return $count;
	}
	
    /**
    *	überladene Funktion, die sich hier lokal noch den aktuellen Block merkt.
    * @param string
    */
    function setCurrentBlock($part = "DEFAULT") {
	    $this->activeBlock = $part;

        if ($part == "DEFAULT") {
            return parent::setCurrentBlock();
        } else {
            return parent::setCurrentBlock($part);
        }
    }

	function touchBlock($block)
	{
		$this->setCurrentBlock($block);
		$count = $this->fillVars();
		$this->parseCurrentBlock();
		
		if ($count == 0 )
		{
			parent::touchBlock($block);
		}
		
	}
	
    /**
    *	überladene Funktion, die auf den aktuelle Block vorher noch ein replace ausfhrt
    * @param string
    */
    function parseCurrentBlock($part = "DEFAULT") {

	    // Hier erst noch ein replace aufrufen
        if ($part != "DEFAULT") {
	        $tmp = $this->activeBlock;
	        $this->activeBlock = $part;
		}

        if ($part != "DEFAULT") {
	        $this->activeBlock = $tmp;
		}

		$this->fillVars();

		$this->activeBlock = "__global__";

        if ($part == "DEFAULT") {
            return parent::parseCurrentBlock();
        } else {
            return parent::parseCurrentBlock($part);
        }
    }


    /**
    *		$block = "anzeige_loop";
            $conv = array("kd_pk"=>"kategorie_value",
                "name"=>"kategorie_text");
            $select = array("id"=>"kd_pk",
                "value"=>$herecopy["pd_kategorie"],
                "field"=>"kategorie_selected",
                "text"=>"selected"
                );
		* @param string
		* @param string
		* @param string
		* @param string
    */
    function replaceFromDatabase(&$DB,$block,$conv,$select="default") {

       $res = $DB->selectDbAll();
       while ($DB->getDbNextElement($res)) {
          $this->setCurrentBlock($block);
          $result = array();
          reset($conv);
          while (list ($key,$val) = each ($conv)) {
              $result[$val]=$DB->element->data[$key];
          }

          if (
                ($select != "default")
                &&
                (
                    $DB->element->data[$select["id"]]==$select["value"]
                    ||
                    (
                        strtolower($select["text"]) == "checked"
                        &&
                        strpos( ",,".$select["value"].",," , ",".$DB->element->data[$select["id"]]."," )!=false
                    )
                )
              ) {
              $result[$select["field"]] = $select["text"];
          }
          $this->replace($result);
          $this->parseCurrentBlock($block);
       }
    }

       /**
       *    Wird angewendet, wenn die Daten in ein Formular replaced werden sollen,
       *    Dann wird erst noch ein htmlspecialchars drumherum gemacht.
       * @param string
       */
    function prepareForFormular($vars) {
        if (!is_array($vars))  return;
        reset($vars);
        while(list($i) = each($vars)) {
            $vars[$i] = stripslashes($vars[$i]);
            $vars[$i] = htmlspecialchars($vars[$i]);
        }
        return($vars);
    }

    /**
    * @param string
    * @param string
    */
    function replace()
	{
        reset($this->vars);
        while(list($key, $val) = each($this->vars))
		{
            $this->setVariable($key, $val);
        }
    }

    function replaceDefault() {
	    $this->replace($this->vars);
    }

	/**
	* checks for a topic in the template
	* @param	string
	* @param	string
	* @access	private
	*/
	function checkTopic($a_block, $a_topic)
	{
		return array_key_exists($a_topic, $this->blockvariables[$a_block]);
	}
	
	/**
	* check if there is a NAVIGATION-topic
	* @access public
	*/
	function includeNavigation()
	{
		return $this->checkTopic("__global__", "NAVIGATION");
	}
	
	/**
	* check if there is a TREE-topic
	* @access public
	*/
	function includeTree()
	{
		return $this->checkTopic("__global__", "TREE");
	}
	
	function fileExists($filename)
	{
		return file_exists($this->tplPath."/".$filename);
	}
	
	function addBlockFile($var, $block, $tplname)
	{
		if (DEBUG)
		{
			echo "Template '".$this->tplPath."/".$tplname."'<br>";
		}
		
		if (file_exists($this->tplPath."/".$tplname) == false)
		{
		    echo "Template '".$this->tplPath."/".$tplname."' doesn't exist! aborting...<br>";
			return false;
		}
		return parent::addBlockFile($var, $block, $tplname);
	}
}

?>
