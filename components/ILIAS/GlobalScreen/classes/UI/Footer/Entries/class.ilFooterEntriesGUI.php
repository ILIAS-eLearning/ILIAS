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

use ILIAS\GlobalScreen\GUI\I18n\Translator;
use ILIAS\GlobalScreen\GUI\Input\TokenContainer;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepository;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepositoryDB;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesTable;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntryForm;
use ILIAS\GlobalScreen\UI\Footer\Groups\Group;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\GUI\AbstractPonsGUI;
use ILIAS\GlobalScreen\GUI\I18n\SupportsTranslationGUI;
use ILIAS\GlobalScreen\GUI\Pons;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepositoryDB;
use ILIAS\GlobalScreen\GUI\Flow\Command;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslatableItem;
use ILIAS\GlobalScreen\GUI\Input\Input;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilFooterEntriesGUI: ilFooterGroupsGUI
 * @ilCtrl_Calls      ilFooterEntriesGUI: ILIAS\GlobalScreen\GUI\I18n\MultiLanguageGUI
 */
final class ilFooterEntriesGUI extends AbstractPonsGUI implements SupportsTranslationGUI
{
    use Hasher;

    /**
     * @var string
     */
    public const CMD_DEFAULT = 'index';
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
    public const CMD_CONFIRM_DELETE = 'confirmDelete';
    /**
     * @var string
     */
    public const CMD_DELETE = 'delete';
    /**
     * @var string
     */
    public const GSFO_ID = 'gsfo_entry_id';
    /**
     * @var string
     */
    public const CMD_TOGGLE_ACTIVATION = 'toggleActivation';
    private EntriesRepository $repository;
    private Factory $ui_factory;
    private ilCtrlInterface $ctrl;
    private ServerRequestInterface $request;
    private Translator $translator;

    private Container $dic;
    private TokenContainer $group_token;
    private TokenContainer $entry_token;
    private ?Group $group;

    public function __construct(
        Pons $pons,
    ) {
        parent::__construct($pons);
        global $DIC;
        $this->translator = $pons->i18n();
        $this->dic = $DIC;
        $this->ui_factory = $this->dic->ui()->factory();

        $group_repository = new GroupsRepositoryDB(
            $this->dic->database(),
            new ilFooterCustomGroupsProvider($DIC)
        );

        $this->repository = new EntriesRepositoryDB(
            $this->dic->database(),
            new ilFooterCustomGroupsProvider($DIC)
        );
        $this->group_token = $this->pons->in()->buildToken('group', 'id');
        $this->entry_token = $this->pons->in()->buildToken('entry', 'id');
        $this->group = $group_repository->get(
            $this->pons->in()->getFirstFromRequest($this->group_token)
        );
        $this->pons->in()->keep($this->group_token);
    }

    public function getTokensToKeep(): array
    {
        return [
            $this->group_token->token(),
            $this->entry_token->token(),
        ];
    }

    public function getCurrentItem(): TranslatableItem
    {
        return $this->repository->get(
            $this->pons->in()->getFirstFromRequest($this->entry_token->token())
        );
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
        $table = new EntriesTable(
            $this->pons,
            $this->group,
            $this->repository,
            $this->entry_token
        );

        $this->pons->out()->out(
            $table->get(),
            ...$components
        );
    }

    #[Command('write')]
    private function add(): void
    {
        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group
        );

