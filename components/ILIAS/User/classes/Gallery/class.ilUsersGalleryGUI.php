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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Legacy\Legacy as LegacyComponent;
use ILIAS\UI\Component\Card\Standard as StandardCard;
use ILIAS\UI\Component\Image\Image as Image;
use ILIAS\UI\Renderer;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @ilCtrl_Calls ilUsersGalleryGUI: ilPublicUserProfileGUI
 * @ilCtrl_isCalledBy ilUsersGalleryGUI: ilCourseMembershipGUI, ilGroupMembershipGUI
 */
class ilUsersGalleryGUI
{
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilObjUser $user;
    private ilRbacSystem $rbacsystem;
    private UIFactory $ui_factory;
    private Renderer $ui_renderer;
    private GlobalHttpState $http;
    private Refinery $refinery;

    private ilUserActionGUI $user_action_gui;

    public function __construct(protected ilUsersGalleryCollectionProvider $collection_provider)
    {
        /** @var $DIC ILIAS\DI\Container */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->user_action_gui = new ilUserActionGUI(
            new ilUserActionProviderFactory(),
            new ilGalleryUserActionContext(),
            $this->tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->lng,
            $DIC['ilDB'],
            $this->user->getId()
        );
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd('view');

        switch (strtolower($next_class)) {
            case strtolower(ilPublicUserProfileGUI::class):
                $profile_gui = new ilPublicUserProfileGUI(
                    $this->http->wrapper()->query()->retrieve(
                        'user',
                        $this->refinery->kindlyTo()->int()
                    )
                );
                $profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'view'));
                $this->ctrl->forwardCommand($profile_gui);
                break;

            default:
                switch ($cmd) {
                    default:
                        $this->$cmd();
                        break;
                }
                break;
        }
    }

    protected function view(): void
    {
        $template = $this->populateTemplate($this->collection_provider->getGroupedCollections());
        $this->tpl->setContent($template->get());
    }

    /**
     * @param array<ilUsersGalleryUserCollection> $gallery_groups
     */
    protected function populateTemplate(array $gallery_groups): ilTemplate
    {
        $tpl = new ilTemplate('tpl.users_gallery.html', true, true, 'Services/User');

        $panel = ilPanelGUI::getInstance();
        $panel->setBody($this->lng->txt('no_gallery_users_available'));
        $tpl->setVariable('NO_ENTRIES_HTML', json_encode($panel->getHTML(), JSON_THROW_ON_ERROR));

        $groups_with_users = array_filter(
            $gallery_groups,
            static fn(ilUsersGalleryGroup $group): bool => count($group) > 0
        );

        if (count($groups_with_users) === 0) {
            $tpl->setVariable('NO_GALLERY_USERS', $panel->getHTML());
            return $tpl;
        }

        $cards = [];

        foreach ($gallery_groups as $group) {
            $sorted_group = new ilUsersGallerySortedUserGroup($group, new ilUsersGalleryUserCollectionPublicNameSorter());

            foreach ($sorted_group as $user) {
                $cards[] = $this->getCardForUser($user, $sorted_group);
            }
        }

        $tpl->setVariable('GALLERY_HTML', $this->ui_renderer->render($this->ui_factory->deck($cards)));

        if ($this->collection_provider->hasRemovableUsers()) {
            $tpl->touchBlock('js_remove_handler');
        }

        return $tpl;
    }

    protected function getCardForUser(
        ilUsersGalleryUser $user,
        ilUsersGalleryUserCollection $group
    ): StandardCard {
        $card = $this->ui_factory->card()->standard($user->getPublicName())
            ->withHighlight($group->isHighlighted());
        $avatar = $this->ui_factory->image()->standard(
            $user->getAggregatedUser()->getPersonalPicturePath('big'),
            $user->getPublicName()
        );

        $sections = [];

        $sections[] = $this->ui_factory->listing()->descriptive(
            [
                $this->lng->txt("username") => $user->getAggregatedUser()->getLogin(),
                $this->lng->txt("crs_contact_responsibility") => $group->getLabel()
            ]
        );

        $actions_section = $this->getActionsSection($user->getAggregatedUser());
        if ($actions_section !== null) {
            $sections[] = $actions_section;
        }

        if ($user->hasPublicProfile()) {
            list($avatar, $card) = $this->addPublicProfileLinksToAvatarAndCard(
                $avatar,
                $card,
                $user->getAggregatedUser()->getId()
            );
        }

        return $card->withImage($avatar)->withSections($sections);
    }

    protected function getActionsSection(ilObjUser $user): ?LegacyComponent
    {
        $contact_btn_html = "";

        if (!$this->user->isAnonymous() &&
            !$user->isAnonymous() &&
            ilBuddySystem::getInstance()->isEnabled() &&
            $this->user->getId() !== $user->getId()
        ) {
            $contact_btn_html = ilBuddySystemLinkButton::getInstanceByUserId($user->getId())->getHtml();
        }

        $list_html = $this->user_action_gui->renderDropDown($user->getId());

        if ($contact_btn_html || $list_html) {
            return $this->ui_factory->legacy(
                "<div style='display:grid; grid-template-columns: max-content max-content;'>"
                . "<div>"
                . $contact_btn_html
                . "</div><div style='margin-left: 5px;'>"
                . $list_html
                . "</div></div>"
            );
        }

        return null;
    }

    protected function addPublicProfileLinksToAvatarAndCard(
        Image $avatar,
        StandardCard $card,
        int $user_id
    ): array {
        $this->ctrl->setParameterByClass(
            ilPublicUserProfileGUI::class,
            'user',
            $user_id
        );
        $public_profile_url = $this->ctrl->getLinkTargetByClass(
            ilPublicUserProfileGUI::class,
            'getHTML'
        );

        return [
            $avatar->withAction($public_profile_url),
            $card->withTitleAction($public_profile_url)
        ];
    }
}
