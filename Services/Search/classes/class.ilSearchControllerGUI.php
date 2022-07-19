<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

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
    protected GlobalHttpState $http;
    protected Factory $refinery;


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
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public function getLastClass() : string
    {
        if (ilSearchSettings::getInstance()->enabledLucene()) {
            $default = 'illucenesearchgui';
        } else {
            $default = 'ilsearchgui';
        }

        $root_id = 0;
        if ($this->http->wrapper()->post()->has('root_id')) {
            $root_id = $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        if ($root_id == self::TYPE_USER_SEARCH) {
            $default = 'illuceneusersearchgui';
        }
        
        $this->setLastClass($default);
        return ilSession::get('search_last_class') ?? $default;
    }
    public function setLastClass(string $a_class) : void
    {
        ilSession::set('search_last_class', $a_class);
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
                $this->ctrl->setCmdClass(ilLuceneSearchGUI::class);
                $this->ctrl->forwardCommand(new ilLuceneSearchGUI());
                break;
                
            case 'illuceneadvancedsearchgui':
                $this->setLastClass('illuceneadvancedsearchgui');
                $this->ctrl->setCmdClass(ilLuceneAdvancedSearchGUI::class);
                $this->ctrl->forwardCommand(new ilLuceneAdvancedSearchGUI());
                break;
            
            case 'illuceneusersearchgui':
                $this->setLastClass('illuceneusersearchgui');
                $this->ctrl->setCmdClass(ilLuceneUserSearchGUI::class);
                $this->ctrl->forwardCommand(new ilLuceneUserSearchGUI());
                break;
                
            case 'iladvancedsearchgui':
                // Remember last class
                $this->setLastClass('iladvancedsearchgui');
                $this->ctrl->setCmdClass(ilAdvancedSearchGUI::class);
                $this->ctrl->forwardCommand(new ilAdvancedSearchGUI());
                break;

            case 'ilsearchgui':
                // Remember last class
                $this->setLastClass('ilsearchgui');
                // no break
            default:
                $search_gui = new ilSearchGUI();
                $this->ctrl->setCmdClass(ilSearchGUI::class);
                $this->ctrl->forwardCommand($search_gui);
                break;
        }
        $this->tpl->printToStdout();
    }
}
