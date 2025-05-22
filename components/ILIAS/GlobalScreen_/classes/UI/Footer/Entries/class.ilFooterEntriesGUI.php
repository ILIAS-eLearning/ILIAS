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

use ILIAS\UI\Component\Input\Container\Form\Standard;
use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen_\UI\Translator;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepository;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepositoryDB;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesTable;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntryForm;
use ILIAS\GlobalScreen\UI\Footer\Groups\Group;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepository;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepositoryDB;
use ILIAS\GlobalScreen_\UI\UIHelper;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilFooterEntriesGUI: ilFooterGroupsGUI
 */
final class ilFooterEntriesGUI
{
    use Hasher;
    use UIHelper;

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
    public const GSFO_ID = 'gsfo_entry_id';
    private EntriesRepository $repository;
    private Factory $ui_factory;
    private ilCtrlInterface $ctrl;
    private ServerRequestInterface $request;

    public function __construct(
        private Container $dic,
        private Translator $translator,
        private ilObjFooterUIHandling $ui_handling,
        private Group $group,
        private GroupsRepository $groups_repository
    ) {
        $this->ui_factory = $this->dic->ui()->factory();
        $this->ctrl = $this->dic->ctrl();
        $this->request = $this->dic->http()->request();
        $this->repository = new EntriesRepositoryDB(
            $this->dic->database(),
            new ilFooterCustomGroupsProvider($dic)
        );
    }

    protected function index(): void
    {
        // Add new
        $components = [];
        if ($this->ui_handling->hasPermission('write')) {
            $components = $this->addButtons();
        }
        // Sync
        $this->repository->syncWithGlobalScreen(
            $this->dic->globalScreen()->collector()->footer()
        );
        // Table
        $table = new EntriesTable(
            $this->group,
            $this->repository,
            new TranslationsRepositoryDB($this->dic->database()),
            $this->translator
        );

        $this->ui_handling->out(
            $table->get(
                $this->ui_handling->getHereAsURI(self::CMD_SAVE_ORDER),
                $this->ui_handling->buildURI(
                    $this->ctrl->getLinkTargetByClass(
                        ilFooterTranslationGUI::class,
                        ilFooterTranslationGUI::CMD_DEFAULT
                    )
                )
            ),
            ...$components
        );
    }

    private function saveOrder(): void
    {
        foreach ($this->request->getParsedBody() as $hashed_id => $position) {
            $this->repository->updatePositionById($this->unhash($hashed_id), (int) $position);
        }
        $this->ctrl->redirect($this, self::CMD_DEFAULT);
    }

    protected function add(): void
    {
        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group
        );

