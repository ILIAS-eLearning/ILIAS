<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * class ilLinkCheckerTableGUI
 * 
 * @author	Michael Jansen <mjansen@databay.de> 
 * @version	$Id$
 * 
 */
final class ilLinkCheckerTableGUI extends ilTable2GUI
{
	/**
	 * Link checker instance
	 *
	 * @access	private
	 * @var		ilLinkChecker
	 * @type	ilLinkChecker
	 * 
	 */
	private $linkChecker = null;
	
	/**
	 * Row handler checker instance
	 *
	 * @access	private
	 * @var		ilLinkCheckerGUIRowHandling
	 * @type	ilLinkCheckerGUIRowHandling
	 * 
	 */
	private $rowHandler = null;
	
	/**
	 * Attributes of the refresh button
	 *
	 * @access	private
	 * @var		array
	 * @type	array
	 * 
	 */
	private $refreshButton = array('txt' => null, 'cmd' => null);
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	ilObjectGUI	Instance of the container gui object
	 * @param	string		Parent standard command 
	 * 
	 */
	public function __construct(ilObjectGUI $parentGUIObject, $parentStdCmd)
	{
		parent::__construct($parentGUIObject, $parentStdCmd);
	}
	
	/**
	 * Set the link checker instance
	 *
	 * @access	public
	 * @param	ilLinkChecker	Instance of class ilLinkChecker
	 * @return	ilLinkCheckerTableGUI
	 * 
	 */
	public function setLinkChecker(ilLinkChecker $linkChecker)
	{
		$this->linkChecker = $linkChecker;
		
		return $this;
	}
	
	/**
	 * Gget the link checker instance
	 *
	 * @access	public
	 * @return	ilLinkChecker
	 * 
	 */
	public function getLinkChecker()
	{
		return $this->linkChecker;
	}
	
	/**
	 * Set the row handler
	 *
	 * @access	public
	 * @param	ilLinkCheckerGUIRowHandling	Instance of interface ilLinkCheckerGUIRowHandling
	 * @return	ilLinkCheckerTableGUI
	 * 
	 */
	public function setRowHandler(ilLinkCheckerGUIRowHandling $rowHandler)
	{
		$this->rowHandler = $rowHandler;
		
		return $this;
	}
	
	/**
	 * Get the row handler
	 *
	 * @access	public
	 * @return	ilLinkCheckerGUIRowHandling
	 * 
	 */
	public function getRowHandler()
	{
		return $this->rowHandler;
	}
	
	/**
	 * Set refresh button attributes
	 *
	 * @access	public
	 * @param	string	text
	 * @param	string	command
	 * @return	ilLinkCheckerTableGUI
	 * 
	 */
	public function setRefreshButton($txt, $cmd)
	{
		$this->refreshButton['txt'] = $txt;
		$this->refreshButton['cmd'] = $cmd;
		
		return $this;
	}
	
	/**
	 * get refresh button attributes
	 *
	 * @access	public
	 * @return	array
	 * 
	 */
	public function getRefreshButton()
	{
		return $this->refreshButton;
	}
	
	/**
	 * 
	 * Call this before using getHTML()
	 *
	 * @access	public
	 * @return	ilLinkCheckerTableGUI
	 * 
	 */	
	public function prepareHTML()
	{
		global $ilCtrl, $lng;
		
		// #11002
		$lng->loadLanguageModule("webr");
		
		$title = $this->getParentObject()->object->getTitle().' ('.$lng->txt('invalid_links_tbl').')';
		if($last_access = $this->getLinkChecker()->getLastCheckTimestamp())
		{
			$title .= ', '.$lng->txt('last_change').': '.
					  ilDatePresentation::formatDate(new ilDateTime($last_access, IL_CAL_UNIX));			
		}		
		$this->setTitle($title);
		
		$invalidLinks = $this->getLinkChecker()->getInvalidLinksFromDB();
		if(!count($invalidLinks))
		{
			#$this->setNoEntriesText($lng->txt('no_invalid_links'));
		}
		else
		{
			foreach($invalidLinks as $key => $invalidLink)
			{
				$invalidLinks[$key] = $this->getRowHandler()->formatInvalidLinkArray($invalidLink);
			}
		}
		
		$this->addColumn($lng->txt('title'), 'title', '20%');
		$this->addColumn($lng->txt('url'), 'url', '80%');
		$this->addColumn('', '', '10%');
		$this->setLimit(32000);
		$this->setEnableHeader(true);
		$this->setData($invalidLinks);
		
		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject(), $this->getParentCmd()));		
		$this->setRowTemplate('tpl.link_checker_table_row.html', 'Services/LinkChecker');
		$this->setEnableTitle(true);
		$this->setEnableNumInfo(false);
		
		$refreshButton = $this->getRefreshButton();
		$this->addCommandButton($refreshButton['cmd'], $refreshButton['txt']);
		
		return $this;
	}
}
?>