<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

/**
 * TableGUI class for selection of milestone responsibles
 * @author  Alex Killing <alex.killing@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilMilestoneResponsiblesTableGUI extends ilTable2GUI
{
    protected int $grp_id;
    protected int $app_id;

    private array $resp_users = [];

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_grp_id,
        int $a_app_id
    ) {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->grp_id = $a_grp_id;
        $this->app_id = $a_app_id;

        $this->addColumn("", "", "1");
        $this->addColumn($this->lng->txt("user"), "", "100%");
        $this->setRowTemplate(
            "tpl.ms_responsible_users_row.html",
            "Services/Calendar"
        );
        $this->setEnableHeader(true);
        $this->setDefaultOrderField("lastname");
        $this->setMaxCount(9999);
        $this->ctrl->setParameter($a_parent_obj, "app_id", $this->app_id);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->getParticipantsAndResponsibles();
        $this->setTitle($this->lng->txt("cal_ms_users_responsible"));
        $this->addMultiCommand(
            "saveMilestoneResponsibleUsers",
            $this->lng->txt("cal_save_responsible_users")
        );
    }

    /**
     * Get participants and responsible users
     */
    public function getParticipantsAndResponsibles()
    {
        $participants = array();
        if ($this->app_id > 0) {
            $app = new ilCalendarEntry($this->app_id);
            $resp_users = $app->readResponsibleUsers();
            foreach ($resp_users as $v) {
                $n = ilObjUser::_lookupName($v["user_id"]);
                $participants[$v["user_id"]] = array_merge($n, array("type" => "non-member"));
                $this->resp_users[] = $v["user_id"];
            }
        }

        $part = ilGroupParticipants::_getInstanceByObjId($this->grp_id);
        $admins = $part->getAdmins();
        $members = $part->getMembers();
        foreach ($members as $v) {
            $n = ilObjUser::_lookupName($v);
            $participants[$v] = array_merge($n, array("type" => "member"));
        }
        foreach ($admins as $v) {
            $n = ilObjUser::_lookupName($v);
            $participants[$v] = array_merge($n, array("type" => "admin"));
        }
        $this->setData($participants);
    }

    /**
     * Standard Version of Fill Row. Most likely to
     * be overwritten by derived class.
     */
    protected function fillRow(array $a_set) : void
    {
        if (is_array($this->resp_users) && in_array($a_set["user_id"], $this->resp_users)) {
            $this->tpl->setVariable("CHECKED", ' checked="checked" ');
        }
        $this->tpl->setVariable("USER_ID", $a_set["user_id"]);
        $this->tpl->setVariable("TXT_FIRSTNAME", $a_set["firstname"]);
        $this->tpl->setVariable("TXT_LASTNAME", $a_set["lastname"]);
        $this->tpl->setVariable("TXT_LOGIN", ilObjUser::_lookupLogin($a_set["user_id"]));
    }
}
