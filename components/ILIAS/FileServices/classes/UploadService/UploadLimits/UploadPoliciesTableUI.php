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

use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\ViewControl\Mode;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Component\Table\Presentation;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Dropdown\Dropdown;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class UploadPoliciesTableUI
{
    public const ACTIVE_FILTER_PARAM = "active_filter";
    protected const ACTIVE_FILTER_ALL = "all";
    protected const ACTIVE_FILTER_ACTIVE = "active";
    protected const ACTIVE_FILTER_INACTIVE = "inactive";

    /**
     * @var Component[]
     */
    protected array $components = [];

    public function __construct(
        protected UploadPolicyDBRepository $upload_policy_db_repository,
        protected ilCtrlInterface $ctrl,
        protected HttpServices $http,
        protected ilLanguage $language,
        protected ilGlobalTemplateInterface $main_tpl,
        protected ilRbacReview $rbac_review,
        protected RefineryFactory $refinery,
        protected UIFactory $ui_factory,
        protected Renderer $ui_renderer,
    ) {
        $policies = $this->getPresentablePolicies();
        $actions = $this->buildPolicyActions($policies);
        $table = $this->ui_factory->table()->presentation(
            $this->language->txt('upload_policies'),
            [$this->getViewControl()],
            function (
                PresentationRow $row,
                UploadPolicy $record,
                UIFactory $ui_factory
            ) use ($actions): PresentationRow {
                // Create texts for better data presentation in table
                $upload_limit_text = $record->getUploadLimitInMB() . " MB";
                $active_text = ($record->isActive()) ? $this->language->txt('yes') : $this->language->txt('no');
                $audience_text = $this->getAudienceText($record->getAudienceType(), $record->getAudience());
                $scope_text = $record->getScopeDefinition();
                $valid_until_text = $record->getValidUntil()?->format('d.m.Y') ?? $this->language->txt(
                    "policy_no_validity_limitation_set"
                );

                // Create row with fields and actions
                $row = $row
                    ->withHeadline($record->getTitle())
                    ->withImportantFields(
                        [
                            $this->language->txt('policy_upload_limit') => $upload_limit_text,
                            $this->language->txt('policy_audience') => $audience_text,
                            $this->language->txt('active') => $active_text
                        ]
                    )
                    ->withContent(
                        $ui_factory->listing()->descriptive(
                            [
                                $this->language->txt('policy_upload_limit') => $upload_limit_text,
                                $this->language->txt('policy_audience') => $audience_text,
                                $this->language->txt('active') => $active_text,
                                $this->language->txt('policy_scope') => $scope_text,
                                $this->language->txt('policy_valid_until') => $valid_until_text,
                            ]
                        )
                    );

                if (null !== ($dropdown = $actions[$record->getPolicyId()] ?? null)) {
                    $row = $row->withAction($dropdown);
                }

                return $row;
            }
        )->withData($policies);

        $this->components[] = $table;
    }

    /**
     * @return Component[]
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param UploadPolicy[] $policies
     * @return Dropdown[]
     */
    protected function buildPolicyActions(array $policies): array
    {
        $dropdowns = [];
        foreach ($policies as $policy) {
            // Store policy_id for later use when the table's actions (edit / delete) are used)
            $this->ctrl->setParameterByClass(
                ilUploadLimitsOverviewGUI::class,
                UploadPolicy::POLICY_ID,
                $policy->getPolicyId()
            );

            $deletion_modal = $this->getDeletionConfirmationModal($policy);
            $dropdowns[$policy->getPolicyId()] = $this->ui_factory->dropdown()->standard(
                [
                    $this->ui_factory->button()->shy(
                        $this->language->txt('edit'),
                        $this->ctrl->getLinkTargetByClass(
                            ilUploadLimitsOverviewGUI::class,
                            ilUploadLimitsOverviewGUI::CMD_EDIT_UPLOAD_POLICY
                        )
                    ),
                    $this->ui_factory->button()->shy(
                        $this->language->txt('delete'),
                        $deletion_modal->getShowSignal()
                    )
                ]
            );

            $this->components[] = $deletion_modal;
        }

        return $dropdowns;
    }

    protected function filterData(string $filter_value, array $data): array
    {
        $filtered_data = [];
        switch ($filter_value) {
            case self::ACTIVE_FILTER_ACTIVE:
                $active_value = true;
                break;
            case self::ACTIVE_FILTER_INACTIVE:
                $active_value = false;
                break;
            case self::ACTIVE_FILTER_ALL:
            default:
                return $data;
        }

        /**
         * @var UploadPolicy $data_entry
         */
        foreach ($data as $data_entry) {
            if ($data_entry->isActive() === $active_value) {
                $filtered_data[] = $data_entry;
            }
        }
        return $filtered_data;
    }

    protected function getAudienceText(int $audience_type, array $audience_data): string
    {
        switch ($audience_type) {
            case UploadPolicy::AUDIENCE_TYPE_GLOBAL_ROLE:
                $audience_text = $this->language->txt('all_global_roles');
                // add selected roles to audience_text
                if (!empty($audience_data['global_roles'])) {
                    $roles = $this->rbac_review->getRolesForIDs($audience_data['global_roles'], false);
                    $counter = 0;
                    foreach ($roles as $role) {
                        $counter++;
                        $audience_text .= " \"" . $role['title'] . "\"";
                        if ($counter !== count($roles)) {
                            $audience_text .= ",";
                        }
                    }
                }
                break;
            case UploadPolicy::AUDIENCE_TYPE_ALL_USERS:
            default:
                $audience_text = $this->language->txt('all_users');
                break;
        }

        return $audience_text;
    }

    protected function getViewControl(): Mode
    {
        $target = $this->ctrl->getLinkTargetByClass(
            ilUploadLimitsOverviewGUI::class,
            ilUploadLimitsOverviewGUI::CMD_INDEX
        );

        $active_control_element = self::ACTIVE_FILTER_ALL;
        if ($this->http->wrapper()->query()->has(self::ACTIVE_FILTER_PARAM)) {
            $active_control_element = $this->http->wrapper()->query()->retrieve(
                self::ACTIVE_FILTER_PARAM,
                $this->refinery->kindlyTo()->string()
            );
        }

        $actions_prefix = $target . "&" . self::ACTIVE_FILTER_PARAM . "=";
        $actions = [
            $this->language->txt(self::ACTIVE_FILTER_ALL) => $actions_prefix . self::ACTIVE_FILTER_ALL,
            $this->language->txt(self::ACTIVE_FILTER_ACTIVE) => $actions_prefix . self::ACTIVE_FILTER_ACTIVE,
            $this->language->txt(self::ACTIVE_FILTER_INACTIVE) => $actions_prefix . self::ACTIVE_FILTER_INACTIVE
        ];

        return $this->ui_factory->viewControl()->mode($actions, 'policy_filter')->withActive(
            $this->language->txt($active_control_element)
        );
    }

    protected function getDeletionConfirmationModal(UploadPolicy $record): Interruptive
    {
        $upload_limit_text = $record->getUploadLimitInMB() . " MB";
        $active_text = ($record->isActive()) ? $this->language->txt('yes') : $this->language->txt('no');
        $audience_text = $this->getAudienceText($record->getAudienceType(), $record->getAudience());
        $valid_until_text = $record->getValidUntil()?->format('d.m.Y') ?? $this->language->txt(
            "policy_no_validity_limitation_set"
        );

        $item_text = $this->language->txt('title') . $record->getTitle() . "<br/>"
            . $this->language->txt('policy_upload_limit') . $upload_limit_text . "<br/>"
            . $this->language->txt('policy_audience') . $audience_text . "<br/>"
            . $this->language->txt('active') . $active_text . "<br/>"
            . $this->language->txt('policy_scope') . $record->getScopeDefinition() . "<br/>"
            . $this->language->txt('policy_valid_until') . $valid_until_text;

        $deletion_item = $this->ui_factory->modal()->interruptiveItem()->standard(
            (string) $record->getPolicyId(),
            $item_text
        );

        return $this->ui_factory->modal()->interruptive(
            $this->language->txt("delete"),
            $this->language->txt('policy_confirm_deletion'),
            $this->ctrl->getLinkTargetByClass(
                ilUploadLimitsOverviewGUI::class,
                ilUploadLimitsOverviewGUI::CMD_DELETE_UPLOAD_POLICY
            )
        )->withAffectedItems(
            [$deletion_item]
        )->withActionButtonLabel($this->language->txt('delete'));
    }

    /**
     * @return UploadPolicy[]
     */
    protected function getPresentablePolicies(): array
    {
        $policy_data = $this->upload_policy_db_repository->getAll();

        if ($this->http->wrapper()->query()->has(self::ACTIVE_FILTER_PARAM)) {
            $policy_data = $this->filterData(
                $this->http->wrapper()->query()->retrieve(
                    self::ACTIVE_FILTER_PARAM,
                    $this->refinery->kindlyTo()->string()
                ),
                $policy_data
            );
        }

        return $policy_data;
    }
}
