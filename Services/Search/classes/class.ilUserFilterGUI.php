<?php declare(strict_types=1);
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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
* GUI class for learning progress filter functionality
* Used for object and learning progress presentation
*
*
* @ilCtrl_Calls ilUserFilterGUI:
*
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @package ilias-tracking
*
*/
class ilUserFilterGUI
{
    private int $usr_id;

    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilUserSearchFilter $filter;
    protected ilObjUser $user;
    protected GlobalHttpState $http;
    protected Factory $refinery;


    public function __construct(int $a_usr_id)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->lng->loadLanguageModule('trac');
        $this->usr_id = $a_usr_id;
        $this->__initFilter();
    }

    /**
    * execute command
    */
    public function executeCommand() : void
    {
        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->ctrl->getCmd() ? $this->ctrl->getCmd() : 'show';
                $this->$cmd();

        }
    }

    
    public function getUserId() : int
    {
        return $this->usr_id;
    }


    public function getHTML() : string
    {
        $tpl = new ilTemplate('tpl.search_user_filter.html', true, true, 'Services/Search');

        $tpl->setVariable("FILTER_ACTION", $this->ctrl->getFormAction($this));
        $tpl->setVariable("TBL_TITLE", $this->lng->txt('trac_lp_filter'));
        $tpl->setVariable("TXT_LOGIN", $this->lng->txt('login'));
        $tpl->setVariable("TXT_FIRSTNAME", $this->lng->txt('firstname'));
        $tpl->setVariable("TXT_LASTNAME", $this->lng->txt('lastname'));
        $tpl->setVariable("BTN_REFRESH", $this->lng->txt('trac_refresh'));

        $tpl->setVariable("QUERY", ilLegacyFormElementsUtil::prepareFormOutput($this->filter->getQueryString('login')));
        $tpl->setVariable(
            "FIRSTNAME",
            ilLegacyFormElementsUtil::prepareFormOutput($this->filter->getQueryString('firstname'))
        );
        $tpl->setVariable(
            "LASTNAME",
            ilLegacyFormElementsUtil::prepareFormOutput($this->filter->getQueryString('lastname'))
        );

        return $tpl->get();
    }

        
        
    public function refresh() : bool
    {
        $filter = [];
        if ($this->http->wrapper()->post()->has('filter')) {
            $filter = (array) ($this->http->request()->getParsedBody()['filter'] ?? []);
        }
        $this->ctrl->setParameter($this, 'offset', 0);
        $this->filter->storeQueryStrings($filter);
        $this->ctrl->returnToParent($this);

        return true;
    }


    public function __initFilter() : bool
    {
        $this->filter = new ilUserSearchFilter($this->user->getId());
        return true;
    }
}
