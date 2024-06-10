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
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\WrapperFactory as RequestWrapper;
//use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper as RequestWrapper;

use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Data\URI;
use ILIAS\Data\DateFormat\DateFormat;

use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Modal;
use ILIAS\UI\Component\Link;
use ILIAS\UI\Component\Listing;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;

//use ILIAS\UI\Renderer  as UIRenderer;
//use Psr\Http\Message\ServerRequestInterface;

class ilStudyProgrammeAssignmentsTable
{
    public const ASYNC_ACTIONS = [
        ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_USER,
        ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_FROM_CURRENT_PLAN,
        ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_CERTIFICATE,
        ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_CERTIFICATE,
        ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_DEADLINE,
        ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_EXPIRE_DATE,
    ];

    protected string $table_id;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $row_id_token;

    public function __construct(
        protected UIFactory $ui_factory,
        protected Refinery $refinery,
        protected ilStudyProgrammeUserTable $prg_user_table,
        protected ilPRGAssignmentFilter $custom_filter,
        protected RequestWrapper $request_wrapper,
        protected URLBuilder $url_builder,
        protected ilPRGPermissionsHelper $permissions,
        protected ilLanguage $lng,
        protected int $current_user_id,
        protected int $prg_obj_id,
        protected bool $prg_has_lp_children,
        protected bool $certificate_enabled,
    ) {

        $this->table_id = 'prg_ass_' . $prg_obj_id;
        $query_params_namespace = ['prg_ass', $prg_obj_id];

        list($url_builder, $action_token, $row_id_token) = $url_builder->acquireParameters(
            $query_params_namespace,
            "action",
            "ids"
        );
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;
    }


    public function getTable(): Table\Data
    {
        $additional_params = [
            'prg_obj_id' => $this->prg_obj_id,
            'valid_user_ids' => $this->getValidUserIds(),
            'may_view_individual_plan' => $this->permissions->may(ilOrgUnitOperation::OP_VIEW_INDIVIDUAL_PLAN),
            'may_edit_individual_plan' => $this->permissions->may(ilOrgUnitOperation::OP_EDIT_INDIVIDUAL_PLAN),
            'may_addremove_users' => $this->permissions->may(ilOrgUnitOperation::OP_MANAGE_MEMBERS),
            'certificate_enabled' => $this->certificate_enabled,
        ];

        return $this->ui_factory->table()->data(
            'Assignments',
            $this->getColumns(),
            $this->getDataRetrieval(),
        )
        ->withId($this->table_id)
        ->withAdditionalParameters($additional_params)
        ->withActions($this->getActions($additional_params));
    }

