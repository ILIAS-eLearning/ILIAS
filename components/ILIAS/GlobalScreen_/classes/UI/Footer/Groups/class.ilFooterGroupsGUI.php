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

use ILIAS\UI\Factory;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen_\UI\Translator;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepositoryDB;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsTable;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupForm;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\GlobalScreen\UI\Footer\Entries\EntriesRepositoryDB;
use ILIAS\GlobalScreen_\UI\UIHelper;
use ILIAS\GlobalScreen\UI\Footer\Groups\GroupsRepository;
use ILIAS\GlobalScreen\UI\Footer\Translation\TranslationsRepositoryDB;

/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 *
 * @ilCtrl_isCalledBy ilFooterGroupsGUI: ilObjFooterAdministrationGUI
 * @ilCtrl_Calls      ilFooterGroupsGUI: ilPermissionGUI
 */
final class ilFooterGroupsGUI
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
    public const GSFO_ID = 'gsfo_group_id';
    /**
     * @var string
     */
    private const CMD_CONFIRM_RESET = 'confirmReset';
    private GroupsRepository $repository;
    private Factory $ui_factory;
    private ilCtrlInterface $ctrl;
    private ServerRequestInterface $request;

    public function __construct(
        private Container $dic,
        private Translator $translator,
        private ilObjFooterUIHandling $ui_handling
    ) {
        $this->ui_factory = $this->dic->ui()->factory();
        $this->ctrl = $this->dic->ctrl();
        $this->request = $this->dic->http()->request();
        $this->repository = new GroupsRepositoryDB(
            $this->dic->database(),
            new ilFooterCustomGroupsProvider($dic)
        );
    }

    protected function addButtons(): array
    {
        $modal = $this->ui_factory->modal()->roundtrip(
            $this->translator->translate('group_add'),
            null
        )->withAsyncRenderUrl(
            $this->ctrl->getLinkTarget($this, self::CMD_ADD)
        );

        $confirm_reset = $this->ui_factory->prompt()->standard(
            $this->ui_handling->getHereAsURI(self::CMD_CONFIRM_RESET),
        );

        $this->dic->toolbar()->addComponent(
            $this->ui_factory
                ->button()
                ->primary(
                    $this->translator->translate('group_add'),
                    '#' //$this->ctrl->getLinkTarget($this, self::CMD_ADD)
                )
                ->withOnClick($modal->getShowSignal())
            //                ->withHelpTopics(...$this->ui_factory->helpTopics('gsfo_button_add')) // TODO reenable after ilHelp is working again
        );
        $this->dic->toolbar()->addComponent(
            $this->ui_factory
                ->button()
                ->standard(
                    $this->translator->translate('reset_footer'),
                    '#' //'$this->ctrl->getLinkTarget($this, self::CMD_RESET)
                )
                ->withOnClick($confirm_reset->getShowSignal())
            //                ->withHelpTopics(...$this->ui_factory->helpTopics('gsfo_button_reset')) // TODO reenable after ilHelp is working again
        );

        return [$modal, $confirm_reset];
    }

    protected function confirmReset(): void
    {
        $this->ui_handling->outAsync(
            $this->ui_factory->prompt()->state()->show(
                $this->ui_factory->messageBox()->confirmation(
                    $this->translator->translate('confirm_reset')
                )->withButtons(
                    [
                        $this->ui_factory->button()->standard(
                            $this->translator->translate('reset'),
                            $this->ctrl->getLinkTarget($this, self::CMD_RESET)
                        )
                    ]
                )
            )
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
        $table = new GroupsTable(
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

    protected function confirmDelete(): void
    {
        $items = [];

        foreach ($this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID) as $id) {
            $group = $this->repository->get($id);
            if ($group === null) {
                continue;
            }
            if ($group->isCore()) {
                $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                    $id,
                    $group->getTitle(),
                    $this->translator->translate('info_not_deletable_core') .
                    $this->ui_handling->render($this->nok($this->ui_factory))
                );
                continue;
            }
            if ($group->getItems() > 0) {
                $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                    $id,
                    $group->getTitle(),
                    $this->translator->translate('info_not_deletable_not_empty') .
                    $this->ui_handling->render($this->nok($this->ui_factory))
                );
                continue;
            }
            $items[] = $this->ui_factory->modal()->interruptiveItem()->keyValue(
                $id,
                $group->getTitle(),
                $this->ui_handling->render($this->ok($this->ui_factory))
            );
        }

        $this->ui_handling->outAsyncAsModal(
            $this->translator->translate('group_delete'),
            $this->ctrl->getFormAction($this, 'delete'),
            ...$items
        );
    }

    private function delete(): void
    {
        foreach ($this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID) as $id) {
            $group = $this->repository->get($id);
            $this->repository->delete($group);
        }

        $this->ui_handling->sendMessageAndRedirect(
            'success',
            $this->translator->translate('group_deleted'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    private function saveOrder(): void
    {
        foreach ($this->request->getParsedBody() as $hashed_id => $position) {
            $this->repository->updatePositionById($this->unhash($hashed_id), (int) $position);
        }
        $this->ui_handling->sendMessageAndRedirect(
            'success',
            $this->translator->translate('order_saved'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    protected function add(): void
    {
        $form = new GroupForm(
            $this->repository,
            $this->translator
        );

        $action = $this->ctrl->getFormAction($this, self::CMD_CREATE);

        $this->ui_handling->outAsyncAsModal(
            $this->translator->translate('group_add'),
            $action,
            $form->get($action)
        );
    }

    public function create(): void
    {
        $form = new GroupForm(
            $this->repository,
            $this->translator
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
        $id = $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0];
        $this->ui_handling->saveIdentificationsToRequest(
            $this,
            self::GSFO_ID,
            $id
        );
        $group = $this->repository->get($id);

        $form = new GroupForm(
            $this->repository,
            $this->translator,
            $group
        );

        $target = $this->ctrl->getFormAction($this, self::CMD_UPDATE);
        $this->ui_handling->outAsyncAsModal(
            $this->translator->translate('group_edit'),
            $target,
            $form->get($target)
        );
    }

    public function update(): void
    {
        $id = $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0];
        $group = $this->repository->get($id);

        $form = new GroupForm(
            $this->repository,
            $this->translator,
            $group
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

    protected function toggleActivation(): void
    {
        foreach ($this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID) as $id) {
            $group = $this->repository->get($id);
            $this->repository->store($group->withActive(!$group->isActive()));
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
        $entries_repo = new EntriesRepositoryDB($this->dic->database(), new ilFooterCustomGroupsProvider($this->dic));
        $entries_repo->reset($this->dic->globalScreen()->collector()->footer());

        $translations = new TranslationsRepositoryDB($this->dic->database());
        $translations->reset();

        $this->ui_handling->sendMessageAndRedirect(
            'success',
            $this->translator->translate('reset_success'),
            $this->ctrl->getLinkTarget($this, self::CMD_DEFAULT)
        );
    }

    protected function editEntries(): void
    {
        $id = $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0];
        $this->ui_handling->saveIdentificationsToRequest(
            ilFooterEntriesGUI::class,
            self::GSFO_ID,
            $id
        );
        $this->ctrl->redirectByClass(ilFooterEntriesGUI::class);
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
            case strtolower(ilFooterEntriesGUI::class):
                $id = $this->ui_handling->getIdentificationsFromRequest(self::GSFO_ID)[0];
                $this->ui_handling->saveIdentificationsToRequest(
                    ilFooterEntriesGUI::class,
                    self::GSFO_ID,
                    $id
                );
                $group = $this->repository->get($id);

                $this->ctrl->forwardCommand(
                    new ilFooterEntriesGUI(
                        $this->dic,
                        $this->translator,
                        $this->ui_handling,
                        $group,
                        $this->repository
                    )
                );
                return;
            default:
                switch ($cmd) {
                    case self::CMD_DEFAULT:
                        $this->ui_handling->requireWritable();
                        $this->index();
                        break;
                    case self::CMD_ADD:
                    case self::CMD_CREATE:
                    case self::CMD_EDIT:
                    case self::CMD_UPDATE:
                    default:
                        $this->ui_handling->backToMainTab();
                        $this->ui_handling->requireWritable();
                        $this->$cmd();
                        break;
                }
        }
    }

}