        $target = $this->ctrl->getFormAction($this, self::CMD_CREATE);
        $this->ui_handling->outAsyncAsModal(
            $this->translator->translate('add', 'entries'),
            $target,
            $form->get($target)
        );
    }

    protected function addButtons(): array
    {
        $modal = $this->ui_factory->modal()->roundtrip(
            $this->translator->translate('add', 'entries'),
            null
        )->withAsyncRenderUrl(
            $this->ctrl->getLinkTarget($this, self::CMD_ADD)
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

    protected function saveCurrentEntry(): mixed
    {
        $id = $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0];
        $this->ui_handling->saveIdentificationsToRequest(
            $this,
            self::GSFO_ID,
            $id
        );
        return $id;
    }

    public function create(): void
    {
        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group
        );
        if ($form->store(
            $this->request,
            $this->ctrl->getFormAction($this, self::CMD_CREATE)
        )) {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        $this->ui_handling->out(
            $form->get(
                $this->ctrl->getFormAction($this, self::CMD_CREATE)
            )
        );
    }

    protected function edit(): void
    {
        $id = $this->saveCurrentEntry();
        $entry = $this->repository->get($id);

        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group,
            $entry
        );

        $target = $this->ctrl->getFormAction($this, self::CMD_UPDATE);
        $this->ui_handling->outAsyncAsModal(
            $this->translator->translate('edit', 'entries'),
            $target,
            $form->get($target)
        );
    }

    public function update(): void
    {
        $id = $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0];
        $entry = $this->repository->get($id);

        $form = new EntryForm(
            $this->repository,
            $this->translator,
            $this->group,
            $entry
        );
        $target = $this->ctrl->getFormAction($this, self::CMD_CREATE);
        if ($form->store(
            $this->request,
            $target
        )) {
            $this->ctrl->redirect($this, self::CMD_DEFAULT);
        }
        $this->ui_handling->out(
            $form->get($target)
        );
    }

    protected function toggleActivation(): void
    {
        foreach ($this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID) as $id) {
            $entry = $this->repository->get($id);
            $this->repository->store($entry->withActive(!$entry->isActive()));
        }

        $this->ui_handling->sendMessageAndRedirect(
            'success',
            $this->translator->translate('group_activation_toggled'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    protected function reset(): void
    {
        $this->repository->reset(
            $this->dic->globalScreen()->collector()->footer()
        );
        $this->ui_handling->sendMessageAndRedirect(
            'success',
            $this->translator->translate('reset_success'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    protected function confirmDelete(): void
    {
        $items = [];

        foreach ($this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID) as $id) {
            $entry = $this->repository->get($id);
            if ($entry === null) {
                continue;
            }
            if ($entry->isCore()) {
                $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                    $id,
                    $entry->getTitle(),
                    $this->translator->translate('info_not_deletable_core') .
                    $this->ui_handling->render($this->nok($this->ui_factory))
                );
                continue;
            }
            $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                $id,
                $entry->getTitle(),
                $this->ui_handling->render($this->ok($this->ui_factory))
            );
        }

        $this->ui_handling->outAsyncAsModal(
            $this->translator->translate('entry_delete'),
            $this->ctrl->getFormAction($this, 'delete'),
            ...$items
        );
    }

    private function delete(): void
    {
        foreach ($this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID) as $id) {
            $item = $this->repository->get($id);
            $this->repository->delete($item);
        }

        $this->ui_handling->sendMessageAndRedirect(
            'success',
            $this->translator->translate('entry_deleted'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    protected function selectMove(): void
    {
        $id = $this->saveCurrentEntry();
        $form = $this->getMoveForm();

        $this->ui_handling->outAsyncAsModal(
            $this->translator->translate('select_parent', 'entries'),
            $this->ctrl->getFormAction($this, 'move'),
            $form
        );
    }

    protected function getMoveForm(): Standard
    {
        $parents = [];

        foreach ($this->groups_repository->all() as $group) {
            $parents[$group->getId()] = $group->getTitle();
        }

        $factory = $this->ui_factory->input();
        return $factory
            ->container()
            ->form()
            ->standard(
                $this->ctrl->getFormAction($this, 'move'),
                [
                    'parent' => $factory
                        ->field()
                        ->select(
                            $this->translator->translate('parent', 'entries'),
                            $parents
                        )
                        ->withRequired(true)
                        ->withValue($this->group->getId())
                ]
            );
    }

    protected function move(): void
    {
        $new_parent = $this->getMoveForm()->withRequest($this->request)->getData()['parent'] ?? null;
        $id = $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0];
        $entry = $this->repository->get($id);
        $entry = $entry->withParent($new_parent);
        $this->repository->store($entry);
        $this->ui_handling->sendMessageAndRedirect(
            'success',
            $this->translator->translate('entry_moved'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    // HELPERS AND NEEDED IMPLEMENATIONS

    public function executeCommand(): void
    {
        $this->ui_handling->requireReadable();

        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd(self::CMD_DEFAULT);

        switch (strtolower($next_class)) {
            case strtolower(ilFooterTranslationGUI::class):
                $item = $this->repository->get(
                    $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0]
                );
                $back_target = null;
                if ($this->request->getQueryParams()['async'] ?? false) {
                    $back_target = $this->ui_handling->buildURI($this->ctrl->getLinkTarget($this, self::CMD_DEFAULT));
                }
                $translation = new ilFooterTranslationGUI(
                    $this->dic,
                    $this->translator,
                    $this->ui_handling,
                    $item,
                    $back_target
                );

                $this->ctrl->forwardCommand($translation);

                return;
            default:
                switch ($cmd) {
                    case self::CMD_ADD:
                    case self::CMD_CREATE:
                    case self::CMD_EDIT:
                    case self::CMD_UPDATE:
                    default:
                        $this->ui_handling->backToMainTab();
                        $this->ui_handling->requireWritable();
                        $this->$cmd();
                }
        }
    }

}