        $target = $this->pons->flow()->getHereAsURI(self::CMD_CREATE);
        $this->pons->out()->outAsyncAsModal(
            $this->translator->translate('add', 'entries'),
            $target,
            $form->get((string) $target)
        );
    }

    private function addButtons(): array
    {
        $modal = $this->ui_factory->modal()->roundtrip(
            $this->translator->translate('add', 'entries'),
            null
        )->withAsyncRenderUrl(
            (string) $this->pons->flow()->getHereAsURI(self::CMD_ADD)
        );

        $this->dic->toolbar()->addComponent(
            $this->ui_factory
                ->button()
                ->primary(
                    $this->translator->translate('add', 'entries'),
                    '#'
                )
                ->withOnClick($modal->getShowSignal())
        );

        return [$modal];
    }

    private function saveCurrentEntry(): mixed
    {
        $id = $this->pons->in()->getFirstFromRequest($this->entry_token);
        $this->pons->in()->keep($this->entry_token);

        return $id;
    }

    #[Command('write')]
    public function create(): void
    {
        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group
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
        $id = $this->saveCurrentEntry();
        $entry = $this->repository->get($id);

        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group,
            $entry
        );

        $target = (string) $this->pons->flow()->getHereAsURI(self::CMD_UPDATE);
        $this->pons->out()->outAsyncAsModal(
            $this->translator->translate('edit', 'entries'),
            $target,
            $form->get($target)
        );
    }

    #[Command('write')]
    public function update(): void
    {
        $id = $this->pons->in()->getFirstFromRequest($this->entry_token);
        $entry = $this->repository->get($id);

        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group,
            $entry
        );
        $target = (string) $this->pons->flow()->getHereAsURI(self::CMD_CREATE);
        if ($form->store(
            $this->pons->in()->request(),
            $target
        )) {
            $this->pons->flow()->redirect(self::CMD_DEFAULT);
        }
        $this->pons->out()->out(
            $form->get($target)
        );
    }

    #[Command('write')]
    private function toggleActivation(): void
    {
        $from_request = $this->pons->in()->getAllFromRequest($this->entry_token);

        if (($from_request[0] ?? null) === Input::ALL_OBJECTS) {
            $from_request = array_map(
                fn(TranslatableItem $item): string => $item->getId(),
                iterator_to_array($this->repository->all())
            );
        }

        foreach ($from_request as $id) {
            $entry = $this->repository->get($id);
            if ($entry === null) {
                continue;
            }
            $this->repository->store($entry->withActive(!$entry->isActive()));
        }

        $this->pons->out()->success($this->translator->translate('group_activation_toggled'), true);
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

    #[Command('write')]
    private function confirmDelete(): void
    {
        $items = [];

        $from_request = $this->pons->in()->getAllFromRequest($this->entry_token);

        if (($from_request[0] ?? null) === Input::ALL_OBJECTS) {
            $from_request = array_map(
                fn(TranslatableItem $item): string => $item->getId(),
                iterator_to_array($this->repository->all())
            );
        }

        foreach ($from_request as $id) {
            $entry = $this->repository->get($id);
            if ($entry === null) {
                continue;
            }
            if ($entry->isCore()) {
                $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                    $id,
                    $entry->getTitle(),
                    $this->translator->translate('info_not_deletable_core') .
                    $this->pons->out()->render($this->pons->out()->nok())
                );
                continue;
            }
            $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                $this->hash($entry->getId()),
                $entry->getTitle(),
                $this->pons->out()->render($this->pons->out()->ok())
            );
        }

        $this->pons->out()->outAsyncAsModal(
            $this->translator->translate('entry_delete'),
            $this->pons->flow()->getHereAsURI(self::CMD_DELETE),
            ...$items
        );
    }

    #[Command('write')]
    private function delete(): void
    {
        $from_request = $this->pons->in()->getAllFromRequest($this->entry_token);

        if (($from_request[0] ?? null) === Input::ALL_OBJECTS) {
            $from_request = array_map(
                fn(TranslatableItem $item): string => $item->getId(),
                iterator_to_array($this->repository->all())
            );
        }

        foreach ($from_request as $id) {
            $item = $this->repository->get($id);
            if ($item === null) {
                continue;
            }
            if ($item->isCore()) {
                continue;
            }
            $this->repository->delete($item);
        }

        $this->pons->out()->success($this->translator->translate('entry_deleted'), true);
        $this->pons->flow()->redirect(self::CMD_DEFAULT);
    }

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
        if ($this->pons->handle(ilObjFooterAdministrationGUI::TAB_SUB_ITEMS)) {
            return true;
        }
        match ($this->pons->flow()->getCommand(self::CMD_DEFAULT)) {
            self::CMD_DEFAULT => $this->index(),
            self::CMD_ADD => $this->add(),
            self::CMD_CREATE => $this->create(),
            self::CMD_EDIT => $this->edit(),
            self::CMD_UPDATE => $this->update(),
            self::CMD_CONFIRM_DELETE => $this->confirmDelete(),
            self::CMD_DELETE => $this->delete(),
            self::CMD_TOGGLE_ACTIVATION => $this->toggleActivation(),
            self::CMD_SAVE_ORDER => $this->saveOrder(),
            default => $this->pons->out()->outString(
                'Unknown command: ' . $this->pons->flow()->getCommand(self::CMD_DEFAULT)
            ),
        };
        return true;
    }

}
