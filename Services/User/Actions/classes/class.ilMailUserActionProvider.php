<?php

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

/**
 * Adds link to mail
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMailUserActionProvider extends ilUserActionProvider
{
    public static array $user_access = array();

    public function checkUserMailAccess(int $a_user_id) : bool
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        if (!isset(self::$user_access[$a_user_id])) {
            self::$user_access[$a_user_id] =
                $rbacsystem->checkAccessOfUser($a_user_id, 'internal_mail', ilMailGlobalServices::getMailObjectRefId());
        }
        return (bool) self::$user_access[$a_user_id];
    }

    public function getComponentId() : string
    {
        return "mail";
    }

    /**
     * @return array<string,string>
     */
    public function getActionTypes() : array
    {
        return array(
            "compose" => $this->lng->txt("mail")
        );
    }

    public function collectActionsForTargetUser(int $a_target_user) : ilUserActionCollection
    {
        $coll = ilUserActionCollection::getInstance();

        // check mail permission of user
        if ($this->getUserId() == ANONYMOUS_USER_ID || !$this->checkUserMailAccess($this->getUserId())) {
            return $coll;
        }

        // check mail permission of target user
        if ($this->checkUserMailAccess($a_target_user)) {
            $f = new ilUserAction();
            $f->setType("compose");
            $f->setText($this->lng->txt("mail"));
            $tn = ilObjUser::_lookupName($a_target_user);
            $f->setHref(ilMailFormCall::getLinkTarget("", '', array(), array('type' => 'new', 'rcp_to' => $tn["login"])));
            $coll->addAction($f);
        }

        return $coll;
    }
}
