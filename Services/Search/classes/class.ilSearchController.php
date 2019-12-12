<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSearchController
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
* @ilCtrl_Calls ilSearchController: ilSearchGUI, ilAdvancedSearchGUI
* @ilCtrl_Calls ilSearchController: ilLuceneSearchGUI, ilLuceneAdvancedSearchGUI, ilLuceneUserSearchGUI
*
*/

class ilSearchController
{
    const TYPE_USER_SEARCH = -1;
    
    public $ctrl = null;
    public $ilias = null;
    public $lng = null;

    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilias = $DIC['ilias'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $this->ilias = $ilias;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
    }

    public function getLastClass()
    {
        include_once './Services/Search/classes/class.ilSearchSettings.php';
        if (ilSearchSettings::getInstance()->enabledLucene()) {
            $default = 'illucenesearchgui';
        } else {
            $default = 'ilsearchgui';
        }
        if ($_REQUEST['root_id'] == self::TYPE_USER_SEARCH) {
            $default = 'illuceneusersearchgui';
        }
        
        $this->setLastClass($default);
        
        return $_SESSION['search_last_class'] ? $_SESSION['search_last_class'] : $default;
    }
    public function setLastClass($a_class)
    {
        $_SESSION['search_last_class'] = $a_class;
    }

    public function &executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        include_once 'Services/Search/classes/class.ilSearchSettings.php';

        // Check hacks
        if (!$rbacsystem->checkAccess('search', ilSearchSettings::_getSearchSettingRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        $forward_class = $this->ctrl->getNextClass($this) ? $this->ctrl->getNextClass($this) : $this->getLastClass();
        
        switch ($forward_class) {
            case 'illucenesearchgui':
                $this->setLastClass('illucenesearchgui');
                include_once './Services/Search/classes/Lucene/class.ilLuceneSearchGUI.php';
                $this->ctrl->forwardCommand(new ilLuceneSearchGUI());
                break;
                
            case 'illuceneadvancedsearchgui':
                $this->setLastClass('illuceneadvancedsearchgui');
                include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchGUI.php';
                $this->ctrl->forwardCommand(new ilLuceneAdvancedSearchGUI());
                break;
            
            case 'illuceneusersearchgui':
                $this->setLastClass('illuceneusersearchgui');
                include_once './Services/Search/classes/Lucene/class.ilLuceneUserSearchGUI.php';
                $this->ctrl->forwardCommand(new ilLuceneUserSearchGUI());
                break;
                
            case 'iladvancedsearchgui':
                // Remember last class
                $this->setLastClass('iladvancedsearchgui');

                include_once 'Services/Search/classes/class.ilAdvancedSearchGUI.php';

                $this->ctrl->forwardCommand(new ilAdvancedSearchGUI());
                break;

            case 'ilsearchgui':
                // Remember last class
                $this->setLastClass('ilsearchgui');

                // no break
            default:
                include_once 'Services/Search/classes/class.ilSearchGUI.php';

                $search_gui = new ilSearchGUI();
                $this->ctrl->forwardCommand($search_gui);
                break;
        }
        $this->tpl->show();

        return true;
    }
}
