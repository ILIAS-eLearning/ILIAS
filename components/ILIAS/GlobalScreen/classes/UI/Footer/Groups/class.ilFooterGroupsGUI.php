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

use ILIAS\GlobalScreen\GUI\Input\TokenContainer;
use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Flow\Command;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepositoryDB;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsTable;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupForm;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepositoryDB;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepository;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepositoryDB;
use ILIAS\GlobalScreen\GUI\AbstractPonsGUI;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\GlobalScreen\GUI\Hasher;
use ILIAS\GlobalScreen\GUI\I18n\SupportsTranslationGUI;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslatableItem;
use ILIAS\GlobalScreen\GUI\Input\Input;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilFooterGroupsGUI: ilObjFooterAdministrationGUI
 * @ilCtrl_Calls      ilFooterGroupsGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilFooterGroupsGUI: ILIAS\GlobalScreen\GUI\I18n\MultiLanguageGUI
 */
final class ilFooterGroupsGUI extends AbstractPonsGUI implements SupportsTranslationGUI
{
    use Hasher;

    /**
     * @var string
     */
    public const CMD_TOGGLE_ACTIVATION = 'toggleActivation';

    /**
     * @var string
     */
    public const CMD_ADD = 'add';

    /**
     * @var string
     */
    public const CMD_CREATE = 'create';

    /**
     * @var string
     */
    public const CMD_EDIT = 'edit';

    /**
     * @var string
     */
    public const CMD_UPDATE = 'update';

    /**
     * @var string
     */
    public const CMD_RESET = 'reset';

    /**
     * @var string
     */
    public const CMD_SAVE_ORDER = 'saveOrder';

    /**
     * @var string
     */
    public const GSFO_ID = 'gsfo_group_id';

    /**
     * @var string
     */
    public const CMD_CONFIRM_RESET = 'confirmReset';
    /**
     * @var string
     */
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    /**
     * @var string
     */
    public const CMD_DELETE = 'delete';
    private GroupsRepository $repository;
    private Factory $ui_factory;
    private ilCtrlInterface $ctrl;
    private ServerRequestInterface $request;

    private Container $dic;
    private TokenContainer $group_token;
    private Translator $translator;

    public function __construct(
        Pons $pons,
    ) {
        parent::__construct($pons);
        global $DIC;
        $this->dic = $DIC;
        $this->translator = $pons->i18n();
        $this->ui_factory = $this->dic->ui()->factory();
        $this->repository = new GroupsRepositoryDB(
            $this->dic->database(),
            new ilFooterCustomGroupsProvider($this->dic)
        );
        $this->group_token = $this->pons->in()->buildToken('group', 'id');
    }

    public function getTokensToKeep(): array
    {
        return [
            $this->group_token->token(),
        ];
    }

    public function getCurrentItem(): TranslatableItem
    {
        return $this->repository->get(
            $this->pons->in()->getFirstFromRequest($this->group_token)
        );
    }

    private function addButtons(): array
    {
        $modal = $this->ui_factory
            ->modal()
            ->roundtrip(
                $this->translator->translate('group_add'),
                null
            )
            ->withAsyncRenderUrl(
                (string) $this->pons->flow()->getHereAsURI(self::CMD_ADD)
            );

        $confirm_reset = $this->ui_factory
            ->prompt()
            ->standard(
                $this->pons->flow()->getHereAsURI(self::CMD_CONFIRM_RESET),
            );

        $this->dic
            ->toolbar()
            ->addComponent(
                $this->ui_factory
                    ->button()
                    ->primary(
                        $this->translator->translate('group_add'),
                        '#'
                    )
                    ->withOnClick($modal->getShowSignal())
                    ->withHelpTopics(
                        ...$this->ui_factory->helpTopics('gsfo_button_add')
                    )
            );
        $this->dic
            ->toolbar()
            ->addComponent(
                $this->ui_factory
                    ->button()
                    ->standard(
                        $this->translator->translate('reset_footer'),
                        '#'
                    )
                    ->withOnClick($confirm_reset->getShowSignal())
                    ->withHelpTopics(
                        ...$this->ui_factory->helpTopics('gsfo_button_reset')
                    )
            );

        return [$modal, $confirm_reset];
    }

