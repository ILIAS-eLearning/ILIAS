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
 ********************************************************************
 */

declare(strict_types=1);

use ILIAS\components\OrgUnit\ARHelper\BaseCommands;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table;

/**
 * Class ilOrgUnitPositionGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionGUI extends BaseCommands
{
    use ILIAS\Repository\BaseGUIRequest;

    public const SUBTAB_SETTINGS = 'settings';
    public const SUBTAB_PERMISSIONS = 'obj_orgunit_positions';
    public const CMD_CONFIRM_DELETION = 'confirmDeletion';
    public const CMD_ASSIGN = 'assign';
    protected ilToolbarGUI $toolbar;
    protected \ILIAS\UI\Component\Link\Factory $link_factory;
    protected \ilOrgUnitPositionDBRepository $positionRepo;
    protected \ilOrgUnitUserAssignmentDBRepository $assignmentRepo;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected DataFactory $data_factory;

    protected array $query_namespace;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $row_id_token;

    protected ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $query;
    protected ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $post;
    protected ServerRequestInterface $request;

    public function __construct()
    {
        $dic = ilOrgUnitLocalDIC::dic();
        $this->positionRepo = $dic["repo.Positions"];
        $this->assignmentRepo = $dic["repo.UserAssignments"];

        $to_int = $dic['refinery']->kindlyTo()->int();
        $ref_id = $dic['query']->retrieve('ref_id', $to_int);
        $this->link_factory = $dic['ui.factory']->link();

        parent::__construct();

        global $DIC;
        $this->toolbar = $DIC->toolbar();

        $this->initRequest(
            $DIC->http(),
            $dic['refinery']
        );

        if (!ilObjOrgUnitAccess::_checkAccessPositions($ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }

        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];

        $this->data_factory = new DataFactory();
        $this->query = $DIC->http()->wrapper()->query();
        $this->post = $DIC->http()->wrapper()->post();
        $this->request = $DIC->http()->request();

        $here_uri = $this->data_factory->uri(
            $this->request->getUri()->__toString()
        );
        $this->url_builder = new URLBuilder($here_uri);
        $this->query_namespace = ['orgu', 'posedit'];
        list($url_builder, $action_token, $row_id_token) =
            $this->url_builder->acquireParameters($this->query_namespace, "action", "posid");
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;

    }

    protected function getPossibleNextClasses(): array
    {
        return array(
            ilOrgUnitDefaultPermissionGUI::class,
            ilOrgUnitUserAssignmentGUI::class,
        );
    }

    protected function getActiveTabId(): string
    {
        return ilObjOrgUnitGUI::TAB_POSITIONS;
    }

    protected function index(): void
    {
        $url = $this->getSinglePosLinkTarget(self::CMD_ADD, 0);
        $link = $this->link_factory->standard(
            $this->lng->txt('add_position'),
            $url
        );
        $this->toolbar->addComponent($link);

        $table = $this->getTable()->withRequest($this->request);
        $this->tpl->setContent($this->ui_renderer->render($table));
    }

    protected function getTable(): Table\Data
    {
        $columns = [
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt("title")),
            'description' => $this->ui_factory->table()->column()->text($this->lng->txt("description")),
            'authorities' => $this->ui_factory->table()->column()->status($this->lng->txt("authorities")),

        ];

        $actions = [
            'edit' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $this->url_builder->withParameter($this->action_token, "edit"),
                $this->row_id_token
            ),
            'delete' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter($this->action_token, "confirmDeletion"),
                $this->row_id_token
            ),
        ];

        return $this->ui_factory->table()
            ->data('', $columns, $this->positionRepo)
            ->withId('orgu_positions')
            ->withActions($actions);
    }

    protected function add(): void
    {
        $position = $this->positionRepo->create();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $this->tpl->setContent($form->getHTML());
    }

    protected function create(): void
    {
        $this->redirectIfCancelled();
        $form = new ilOrgUnitPositionFormGUI($this, $this->positionRepo->create());
        if ($form->saveObject() === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_position_created'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function edit(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_SETTINGS);
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function update(): void
    {
        $this->redirectIfCancelled();
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->setValuesByPost();
        if ($form->saveObject() === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_position_updated'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function redirectIfCancelled() {
        if($this->post->has('cmd')) {
            $cmd = $this->post->retrieve(
                'cmd',
                $this->refinery->custom()->transformation(
                    fn($v) => array_key_first($v)
                )
            );
            if($cmd === SELF::CMD_CANCEL) {
                $url = $this->url_builder
                    ->withParameter($this->action_token, self::CMD_INDEX)
                    ->buildURI()
                    ->__toString();
                $this->ctrl->redirectToURL($url);
            }
        }
    }

    protected function assign(): void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }

        $employee_position = $this->positionRepo->getSingle(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE, 'core_identifier');
        $assignments = $this->assignmentRepo->getByPosition($position->getId());
        foreach ($assignments as $assignment) {
            $this->assignmentRepo->store($assignment->withPositionId($employee_position->getId()));
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_assignment_to_employee_done'), true);
    }

    protected function confirmDeletion(): void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }
        $this->lng->loadLanguageModule('orgu');
        $position_string = $this->lng->txt("position") . ": ";
        $authority_string = $this->lng->txt("authorities") . ": ";
        $user_string = $this->lng->txt("user_assignments") . ": ";

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $confirmation->setConfirm($this->lng->txt(self::CMD_DELETE), self::CMD_DELETE);
        $confirmation->setHeaderText($this->lng->txt('msg_confirm_deletion'));
        $confirmation->addItem(self::AR_ID, (string) $position->getId(), $position_string
            . $position->getTitle());
        // Authorities
        $authority_string .= implode(", ", $this->getAuthorityDescription($position->getAuthorities()));
        $confirmation->addItem('authorities', '', $authority_string);

        // Amount uf user-assignments
        $userIdsOfPosition = $this->assignmentRepo->getUsersByPosition($position->getId());
        $ilOrgUnitUserQueries = new ilOrgUnitUserQueries();
        $usersOfPosition = $ilOrgUnitUserQueries->findAllUsersByUserIds($userIdsOfPosition);
        $userNames = $ilOrgUnitUserQueries->getAllUserNames($usersOfPosition);

        $confirmation->addItem('users', '', $user_string . implode(', ', $userNames));

        $checkbox_assign_users = new ilCheckboxInputGUI('', 'assign_users');
        $checkbox_assign_users->setChecked(true);
        $checkbox_assign_users->setValue('1');
        $checkbox_assign_users->setOptionTitle('Assign affected users to employee role');
        $confirmation->addItem('assign_users', '', $checkbox_assign_users->render());

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function delete(): void
    {
        if ($_POST['assign_users']) {
            $this->assign();
        }
        $position = $this->getPositionFromRequest();
        $this->positionRepo->delete($position->getId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function getPositionFromRequest(): ?ilOrgUnitPosition
    {
        if($this->query->has($this->row_id_token->getName())) {
            $id = $this->query->retrieve(
                $this->row_id_token->getName(),
                $this->refinery->custom()->transformation(fn($v) => (int)array_shift($v))
            );
            return $this->positionRepo->getSingle($id, 'id');
        }
        throw new \Exception('no position from request');
        //return $this->positionRepo->getSingle($this->int(self::AR_ID), 'id');
    }

    protected function getPosIdFromQuery(): int
    {
        if($this->query->has($this->row_id_token->getName())) {
            return $this->query->retrieve(
                $this->row_id_token->getName(),
                $this->refinery->custom()->transformation(fn($v) => (int)array_shift($v))
            );
        }
        throw new \Exception('no position-id in query');
    }

    public function getSinglePosLinkTarget(string $action, int $pos_id = null): string
    {
        $target_id = $pos_id !== null ? [$pos_id] : [$this->getPosIdFromQuery()];
        return $this->url_builder
            ->withParameter($this->row_id_token, $target_id)
            ->withParameter($this->action_token, $action)
            ->buildURI()->__toString();
    }

    public function addSubTabs(): void
    {
        $this->ctrl->saveParameter($this, 'arid');
        $this->ctrl->saveParameterByClass(ilOrgUnitDefaultPermissionGUI::class, 'arid');
        $this->pushSubTab(
            self::SUBTAB_SETTINGS
            $this->getSinglePosLinkTarget(self::CMD_EDIT)
        );
        $this->pushSubTab(
            'TODO: ' .self::SUBTAB_PERMISSIONS
            $this->getSinglePosLinkTarget(self::CMD_INDEX)
        );

        $this->pushSubTab(self::SUBTAB_PERMISSIONS, $this->ctrl
                                                         ->getLinkTargetByClass(
                                                             ilOrgUnitDefaultPermissionGUI::class,
                                                             self::CMD_INDEX
                                                         ));
    }

    /**
     * Returns descriptions for authorities as an array of strings
     *
     * @param ilOrgUnitAuthority[] $authorities
     */
    private function getAuthorityDescription(array $authorities): array
    {
        $lang = $this->lng;
        $lang->loadLanguageModule('orgu');
        $lang_keys = array(
            'in',
            'over',
            'scope_' . ilOrgUnitAuthority::SCOPE_SAME_ORGU,
            'scope_' . ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS,
            'over_' . ilOrgUnitAuthority::OVER_EVERYONE,
        );
        $t = [];
        foreach ($lang_keys as $key) {
            $t[$key] = $lang->txt($key);
        }

        $authority_description = [];
        foreach ($authorities as $authority) {
            switch ($authority->getOver()) {
                case ilOrgUnitAuthority::OVER_EVERYONE:
                    $over_txt = $t["over_" . $authority->getOver()];
                    break;
                default:
                    $over_txt = $this->positionRepo
                        ->getSingle($authority->getOver(), 'id')
                        ->getTitle();
                    break;
            }

            $authority_description[] = " " . $t["over"] . " " . $over_txt . " " . $t["in"] . " " . $t["scope_" . $authority->getScope()];
        }

        return $authority_description;
    }
}
