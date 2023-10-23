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

namespace ILIAS\Portfolio\Notification;

class SharedNotification extends \ilMailNotification
{
    protected array $shared_to_obj_ids = [];
    protected \ilObjUser $user;

    public function __construct()
    {
        global $DIC;
        $this->user = $DIC->user();
        parent::__construct();
    }

    public function setSharedToObjectIds(array $a_val): void
    {
        $this->shared_to_obj_ids = $a_val;
    }

    public function send(): bool
    {
        $rcp = $this->user->getId();

        $this->initLanguage($rcp);
        $this->initMail();
        $this->setSubject(
            sprintf(
                $this->getLanguageText('prtf_successfully_shared_prtf'),
                $this->getObjectTitle(true)
            )
        );
        $this->setBody(\ilMail::getSalutation($rcp, $this->getLanguage()));
        $this->appendBody("\n\n");
        $this->appendBody(
            sprintf(
                $this->getLanguageText('prtf_successfully_shared_prtf_body'),
                $this->getObjectTitle(false)
            )
        );
        $this->appendObjectInformation();
        $this->appendBody("\n\n");
        $this->appendBody($this->getLanguageText('prtf_permanent_link'));
        $this->appendBody("\n");
        $this->appendBody(\ilLink::_getStaticLink($this->getObjId(), "prtf"));
        $this->getMail()->appendInstallationSignature(true);

        $this->sendMail(array($rcp));

        return true;
    }

    protected function appendObjectInformation(): void
    {
        $users = [];
        foreach ($this->shared_to_obj_ids as $obj_id) {
            $type = \ilObject::_lookupType($obj_id);
            switch ($type) {
                case "crs":
                case "grp":
                    $this->appendBody("\n\n" . $this->getLanguage()->txt("obj_" . $type) . ": " .
                        \ilObject::_lookupTitle($obj_id));
                    break;
                case "usr":
                    $users[] = \ilUserUtil::getNamePresentation($obj_id);
                    break;
            }
        }
        if (count($users) > 1) {
            $this->appendBody("\n\n" . $this->getLanguage()->txt("users") . ": ");
            $this->appendBody("\n" . implode("\n", $users));
        }
        if (count($users) === 1) {
            $this->appendBody("\n\n" . $this->getLanguage()->txt("user") . ": " . current($users));
        }
        if (in_array(\ilWorkspaceAccessGUI::PERMISSION_REGISTERED, $this->shared_to_obj_ids, true)) {
            $this->appendBody("\n\n" . $this->getLanguage()->txt("wsp_set_permission_registered"));
        }
        if (in_array(\ilWorkspaceAccessGUI::PERMISSION_ALL, $this->shared_to_obj_ids, true)) {
            $this->appendBody("\n\n" . $this->getLanguage()->txt("wsp_set_permission_all"));
        }
        if (in_array(\ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, $this->shared_to_obj_ids, true)) {
            $this->appendBody("\n\n" . $this->getLanguage()->txt("wsp_set_permission_all_password"));
        }
    }

    protected function initLanguage(int $a_usr_id): void
    {
        parent::initLanguage($a_usr_id);
        $this->getLanguage()->loadLanguageModule('prtf');
        $this->getLanguage()->loadLanguageModule('wsp');
    }
}
