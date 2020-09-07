<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/Actions/classes/class.ilUserActionProvider.php");

/**
 * Adds link to profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserUserActionProvider extends ilUserActionProvider
{
    /**
     * @inheritdoc
     */
    public function getComponentId()
    {
        return "user";
    }

    /**
     * @inheritdoc
     */
    public function getActionTypes()
    {
        return array(
            "profile" => $this->lng->txt("profile")
        );
    }

    /**
     * Collect all actions
     *
     * @param int $a_target_user target user
     * @return ilUserActionCollection collection
     */
    public function collectActionsForTargetUser($a_target_user)
    {
        $coll = ilUserActionCollection::getInstance();
        include_once("./Services/User/Actions/classes/class.ilUserAction.php");

        if (!in_array(
            ilObjUser::_lookupPref($a_target_user, "public_profile"),
            array("y", "g")
        )) {
            return $coll;
        }

        $f = new ilUserAction();
        $f->setType("profile");
        $f->setText($this->lng->txt('profile'));
        $f->setHref("./goto.php?target=usr_" . $a_target_user);
        $coll->addAction($f);

        return $coll;
    }
}
