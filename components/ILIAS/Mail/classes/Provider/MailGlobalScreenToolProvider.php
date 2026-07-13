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

namespace ILIAS\Mail\Provider;

use ilCtrlException;
use ilCtrlInterface;
use ILIAS\Data\URI;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\HTTP\Wrapper\WrapperFactory as HttpWrapper;
use ILIAS\Mail\Folder\MailFolderData;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Link\Bulky as BulkyLink;
use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Legacy\Content as LegacyContent;
use ILIAS\UI\Component\Symbol\Icon\Custom as CustomIcon;
use ILIAS\UI\Component\Symbol\Icon\Factory as IconFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ilLanguage;
use ilMailAttachmentGUI;
use ilMailbox;
use ilMailFolderGUI;
use ilMailFormGUI;
use ilMailGUI;
use ilMailOptionsGUI;
use ilObjUser;
use ilUtil;

class MailGlobalScreenToolProvider extends AbstractDynamicToolProvider
{
    final public const string SHOW_MAIL_FOLDERS_TOOL = 'show_mail_folders_tool';

    private readonly UIFactory $ui_factory;
    private readonly UIRenderer $ui_renderer;
    private readonly ilMailbox $mbox;
    private readonly ilObjUser $user;
    private readonly ?ilCtrlInterface $ctrl;
    private readonly HttpWrapper $http_wrapper;
    private readonly Refinery $refinery;
    private readonly ilLanguage $lng;
    private readonly IconFactory $icon_factory;

    public function __construct(
        Container $dic,
        ?UIFactory $ui_factory = null,
        ?UIRenderer $ui_renderer = null,
        ?ilObjUser $user = null,
        ?ilCtrlInterface $ctrl = null,
        ?HttpWrapper $http_wrapper = null,
        ?Refinery $refinery = null,
        ?ilLanguage $lng = null,
        ?IconFactory $icon_factory = null
    ) {
        parent::__construct($dic);
        $this->ui_factory = $ui_factory ?? $this->dic->ui()->factory();
        $this->ui_renderer = $ui_renderer ?? $this->dic->ui()->renderer();
        $this->user = $user ?? $this->dic->user();
        $this->ctrl = $ctrl ?? $this->dic->ctrl();
        $this->http_wrapper = $http_wrapper ?? $this->dic->http()->wrapper();
        $this->refinery = $refinery ?? $this->dic->refinery();
        $this->lng = $lng ?? $this->dic->language();
        $this->icon_factory = $icon_factory ?? $this->ui_factory->symbol()->icon();

        $this->mbox = new ilMailbox($this->user->getId());
    }

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main()->repository()->administration();
    }

    public function getToolsForContextStack(CalledContexts $called_contexts): array
    {
        $identification = fn($id): IdentificationInterface => $this->identification_provider->contextAwareIdentifier($id);

        $tools = [];

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_MAIL_FOLDERS_TOOL) &&
            $additional_data->get(self::SHOW_MAIL_FOLDERS_TOOL) === true) {

            $tools[] = $this->factory
                ->tool($identification('mail_folders_tree'))
                ->withTitle($this->lng->txt('mail'))
                ->withSymbol($this->icon_factory->standard('mail', $this->lng->txt('mail')))
                ->withContentWrapper(function (): LegacyContent {
                    $current_folder_id = $this->http_wrapper->query()->retrieve(
                        'mobj_id',
                        $this->refinery->kindlyTo()->int()
                    );

                    $this->ctrl->setParameterByClass(ilMailFormGUI::class, 'type', ilMailFormGUI::MAIL_FORM_TYPE_NEW);

                    $sub_items = [
                        $this->buildItem(
                            $this->lng->txt('mail_new'),
                            'mail',
                            $this->buildFolderLink($current_folder_id, ilMailFormGUI::class)
                        ),
                        ...$this->buildSubItems(),
                        $this->buildItem(
                            $this->lng->txt('mail_attachments'),
                            'attach',
                            $this->buildFolderLink($current_folder_id, [ilMailGUI::class, ilMailAttachmentGUI::class])
                        )
                    ];

                    if ($this->dic->settings()->get('show_mail_settings', '0')) {
                        $sub_items[] = $this->buildItem(
                            $this->lng->txt('mail_options'),
                            'adm',
                            $this->buildFolderLink($current_folder_id, ilMailOptionsGUI::class)
                        );
                    }

                    $item = $this->ui_factory->menu()->drilldown(
                        $this->lng->txt('mail'),
                        $sub_items
                    );

                    return $this->ui_factory->legacy()->content(
                        $this->ui_renderer->render($item)
                    );
                });
        }

        return $tools;
    }

    /**
     * @return Clickable[]
     */
    private function buildSubItems(): array
    {
        $items = [];

        $folders = $this->mbox->getSubFolders();
        usort(
            $folders,
            static fn(MailFolderData $a, MailFolderData $b): int => $a->isTrash() && !$b->isUserLocalFolder() ? 1 : 0
        );
        $user_folders = $this->filterUserFolders($folders);

        foreach ($folders as $folder) {
            if ($folder->isUserLocalFolder() && $user_folders !== []) {
                $icon_name = $folder->getType()->value;
                $items[] = $this->ui_factory->menu()->sub(
                    $folder->getTitle(),
                    [
                        $this->buildItem(
                            $this->lng->txt('mail_main_folder'),
                            $icon_name,
                            $this->buildFolderLink($folder->getFolderId(), ilMailFolderGUI::class)
                        ),
                        ...array_map(
                            function (MailFolderData $folder) use ($icon_name): BulkyLink {
                                return $this->buildItem(
                                    $folder->getTitle(),
                                    $icon_name,
                                    $this->buildFolderLink($folder->getFolderId(), ilMailFolderGUI::class)
                                );
                            },
                            $user_folders
                        ),
                    ]
                );
                continue;
            }

            $items[] = $this->buildItem(
                $folder->getTitle(),
                $folder->getType()->value,
                $this->buildFolderLink($folder->getFolderId(), ilMailFolderGUI::class)
            );
        }

        return $items;
    }

    /**
     * @param class-string|list<class-string> $class
     * @throws ilCtrlException
     */
    private function buildFolderLink(int $folderId, string|array $class, ?string $cmd = null): string
    {
        $this->ctrl->setParameterByClass(is_array($class) ? current($class) : $class, 'mobj_id', $folderId);
        $url = $this->ctrl->getLinkTargetByClass($class, $cmd);
        $this->ctrl->setParameterByClass(is_array($class) ? current($class) : $class, 'mob_id', null);

        return $url;
    }

    private function buildItem(string $title, string $icon_name, string $link): BulkyLink
    {
        return $this->ui_factory->link()->bulky(
            $this->buildIcon($title, $icon_name),
            $title,
            new URI(ILIAS_HTTP_PATH . '/' . $link)
        );
    }

    private function buildIcon(string $title, string $type): CustomIcon
    {
        return $this->icon_factory->custom(
            ilUtil::getImagePath("standard/icon_$type.svg"),
            $title
        );
    }

    /**
     * @param list<MailFolderData> $folders
     * @return list<MailFolderData>
     */
    private function filterUserFolders(array &$folders): array
    {
        $user_folders = [];
        $filtered_folders = [];

        foreach ($folders as $folder) {
            if ($folder->isUserFolder()) {
                $user_folders[] = $folder;
                continue;
            }

            $filtered_folders[] = $folder;
        }
        $folders = $filtered_folders;

        return $user_folders;
    }
}