    protected function getDataRetrieval(): Table\DataRetrieval
    {
        return new class (
            $this->prg_user_table,
            $this->ui_factory
        ) implements Table\DataRetrieval {
            public function __construct(
                protected ilStudyProgrammeUserTable $prg_user_table,
                protected UIFactory $ui_factory,
            ) {
            }

            public function getRows(
                Table\DataRowBuilder $row_builder,
                array $visible_column_ids,
                Range $range,
                Order $order,
                ?array $filter_data,
                ?array $additional_parameters
            ): \Generator {
                $rows = $this->prg_user_table->fetchData(
                    $additional_parameters['prg_obj_id'],
                    $additional_parameters['valid_user_ids'],
                    $order
                );
                foreach ($rows as $row) {
                    $row_data = $row->toArray();
                    $row_data['completion_by'] = $this->buildLinkListForCompletionBy($row);
                    $data_row = $row_builder->buildDataRow((string) $row->getId(), $row_data);
                    $data_row = $this->disableSingleActions($data_row, $row);
                    yield $data_row ;
                }
            }

            protected function buildLinkListForCompletionBy(ilStudyProgrammeUserTableRow $row): Listing\Unordered
            {
                $completion_by = $row->getCompletionBy();
                $out = [];
                if(!$completion_by) {
                    return $this->ui_factory->listing()->unordered($out);
                }

                if ($completion_by_obj_ids = $row->getCompletionByObjIds()) {
                    foreach ($completion_by_obj_ids as $completion_by_obj_id) {
                        $type = ilObject::_lookupType($completion_by_obj_id);
                        if ($type === 'crsr') {
                            $target_obj_id = ilContainerReference::_lookupTargetId($completion_by_obj_id);
                            $out[] = $this->getCompletionLink($completion_by, $target_obj_id);
                        } else {
                            $target_obj_id = $completion_by_obj_id;
                            $out[] = $this->getCompletionLink(
                                ilStudyProgrammeUserTable::lookupTitle($completion_by_obj_id),
                                $target_obj_id
                            );
                        }
                    }
                } else {
                    $out[] = $this->getCompletionLink($completion_by, null);
                }
                return $this->ui_factory->listing()->unordered($out);
            }

            protected function getCompletionLink(string $title, ?int $target_obj_id): Link\Standard
            {
                $url = '#';
                if($target_obj_id !== null) {
                    $ref_ids = array_filter(
                        array_values(ilObject::_getAllReferences($target_obj_id)),
                        fn($ref_id) => !ilObject::_isInTrash($ref_id)
                    );
                    if($ref_ids) {
                        $url = ilLink::_getStaticLink(current($ref_ids), "crs");
                    }
                }
                return $this->ui_factory->link()->standard($title, $url);//->withDisabled($url === '#');
            }

            protected function disableSingleActions(
                Table\DataRow $data_row,
                ilStudyProgrammeUserTableRow $row
            ): Table\DataRow {
                $disabled = [];
                if ($row->isRootProgress()) {
                    $disabled = array_merge($disabled, [
                        ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_RELEVANT,
                        ilObjStudyProgrammeMembersGUI::ACTION_MARK_RELEVANT,
                    ]);
                } else {
                    $disabled = array_merge($disabled, [
                        ilObjStudyProgrammeMembersGUI::ACTION_SHOW_INDIVIDUAL_PLAN,
                        ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_USER,
                        ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_FROM_CURRENT_PLAN,
                        ilObjStudyProgrammeMembersGUI::ACTION_ACKNOWLEDGE_COURSES,
                        ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_DEADLINE,
                        ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_EXPIRE_DATE,
                    ]);
                }

                if (in_array($row->getStatusRaw(), [
                    ilPRGProgress::STATUS_COMPLETED,
                    ilPRGProgress::STATUS_ACCREDITED
                ])) {
                    $disabled = array_merge($disabled, [
                        ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_RELEVANT,
                        ilObjStudyProgrammeMembersGUI::ACTION_MARK_ACCREDITED,
                    ]);
                } else {
                    $disabled = array_merge($disabled, [
                        ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_CERTIFICATE,
                        ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_CERTIFICATE
                    ]);
                }

                if($row->getStatusRaw() === ilPRGProgress::STATUS_NOT_RELEVANT) {
                    $disabled = array_merge($disabled, [
                        ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_RELEVANT,
                        ilObjStudyProgrammeMembersGUI::ACTION_MARK_ACCREDITED
                    ]);
                } else {
                    $disabled = array_merge($disabled, [
                        ilObjStudyProgrammeMembersGUI::ACTION_MARK_RELEVANT
                    ]);
                }

                if($row->getStatusRaw() !== ilPRGProgress::STATUS_ACCREDITED) {
                    $disabled = array_merge($disabled, [
                        ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_ACCREDITED
                    ]);
                }

                foreach(array_unique($disabled) as $cmd) {
                    $data_row = $data_row->withDisabledAction($cmd, true);
                }

                return $data_row;
            }

            public function getTotalRowCount(
                ?array $filter_data,
                ?array $additional_parameters
            ): ?int {
                return 100;
                return $this->prg_user_table->countFetchData(
                    $additional_parameters['prg_obj_id'],
                    $additional_parameters['valid_user_ids'],
                    //$this->custom_filter->withValues($filter_values)
                );
            }
        };
    }

    protected function getColumns(): array
    {
        $columns = [];
        foreach ($this->prg_user_table->getColumns($this->prg_obj_id) as $column) {
            [$col, $lng_var, $optional, $lp, $no_lp] = $column;
            if(str_starts_with($col, 'prg_')) {
                $col = substr($col, 4);
            }

            $show_by_lp = ($this->prg_has_lp_children && $lp) || (!$this->prg_has_lp_children && $no_lp);
            if($show_by_lp) {

                switch($col) {
                    case 'completion_by':
                        $columns[$col] = $this->ui_factory->table()->column()
                            ->linkListing($lng_var)
                            ->withIsOptional($optional, false);
                        break;
                    default:
                        $columns[$col] = $this->ui_factory->table()->column()
                            ->text($lng_var)
                            ->withIsOptional($optional, false);
                }

            }
        }
        return $columns;
    }

