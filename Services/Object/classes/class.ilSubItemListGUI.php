<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


include_once './Services/Search/classes/class.ilSearchSettings.php';

/**
* Base class for all sub item list gui's
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesObject
*/
abstract class ilSubItemListGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected static $MAX_SUBITEMS = 5;
    
    protected $cmdClass = null;

    protected $tpl;
    private $highlighter = null;
    
    private static $details = array();
    
    private $subitem_ids = array();
    private $item_list_gui;
    private $ref_id;
    private $obj_id;
    private $type;
    
    /**
     * Constructor
     * @param
     * @return
     */
    public function __construct($a_cmd_class)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        
        $this->cmdClass = $a_cmd_class;
        self::$MAX_SUBITEMS = ilSearchSettings::getInstance()->getMaxSubitems();
    }
    
    /**
     * set show details.
     * Show all subitem links for a specific object
     * As long as static::setShowDetails is not possible this method is final
     *
     * @return
     * @param	int		$a_obj_id	object id
     * @static
     */
    final public static function setShowDetails($a_obj_id)
    {
        $_SESSION['lucene_search']['details'][$a_obj_id] = true;
    }
     
    /**
     * reset details
     * As long as static::resetDetails is not possible this method is final
     *
     * @return
     * @static
     */
    final public static function resetDetails()
    {
        $_SESSION['lucene_search']['details'] = array();
    }
    
    /**
     * enabled show details
     * As long as static::enableDetails is not possible this method is final
     *
     * @param	int		$a_obj_id	object id
     * @return	bool
     * @static
     */
    final public static function enabledDetails($a_obj_id)
    {
        return isset($_SESSION['lucene_search']['details'][$a_obj_id]) and $_SESSION['lucene_search']['details'][$a_obj_id];
    }
    
    /**
     * get cmd class
     * @return
     */
    public function getCmdClass()
    {
        return $this->cmdClass;
    }
    

    /**
     * set highlighter
     * @param
     * @return
     */
    public function setHighlighter($a_highlighter)
    {
        $this->highlighter = $a_highlighter;
    }
    
    /**
     * get highlighter
     * @param
     * @return
     */
    public function getHighlighter()
    {
        return $this->highlighter;
    }
    
    /**
     * get ref id
     * @return
     */
    public function getRefId()
    {
        return $this->ref_id;
    }
    
    /**
     * get obj id
     * @return
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * get type
     * @return
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * get sub item ids
     * @param	bool	$a_limited
     * @return
     */
    public function getSubItemIds($a_limited = false)
    {
        if ($a_limited and !self::enabledDetails($this->getObjId())) {
            return array_slice($this->subitem_ids, 0, self::$MAX_SUBITEMS);
        }
        
        return $this->subitem_ids;
    }
    
    /**
     * get item list gui
     * @return
     */
    public function getItemListGUI()
    {
        return $this->item_list_gui;
    }

    /**
     * init
     * @param
     * @return
     */
    public function init($item_list_gui, $a_ref_id, $a_subitem_ids)
    {
        $this->tpl = new ilTemplate('tpl.subitem_list.html', true, true, 'Services/Object');
        $this->item_list_gui = $item_list_gui;
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->getRefId());
        $this->type = ilObject::_lookupType($this->getObjId());
        
        $this->subitem_ids = $a_subitem_ids;
    }
    
    /**
     * show details link
     * @return
     */
    protected function showDetailsLink()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (count($this->getSubItemIds()) <= self::$MAX_SUBITEMS) {
            return;
        }
        if (self::enabledDetails($this->getObjId())) {
            return;
        }

        $additional = count($this->getSubItemIds()) - self::$MAX_SUBITEMS;
        
        $ilCtrl->setParameterByClass(get_class($this->getCmdClass()), 'details', (int) $this->getObjId());
        $link = $ilCtrl->getLinkTargetByClass(get_class($this->getCmdClass()), '');
        $ilCtrl->clearParametersByClass(get_class($this->getCmdClass()));
        
        $this->tpl->setCurrentBlock('choose_details');
        $this->tpl->setVariable('LUC_DETAILS_LINK', $link);
        $this->tpl->setVariable('LUC_NUM_HITS', sprintf($lng->txt('lucene_more_hits_link'), $additional));
        $this->tpl->parseCurrentBlock();
    }
    
    // begin-patch mime_filter
    protected function parseRelevance($sub_item)
    {
        if (!ilSearchSettings::getInstance()->isSubRelevanceVisible() ||
            !ilSearchSettings::getInstance()->enabledLucene()) {
            return '';
        }
        
        $relevance = $this->getHighlighter()->getRelevance($this->getObjId(), $sub_item);
        
        //$this->tpl->addBlockFile('SUB_REL','sub_rel','tpl.lucene_sub_relevance.html','Services/Search');
        
        include_once "Services/UIComponent/ProgressBar/classes/class.ilProgressBar.php";
        $pbar = ilProgressBar::getInstance();
        $pbar->setCurrent($relevance);
        
        $this->tpl->setVariable('REL_PBAR', $pbar->render());
    }
    // end-patch mime_filter
    
    abstract public function getHTML();
}
