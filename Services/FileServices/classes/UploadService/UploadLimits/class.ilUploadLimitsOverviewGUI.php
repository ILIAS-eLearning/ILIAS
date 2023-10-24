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
 */

declare(strict_types=1);

use ILIAS\HTTP\Services as HttpServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * @author            Lukas Zehnder <lukas@sr.solutions>
 *
 * @ilCtrl_IsCalledBy ilUploadLimitsOverviewGUI: ilObjFileServicesGUI
 */
class ilUploadLimitsOverviewGUI
{
    public const CMD_INDEX = 'index';
    public const CMD_ADD_UPLOAD_POLICY = 'addUploadPolicy';
    public const CMD_EDIT_UPLOAD_POLICY = 'editUploadPolicy';
    public const CMD_SAVE_UPLOAD_POLICY = 'saveUploadPolicy';
    public const CMD_DELETE_UPLOAD_POLICY = 'deleteUploadPolicy';

    protected ilCtrlInterface $ctrl;
    protected ilObjUser $current_user;
    protected ilDBInterface $db;
    protected HttpServices $http;
    protected ilLanguage $language;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilRbacReview $rbac_review;
    protected RefineryFactory $refinery;
    protected ilToolbarGUI $toolbar;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected UploadPolicyDBRepository $upload_policy_db_repository;
    protected ilTabsGUI $tabs;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->current_user = $DIC->user();
        $this->db = $DIC->database();
        $this->http = $DIC->http();
        $this->language = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->rbac_review = $DIC->rbac()->review();
        $this->refinery = $DIC->refinery();
        $this->toolbar = $DIC->toolbar();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->tabs = $DIC->tabs();

        $this->upload_policy_db_repository = new UploadPolicyDBRepository($this->db);
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getCmd(self::CMD_INDEX)) {
            case self::CMD_ADD_UPLOAD_POLICY:
                $this->gotoUploadPolicyForm();
                break;
            case self::CMD_EDIT_UPLOAD_POLICY:
                $this->gotoUploadPolicyForm(true);
                break;
            case self::CMD_SAVE_UPLOAD_POLICY:
                $this->saveUploadPolicy();
                break;
            case self::CMD_DELETE_UPLOAD_POLICY:
                $this->deleteUploadPolicy();
                break;
            case self::CMD_INDEX:
            default:
                $this->index();
                break;
        }
    }

    protected function index(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->button()->primary(
                $this->language->txt("add_upload_policy"),
                $this->ctrl->getLinkTargetByClass(self::class, self::CMD_ADD_UPLOAD_POLICY)
            )
        );

        $policies_table = new UploadPoliciesTableUI(
            $this->upload_policy_db_repository,
            $this->ctrl,
            $this->http,
            $this->language,
            $this->main_tpl,
            $this->rbac_review,
            $this->refinery,
            $this->ui_factory,
            $this->ui_renderer,
        );
        $this->main_tpl->setContent(
            $this->ui_renderer->render($policies_table->getComponents())
        );
    }

    protected function gotoUploadPolicyForm(bool $is_update = false): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->language->txt('back'),
            $this->ctrl->getLinkTargetByClass(self::class, self::CMD_INDEX)
        );

        $policy_form_ui = new UploadPolicyFormUI(
            $this->upload_policy_db_repository,
            $this->ctrl,
            $this->http,
            $this->language,
            $this->rbac_review,
            $this->refinery,
            $this->ui_factory
        );

        $form_panel_title = ($is_update) ?
            $this->language->txt('edit_upload_policy') :
            $this->language->txt('add_upload_policy');

        $this->main_tpl->setContent(
            $this->ui_renderer->render(
                $this->ui_factory->panel()->standard(
                    $form_panel_title,
                    [$policy_form_ui->getForm()]
                )
            )
        );
    }

    protected function saveUploadPolicy(): void
    {
        $policy_form_ui = new UploadPolicyFormUI(
            $this->upload_policy_db_repository,
            $this->ctrl,
            $this->http,
            $this->language,
            $this->rbac_review,
            $this->refinery,
            $this->ui_factory
        );
        $policy_form = $policy_form_ui->getForm();
        $policy_form = $policy_form->withRequest($this->http->request());
        $data = $policy_form->getData();

        if ($data !== null) {
            $date_now = new DateTimeImmutable();
            $policy_id = null;
            if (!empty($data[UploadPolicyFormUI::HIDDEN_INPUT_POLICY_ID])) {
                $policy_id = (int) $data[UploadPolicyFormUI::HIDDEN_INPUT_POLICY_ID];
            }

            $upload_policy = new UploadPolicy(
                $policy_id,
                $data[UploadPolicyFormUI::INPUT_SECTION_GENERAL][UploadPolicyFormUI::INPUT_FIELD_TITLE],
                $data[UploadPolicyFormUI::INPUT_SECTION_GENERAL][UploadPolicyFormUI::INPUT_FIELD_UPLOAD_LIMIT],
                $data[UploadPolicyFormUI::INPUT_SECTION_AUDIENCE][UploadPolicyFormUI::INPUT_FIELD_AUDIENCE_DATA],
                $data[UploadPolicyFormUI::INPUT_SECTION_AUDIENCE][UploadPolicyFormUI::INPUT_FIELD_AUDIENCE_TYPE],
                UploadPolicy::SCOPE_DEFINITION_GLOBAL,
                $data[UploadPolicyFormUI::INPUT_SECTION_VALIDITY][UploadPolicyFormUI::INPUT_FIELD_ACTIVE],
                $date_now,
                $data[UploadPolicyFormUI::INPUT_SECTION_VALIDITY][UploadPolicyFormUI::INPUT_FIELD_VALID_UNTIL],
                $this->current_user->getId(),
                $date_now,
                $date_now
            );
            $this->upload_policy_db_repository->store($upload_policy);

            $this->ctrl->redirectByClass(self::class, self::CMD_INDEX);
        }

        $this->main_tpl->setContent($this->ui_renderer->render($policy_form));
    }

    protected function deleteUploadPolicy(): void
    {
        $deletion_successful = false;

        if ($this->http->wrapper()->query()->has(UploadPolicy::POLICY_ID)) {
            $policy_id = $this->http->wrapper()->query()->retrieve(
                UploadPolicy::POLICY_ID,
                $this->refinery->kindlyTo()->int()
            );
            $policy = $this->upload_policy_db_repository->get($policy_id);
            if ($policy !== null) {
                $this->upload_policy_db_repository->delete($policy);
                $deletion_successful = true;
            }
        }

        if ($deletion_successful) {
            $this->main_tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->language->txt('policy_deletion_successful'),
                true
            );
        } else {
            $this->main_tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_FAILURE,
                $this->language->txt('policy_deletion_failure_not_found'),
                true
            );
        }

        $this->ctrl->redirectByClass(self::class, self::CMD_INDEX);
    }
}
