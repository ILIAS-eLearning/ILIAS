<?php declare(strict_types=1);

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSearchController
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
* @ilCtrl_Calls ilSearchControllerGUI: ilSearchGUI, ilAdvancedSearchGUI
* @ilCtrl_Calls ilSearchControllerGUI: ilLuceneSearchGUI, ilLuceneAdvancedSearchGUI, ilLuceneUserSearchGUI
*
*/

class ilSearchControllerGUI implements ilCtrlBaseClassInterface
{
    public const TYPE_USER_SEARCH = -1;
    
    protected ilCtrl $ctrl;
    protected ILIAS $ilias;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilRbacSystem $system;

    /**
    * Constructor
    * @access public
    */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->ilias = $DIC['ilias'];
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->system = $DIC->rbac()->system();
    }

    public function getLastClass() : string
    {
                if (ilSearchSettings::getInstance()->enabledLucene()) {
            $default = 'illucenesearchgui';
        } else {
            $default = 'ilsearchgui';
        }
        if ($_REQUEST['root_id'] == self::TYPE_USER_SEARCH) {
            $default = 'illuceneusersearchgui';
        }
        
        $this->setLastClass($default);
        
        return $_SESSION['search_last_class'] ?: $default;
    }
    public function setLastClass(string $a_class) : void
    {
        $_SESSION['search_last_class'] = $a_class;
    }

    public function executeCommand() : void
    {
        // Check hacks
        if (!$this->system->checkAccess('search', ilSearchSettings::_getSearchSettingRefId())) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }
        $forward_class = $this->ctrl->getNextClass($this) ? $this->ctrl->getNextClass($this) : $this->getLastClass();
        
        switch ($forward_class) {
            case 'illucenesearchgui':
                $this->setLastClass('illucenesearchgui');
                                $this->ctrl->forwardCommand(new ilLuceneSearchGUI());
                break;
                
            case 'illuceneadvancedsearchgui':
                $this->setLastClass('illuceneadvancedsearchgui');
                                $this->ctrl->forwardCommand(new ilLuceneAdvancedSearchGUI());
                break;
            
            case 'illuceneusersearchgui':
                $this->setLastClass('illuceneusersearchgui');
                                $this->ctrl->forwardCommand(new ilLuceneUserSearchGUI());
                break;
                
            case 'iladvancedsearchgui':
                // Remember last class
                $this->setLastClass('iladvancedsearchgui');
                $this->ctrl->forwardCommand(new ilAdvancedSearchGUI());
                break;

            case 'ilsearchgui':
                // Remember last class
                $this->setLastClass('ilsearchgui');
                // no break
            default:

                $search_gui = new ilSearchGUI();
                $this->ctrl->forwardCommand($search_gui);
                break;
        }
        $this->tpl->printToStdout();
    }
}
