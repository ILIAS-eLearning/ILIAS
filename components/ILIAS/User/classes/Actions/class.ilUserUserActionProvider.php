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

declare(strict_types=1);

use ILIAS\User\StaticURLHandler;
use ILIAS\StaticURL\Services as StaticURLServices;

/**
 * Adds link to profile
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserUserActionProvider extends ilUserActionProvider
{
    private readonly StaticURLServices $static_url;

    public function __construct()
    {
        global $DIC;
        $this->static_url = $DIC['static_url'];

        parent::__construct();
    }

    public function getComponentId(): string
    {
        return 'user';
    }

    /**
     * @return array<string,string>
     */
    public function getActionTypes(): array
    {
        return [
            "profile" => $this->lng->txt("profile")
        ];
    }

    public function collectActionsForTargetUser(int $target_user): ilUserActionCollection
    {
        $coll = new ilUserActionCollection();
        if (!in_array(
            ilObjUser::_lookupPref($target_user, "public_profile"),
            ["y", "g"]
        )) {
            return $coll;
        }

        $f = new ilUserAction();
        $f->setType("profile");
        $f->setText($this->lng->txt('profile'));
        $f->setHref(
            $this->static_url->builder()->build(
                StaticURLHandler::NAMESPACE,
                null,
                [$target_user]
            )->__toString()
        );
        $coll->addAction($f);

        return $coll;
    }
}