    protected function getActions(array $additional_parameters): array
    {
        if($additional_parameters['may_view_individual_plan']) {
            $cmds[] = ilObjStudyProgrammeMembersGUI::ACTION_SHOW_INDIVIDUAL_PLAN;
        }

        if($additional_parameters['may_addremove_users']) {
            $cmds[] = ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_USER;
            $cmds[] = ilObjStudyProgrammeMembersGUI::ACTION_MAIL_USER;
        }

        if($additional_parameters['may_edit_individual_plan']) {
            $cmds = array_merge($cmds, [
                ilObjStudyProgrammeMembersGUI::ACTION_MARK_ACCREDITED,
                ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_ACCREDITED,
                ilObjStudyProgrammeMembersGUI::ACTION_UNMARK_RELEVANT,
                ilObjStudyProgrammeMembersGUI::ACTION_MARK_RELEVANT,
                ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_FROM_CURRENT_PLAN,
                ilObjStudyProgrammeMembersGUI::ACTION_ACKNOWLEDGE_COURSES,
                ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_DEADLINE,
                ilObjStudyProgrammeMembersGUI::ACTION_CHANGE_EXPIRE_DATE,
            ]);
        }

        if($additional_parameters['certificate_enabled']) {
            $cmds = array_merge($cmds, [
                ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_CERTIFICATE,
                ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_CERTIFICATE,
            ]);
        }

        $async_actions = self::ASYNC_ACTIONS;
        $single_actions = [
            ilObjStudyProgrammeMembersGUI::ACTION_SHOW_INDIVIDUAL_PLAN,
        ];

        $actions = [];
        foreach($cmds as $cmd) {
            $action_type = in_array($cmd, $single_actions) ? 'single' : 'standard';
            $actions[$cmd] = $this->ui_factory->table()->action()->$action_type(
                $this->lng->txt("prg_$cmd"),
                $this->url_builder->withParameter($this->action_token, $cmd),
                $this->row_id_token
            );
            if (in_array($cmd, $async_actions)) {
                $actions[$cmd] = $actions[$cmd]->withAsync();
            }
        }
        return $actions;
    }

    protected function getValidUserIds(): ?array
    {
        if ($this->permissions->may($this->permissions::ROLEPERM_MANAGE_MEMBERS)) {
            return null;
        }
        $valid_user_ids = $this->permissions->getUserIdsSusceptibleTo(ilOrgUnitOperation::OP_VIEW_MEMBERS);
        array_unshift($valid_user_ids, $this->current_user_id);
        return $valid_user_ids;
    }

    public function getTableCommand(): ?string
    {
        if(! $this->request_wrapper->query()->has($this->action_token->getName())) {
            return null;
        }
        return $this->request_wrapper->query()->retrieve(
            $this->action_token->getName(),
            $this->refinery->kindlyTo()->string()
        );
    }