    #[Command('read')]
    private function index(): void
    {
        // Add new
        $components = [];
        if ($this->pons->access()->hasUserPermissionTo('write')) {
            $components = $this->addButtons();
        }
        // Sync
        $this->repository->syncWithGlobalScreen(
            $this->dic->globalScreen()->collector()->footer()
        );

        // Table
        $table = new GroupsTable(
            $this->pons,
            $this->repository,
            $this->group_token
        );

        $this->pons->out()->out(
            $table->get(),
            ...$components
        );
    }

    #[Command('write')]
    private function confirmReset(): void
    {
        $this->pons->out()->outAsync(
            $this->ui_factory->prompt()->state()->show(
                $this->ui_factory->messageBox()->confirmation(
                    $this->translator->translate('confirm_reset')
                )->withButtons(
                    [
                        $this->ui_factory->button()->standard(
                            $this->translator->translate('reset'),
                            (string) $this->pons->flow()->getHereAsURI(self::CMD_RESET)
                        )
                    ]
                )
            )
        );
    }

    #[Command('write')]
    private function toggleActivation(): void
    {
        $from_request = $this->pons->in()->getAllFromRequest($this->group_token);

        if (($from_request[0] ?? null) === Input::ALL_OBJECTS) {
            $from_request = array_map(
                fn(TranslatableItem $item): string => $item->getId(),
                iterator_to_array($this->repository->all())
            );
        }

        foreach ($from_request as $id) {
            $group = $this->repository->get($id);
            if ($group === null) {
                continue;
            }
            $this->repository->store($group->withActive(!$group->isActive()));
        }

        $this->pons->out()->success($this->translator->translate('group_activation_toggled'));
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    private function confirmDelete(): void
    {
        $items = [];

        $nok = $this->pons->out()->nok();
        $ok = $this->pons->out()->ok();

        $from_request = $this->pons->in()->getAllFromRequest($this->group_token);

        if (($from_request[0] ?? null) === Input::ALL_OBJECTS) {
            $from_request = array_map(
                fn(TranslatableItem $item): string => $item->getId(),
                iterator_to_array($this->repository->all())
            );
        }

        foreach ($from_request as $id) {
            $group = $this->repository->get($id);
            $id = $this->hash($id);
            if ($group === null) {
                continue;
            }
            if ($group->isCore()) {
                $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                    $id,
                    $group->getTitle(),
                    $this->translator->translate('info_not_deletable_core') .
                    $this->pons->out()->ui()->renderer()->render(
                        $nok
                    )
                );
                continue;
            }
            if ($group->getItems() > 0) {
                $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                    $id,
                    $group->getTitle(),
                    $this->translator->translate('info_not_deletable_not_empty') .
                    $this->pons->out()->ui()->renderer()->render($nok)
                );
                continue;
            }

            $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                $id,
                $group->getTitle(),
                $this->pons->out()->ui()->renderer()->render($ok)
            );
        }

        $this->pons->out()->outAsyncAsModal(
            $this->pons->i18n()->translate('group_delete'),
            (string) $this->pons->flow()->getHereAsURI(self::CMD_DELETE),
            ...$items
        );
    }

    #[Command('write')]
    private function delete(): void
    {
        $from_request = $this->pons->in()->getAllFromRequest($this->group_token);

        if (($from_request[0] ?? null) === Input::ALL_OBJECTS) {
            $from_request = array_map(
                fn(TranslatableItem $item): string => $item->getId(),
                iterator_to_array($this->repository->all())
            );
        }

        $successful_deletions = 0;
        foreach ($from_request as $id) {
            $item = $this->repository->get($id);
            if ($item === null) {
                continue;
            }
            if ($item->isCore()) {
                continue;
            }
            $this->repository->delete($item);
            $successful_deletions++;
        }

        if ($successful_deletions === 0) {
            $this->pons->out()->error($this->translator->translate('entry_deleted_failed'), true);
            $this->pons->flow()->redirect(self::CMD_DEFAULT);
            return;
        }
        $this->pons->out()->success($this->translator->translate('entry_deleted'), true);
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    private function add(): void
    {
        $form = new GroupForm(
            $this->repository,
            $this->translator
        );

        $action = (string) $this->pons->flow()->getHereAsURI(self::CMD_CREATE);

        $this->pons->out()->outAsyncAsModal(
            $this->translator->translate('group_add'),
            $action,
            $form->get($action)
        );
    }

    #[Command('write')]
    public function create(): void
    {
        $form = new GroupForm(
            $this->repository,
            $this->translator
        );
        $target = (string) $this->pons->flow()->getHereAsURI(self::CMD_CREATE);
        if ($form->store(
            $this->pons->in()->request(),
            $target
        )) {
            $this->pons->flow()->redirect(self::CMD_DEFAULT);
        }
        $this->pons->out()->out(
            $form->get(
                $target
            )
        );
    }

    #[Command('write')]
    private function edit(): void
    {
        $id = $this->pons->in()->getFirstFromRequest($this->group_token);
        $this->pons->in()->keepTokens($this);

        $group = $this->repository->get($id);

        $form = new GroupForm(
            $this->repository,
            $this->translator,
            $group
        );

        $target = (string) $this->pons->flow()->getHereAsURI(self::CMD_UPDATE);
        $this->pons->out()->outAsyncAsModal(
            $this->translator->translate('group_edit'),
            $target,
            $form->get($target)
        );
    }

    #[Command('write')]
    public function update(): void
    {
        $id = $this->pons->in()->getFirstFromRequest($this->group_token);

        $group = $this->repository->get($id);

        $form = new GroupForm(
            $this->repository,
            $this->translator,
            $group
        );

        $target = (string) $this->pons->flow()->getHereAsURI(self::CMD_EDIT);
        if ($form->store(
            $this->pons->in()->request(),
            $target
        )) {
            $this->pons->flow()->redirect(self::CMD_DEFAULT);
        }
        $this->pons->out()->out(
            $form->get(
                $target
            )
        );
    }

    #[Command('write')]
    private function reset(): never
    {
        $this->repository->reset(
            $this->dic->globalScreen()->collector()->footer()
        );
        $entries_repo = new EntriesRepositoryDB($this->dic->database(), new ilFooterCustomGroupsProvider($this->dic));
        $entries_repo->reset($this->dic->globalScreen()->collector()->footer());

        $translations = new TranslationsRepositoryDB($this->dic->database());
        $translations->reset();

        $this->pons->out()->success($this->translator->translate('reset_success'));
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    private function saveOrder(): void
    {
        foreach ($this->pons->in()->request()->getParsedBody() as $hashed_id => $position) {
            $item = $this->repository->get($this->unhash($hashed_id));
            $item = $item->withPosition((int) $position);
            $this->repository->store($item);
        }
        $this->pons->out()->success($this->pons->i18n()->translate('order_saved'));
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    public function executeCommand(): bool
    {
        if ($this->pons->handle(ilObjFooterAdministrationGUI::TAB_GROUPS)) {
            return true;
        }
        match ($this->pons->flow()->getCommand(self::CMD_DEFAULT)) {
            self::CMD_DEFAULT => $this->index(),
            self::CMD_ADD => $this->add(),
            self::CMD_CREATE => $this->create(),
            self::CMD_EDIT => $this->edit(),
            self::CMD_UPDATE => $this->update(),
            self::CMD_CONFIRM_RESET => $this->confirmReset(),
            self::CMD_CONFIRM_DELETE => $this->confirmDelete(),
            self::CMD_DELETE => $this->delete(),
            self::CMD_TOGGLE_ACTIVATION => $this->toggleActivation(),
            self::CMD_RESET => $this->reset(),
            self::CMD_SAVE_ORDER => $this->saveOrder(),
            default => $this->pons->out()->outString(
                'Unknown command: ' . $this->pons->flow()->getCommand(self::CMD_DEFAULT)
            ),
        };
        return true;
    }

}
