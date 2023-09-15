<?php

declare(strict_types=1);



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
 *  Membership Mail GUI
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesMembership
 */
class ilMembershipMailGUI
{
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    private ilObjectGUI $object;
    protected GlobalHttpState $http;
    protected Factory $refinery;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(ilObjectGUI $object)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->object = $object;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    public function getCurrentObject(): ilObjectGUI
    {
        return $this->object;
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    protected function initRecipientsFromPost(string $name): array
    {
        if ($this->http->wrapper()->post()->has($name)) {
            return $this->http->wrapper()->post()->retrieve(
                $name,
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    protected function initMemberIdFromGet(): int
    {
        if ($this->http->wrapper()->query()->has('member_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'member_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function sendMailToSelectedUsers(): void
    {
        if ($this->http->wrapper()->query()->has('member_id')) {
            $particpants = [$this->initMemberIdFromGet()];
        } else {
            $particpants = array_unique(array_merge(
                $this->initRecipientsFromPost('admins'),
                $this->initRecipientsFromPost('tutors'),
                $this->initRecipientsFromPost('members'),
                $this->initRecipientsFromPost('roles'),
                $this->initRecipientsFromPost('waiting'),
                $this->initRecipientsFromPost('subscribers')
            ));
        }

        if (!count($particpants)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->returnToParent($this);
            return;
        }
        $rcps = [];
        foreach ($particpants as $usr_id) {
            $rcps[] = ilObjUser::_lookupLogin($usr_id);
        }
        $this->ctrl->redirectToURL(
            ilMailFormCall::getRedirectTarget(
                $this->getCurrentObject(),
                'members',
                [],
                [
                    'type' => 'new',
                    'rcp_to' => implode(',', $rcps),
                    'sig' => $this->createMailSignature()
                ]
            )
        );
    }

    protected function createMailSignature(): string
    {
        $this->lng->loadLanguageModule($this->getCurrentObject()->getObject()->getType());

        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt($this->getCurrentObject()->getObject()->getType() . '_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->getCurrentObject()->getObject()->getRefId());
        return rawurlencode(base64_encode($link));
    }
}