    public function getRowIds(): ?array
    {
        if ($this->request_wrapper->query()->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->identity()
        ) === ['ALL_OBJECTS']) {
            $rows = $this->prg_user_table->fetchData($this->prg_obj_id, null, null);
            $ids = array_map(
                fn($r) => (string) $r->getId(),
                $rows
            );
        } else {
            $ids = $this->request_wrapper->query()->retrieve(
                $this->row_id_token->getName(),
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->byTrying([
                        $this->refinery->kindlyTo()->string(),
                        $this->refinery->always(null)
                    ])
                )
            );
        }

        return array_map(
            fn($id) => PRGProgressId::createFromString($id),
            array_filter($ids)
        );
    }

    protected const MODAL_TEXTS = [
        ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_USER_CONFIRMED => [
            'prg_remove_user',
            'confirm_to_remove_selected_assignments',
            'prg_remove_user',
        ],
        ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_FROM_CURRENT_PLAN_CONFIRMED => [
            'confirm',
            'header_update_current_plan',
            'confirm',
        ],
        ilObjStudyProgrammeMembersGUI::ACTION_UPDATE_CERTIFICATE_CONFIRMED => [
            'confirm',
            'header_update_certificate',
            'confirm',
        ],
        ilObjStudyProgrammeMembersGUI::ACTION_REMOVE_CERTIFICATE_CONFIRMED => [
            'confirm',
            'header_remove_certificate',
            'confirm',
        ]
    ];

    public function getConfirmationModal(string $action, array $prgs_ids): Modal\Interruptive
    {
        $affected = [];
        foreach ($prgs_ids as $id) {
            $user_name = ilObjUser::_lookupFullname($id->getUsrId());
            $affected[] = $this->ui_factory->modal()->interruptiveItem()->keyvalue(
                (string) $id,
                $user_name,
                (string) $id
            );
        }

        list($caption, $txt, $button_label) = self::MODAL_TEXTS[$action];

        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt($caption),
            $this->lng->txt($txt),
            $this->url_builder->withParameter(
                $this->action_token,
                $action
            )
            ->buildURI()
            ->__toString()
        )
        ->withAffectedItems($affected)
        ->withActionButtonLabel($this->lng->txt($button_label));
    }


    public function getDeadlineModal(
        string $action,
        array $prgrs_ids,
        DateFormat $format
    ): Modal\Roundtrip {
        $ff = $this->ui_factory->input()->field();
        $settings = $ff->switchableGroup(
            [
                ilObjStudyProgrammeSettingsGUI::OPT_NO_DEADLINE => $ff->group(
                    [],
                    $this->lng->txt('prg_no_deadline')
                ),
                ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE => $ff->group(
                    [
                        $ff->dateTime('', $this->lng->txt('prg_deadline_date_desc'))
                        ->withFormat($format)
                        ->withRequired(true)
                    ],
                    $this->lng->txt('prg_deadline_date')
                )
            ],
            ''
        )->withValue(ilObjStudyProgrammeSettingsGUI::OPT_DEADLINE_DATE);

        $ids = array_map(fn($id) => $id->__toString(), $prgrs_ids);
        $action = $this->url_builder
            ->withParameter($this->action_token, $action)
            ->withParameter($this->row_id_token, $ids)
            ->buildURI()
            ->__toString();

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('prg_deadline_settings'),
            null,
            [$settings],
            $action
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->transformation(fn($v) => array_shift($v))
        );
    }

    public function getExpiryModal(
        string $action,
        array $prgrs_ids,
        DateFormat $format
    ): Modal\Roundtrip {
        $ff = $this->ui_factory->input()->field();
        $settings = $ff->switchableGroup(
            [
                ilObjStudyProgrammeSettingsGUI::OPT_NO_VALIDITY_OF_QUALIFICATION => $ff->group(
                    [],
                    $this->lng->txt('prg_no_validity_qualification')
                ),
                ilObjStudyProgrammeSettingsGUI::OPT_VALIDITY_OF_QUALIFICATION_DATE => $ff->group(
                    [
                        $ff->dateTime('', $this->lng->txt('validity_qualification_date_desc'))
                        ->withFormat($format)
                        ->withRequired(true)
                    ],
                    $this->lng->txt('validity_qualification_date')
                )
            ],
            ''
        )->withValue(ilObjStudyProgrammeSettingsGUI::OPT_VALIDITY_OF_QUALIFICATION_DATE);

        $ids = array_map(fn($id) => $id->__toString(), $prgrs_ids);
        $action = $this->url_builder
            ->withParameter($this->action_token, $action)
            ->withParameter($this->row_id_token, $ids)
            ->buildURI()
            ->__toString();

        return $this->ui_factory->modal()->roundtrip(
            $this->lng->txt('prg_deadline_settings'),
            null,
            [$settings],
            $action
        )
        ->withAdditionalTransformation(
            $this->refinery->custom()->transformation(fn($v) => array_shift($v))
        );
    }

    public function getLinkMailToAllUsers(): URI
    {
        return $this->url_builder
            ->withParameter($this->action_token, ilObjStudyProgrammeMembersGUI::ACTION_MAIL_USER)
            ->withParameter($this->row_id_token, ['ALL_OBJECTS'])
            ->buildURI();
    }

    public function getFilter(): Filter
    {
        return $this->custom_filter->toForm();
    }

}
