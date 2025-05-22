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

use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Implementation\Component\Symbol\Avatar\Picture;
use ILIAS\UI\Component\Button\Shy as ShyButton;
use ILIAS\UI\Component\Item\Item;

/**
 * @ilCtrl_IsCalledBy ilTutorialSupportBlockGUI: ilColumnGUI
 */
class ilTutorialSupportBlockGUI extends ilBlockGUI
{
    protected const BLOCK_TYPE = 'tusu';
    protected const DATA_MAIL_URL = 'mail_url';
    protected const DATA_NAME = 'name';
    protected const DATA_TUTOR_ID = 'tutor_id';
    protected const EMPTY_MAIL_URL_STRING = '';
    protected const ITEM_LIMIT = 5;
    protected HttpServices $http;
    protected RefineryFactory $refinery;
    protected ilSetting $ilias_settings;
    protected ilRbacSystem $rbac_system;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ilias_settings = $DIC['ilSetting'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->setBlockId('tusu_' . $this->ctrl->getContextObjId());
        $this->setLimit(self::ITEM_LIMIT);
        $this->setTitle($this->lng->txt('tutorial_support_block_title'));
        $this->setPresentation(self::PRES_MAIN_LIST);
    }

    protected function initRefIdFromQuery(): int|null
    {
        if ($this->http->wrapper()->query()->has('ref_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return null;
    }

    protected function createTutorItem(
        string $mail_url,
        string $name,
        int $tutor_id,
    ): Item {
        $properties = [];
        if ($mail_url !== self::EMPTY_MAIL_URL_STRING) {
            $mail_button = $this->createMailShyButton($mail_url);
            $property_name = $this->lng->txt('tutorial_support_block_contact');
            $properties[$property_name] = $mail_button;
        }
        $avatar = new Picture(ilObjUser::_getPersonalPicturePath($tutor_id), $name);
        $tutor_item = $this->ui->factory()->item()->standard($name)
            ->withLeadAvatar($avatar)
            ->withProperties($properties);
        if (
            $this->isTutorCurrentUser($tutor_id) ||
            $this->user->isAnonymous() ||
            !$this->hasContactEnabled($tutor_id)
        ) {
            return $tutor_item;
        }
        $dropdown_actions[] = $this->ui->factory()->legacy()->content(
            ilBuddySystemLinkButton::getInstanceByUserId($tutor_id)->getHTML()
        );
        $dropdown = $this->ui->factory()->dropdown()->standard($dropdown_actions);
        return $tutor_item->withActions($dropdown);
    }

    protected function getTutorData(ilObjUser $tutor): array
    {
        return [
            self::DATA_MAIL_URL => $this->getMailUrlOfUser($tutor),
            self::DATA_NAME => $tutor->getFullname(),
            self::DATA_TUTOR_ID => $tutor->getId(),
        ];
    }

    protected function getTutorIds(): array
    {
        $crs_ref_id = $this->initRefIdFromQuery();
        if (is_null($crs_ref_id)) {
            return [];
        }
        $crs_obj_id = ilObjCourse::_lookupObjId($crs_ref_id);
        $course_members = new ilCourseParticipants($crs_obj_id);
        return $course_members->getContacts();
    }

    protected function hasPublicProfile(ilObjUser $tutor): bool
    {
        return $tutor->getPref('public_profile') === 'g' ||
            (
                !$this->user->isAnonymous() &&
                $tutor->getPref('public_profile') === 'y'
            );
    }

    protected function hasContactEnabled(int $tutor_id): bool
    {
        if (!ilBuddySystem::getInstance()->isEnabled()) {
            return false;
        }
        $setting_value = ilObjUser::_lookupPref($tutor_id, 'bs_allow_to_contact_me');
        return is_null($setting_value) ? false : $setting_value === 'y';
    }

    protected function isIliasInternalMailEnabled(): bool
    {
        return $this->rbac_system->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId());
    }

    protected function isTutorCurrentUser(int $tutor_id)
    {
        return $tutor_id === $this->user->getId();
    }

    protected function isUserValid(ilObjUser $tutor): bool
    {
        return !$tutor->isAnonymous() && $this->hasPublicProfile($tutor);
    }

    protected function isCurrentUserAllowedToSeeTutorBlock(): bool
    {
        return !$this->user->isAnonymous() ||
            (
                $this->user->isAnonymous() &&
                $this->areILIASGlobalProfilesEnabled()
            );
    }

    protected function areILIASGlobalProfilesEnabled(): bool
    {
        return (bool) $this->ilias_settings->get('enable_global_profiles');
    }

    protected function createMailShyButton(string $mail_url): ShyButton
    {
        return $this->ui->factory()->button()->shy(
            $this->lng->txt('tutorial_support_block_send_mail'),
            $mail_url
        );
    }

    protected function getMailUrlOfUser(ilObjUser $tutor): string
    {
        if (
            !$this->isIliasInternalMailEnabled()
        ) {
            return (string) self::EMPTY_MAIL_URL_STRING;
        }
        return ilMailFormCall::getLinkTarget(
            $this->http->request()->getUri()->__toString(),
            '',
            [],
            [
                'type' => 'new',
                'rcp_to' => $tutor->getLogin()
            ]
        );
    }

    public function getData(): array
    {
        if (!$this->isCurrentUserAllowedToSeeTutorBlock()) {
            return [];
        }
        $data = [];
        $tutor_ids = $this->getTutorIds();
        foreach ($tutor_ids as $tutor_id) {
            $tutor = new ilObjUser($tutor_id);
            if (
                !$this->isUserValid($tutor) ||
                $this->user->isAnonymous()
            ) {
                continue;
            }
            $data[] = $this->getTutorData($tutor);
        }
        return $data;
    }

    protected function getListItemForData(array $data): Item
    {
        return $this->createTutorItem(
            (string) $data[self::DATA_MAIL_URL],
            (string) $data[self::DATA_NAME],
            (int) $data[self::DATA_TUTOR_ID]
        );
    }

    public function getBlockType(): string
    {
        return self::BLOCK_TYPE;
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }
}
