<?php
/**
* AdminTabulatorhandling
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package application
* 
*/
class AdmTabs
{
	/**
	* ilias object
	* @var object Ilias
	* @access private
	*/
	var $ilias;
	
	/**
	* text elements
	* @var array
	* @access private
	*/
	var $text;
	
	/**
	* languagecode (two characters), e.g. "de", "en", "in"
	* @var string
	* @access publicc
	*/
	var $lng;
	

	/**
	* Constructor
	* @access	public 
	* @return	boolean 	false if reading failed
	*/
	function AdmTabs()
	{
		global $ilias;
		global $tpl;
		
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;

		$this->tabs = array();
				
		return true;
	}
	
	function setHighlight($int)
	{
		$this->highlighted = $int;
	}
	
	/**
	* @access	public 
	* @return	string	text clear-text
	*/
	function setLink($a_text, $a_link)
	{
		$this->tabs[] = array(
			"text" => $a_text,
			"link" => $a_link
		);
	}

	/**
	* @access	public
	*/
	function getOutput()
	{
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.adm_tabs.html");

		$i = 0;

		foreach ($this->tabs as $row)
		{
			$i++;
			if ($i == $this->highlighted)
			{
		    	$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}

			$this->tpl->setCurrentBlock("tab");

			$this->tpl->setVariable("TAB_TYPE", $tabtype);
			$this->tpl->setVariable("TAB_TYPE2", $tab);

			$this->tpl->setVariable("TAB_LINK", $row["link"]);
			$this->tpl->setVariable("TAB_TEXT", $row["text"]);

			$this->tpl->parseCurrentBlock();
		}

		return true;
	}


} // class
?>