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
 * Adds link to profile
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserUserActionProvider extends ilUserActionProvider
{
    public function getComponentId() : string
    {
        return "user";
    }

    /**
     * @return array<string,string>
     */
    public function getActionTypes() : array
    {
        return array(
            "profile" => $this->lng->txt("profile")
        );
    }

    public function collectActionsForTargetUser(int $a_target_user) : ilUserActionCollection
    {
        $coll = ilUserActionCollection::getInstance();

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
