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

use ILIAS\EmployeeTalk\UI\ControlFlowCommand;
use ILIAS\EmployeeTalk\UI\ControlFlowCommandHandler;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Implementation\Component\Modal\Interruptive as InterruptiveModal;

final class ilEmployeeTalkTableGUI extends ilTable2GUI
{
    public const STATUS_ALL = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_COMPLETED = 2;

    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private ilLanguage $language;
    private ilObjEmployeeTalkAccess $talk_access;

    /**
     * InterruptiveModal[]
     */
    private array $delete_modals;

    public function __construct(ControlFlowCommandHandler $a_parent_obj, $a_parent_cmd = '')
    {
        global $DIC;

        $this->talk_access = new ilObjEmployeeTalkAccess();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->language = $DIC->language();
        $this->language->loadLanguageModule('etal');
        $this->language->loadLanguageModule('orgu');

        $this->setPrefix('myst_etal_list_');
        $this->setFormName('myst_etal_list');
        $this->setId('myst_etal_list');

        parent::__construct($a_parent_obj, $a_parent_cmd, '');
        $this->setRowTemplate('tpl.list_employee_talk_row.html', "components/ILIAS/EmployeeTalk");
        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj));
        ;
        $this->setDefaultOrderDirection('desc');

        $this->setShowRowsSelector(true);


        $this->setEnableTitle(true);
        $this->setDisableFilterHiding(true);
        $this->setEnableNumInfo(true);
        $this->setExternalSegmentation(true);

        $this->addColumns();

        $this->initFilter();
        $this->determineLimit();
        $this->determineOffsetAndOrder();
    }

    public function initFilter(): void
    {
        $this->setFilterCols(6);
        $this->addFilterItemByMetaType('etal_title', self::FILTER_TEXT, false, $this->language->txt('title'));
        $this->addFilterItemByMetaType('etal_template', self::FILTER_TEXT, false, $this->language->txt('type'));

        /**
         * @var ilDateTimeInputGUI $dateSelectInput
         */
        $dateSelectInput = $this->addFilterItemByMetaType('etal_date', self::FILTER_DATE, false, $this->language->txt('date_of_talk'));

        // Filter throws a null pointer error if not set
        if ($dateSelectInput->getDate() === null) {
            $dateSelectInput->setDate(new ilDateTime());
        }
        $this->addFilterItemByMetaType('etal_superior', self::FILTER_TEXT, false, $this->language->txt('superior'));
        $this->addFilterItemByMetaType('etal_employee', self::FILTER_TEXT, false, $this->language->txt('employee'));
        /**
         * @var ilSelectInputGUI $ilSelectInput
         */
        $ilSelectInput = $this->addFilterItemByMetaType('etal_status', self::FILTER_SELECT, false, $this->language->txt('status'));
        $ilSelectInput->setOptions([
            self::STATUS_ALL => $this->language->txt('etal_status_all'),
            self::STATUS_PENDING => $this->language->txt('etal_status_pending'),
            self::STATUS_COMPLETED => $this->language->txt('etal_status_completed')
        ]);
    }

    private function addColumns(): void
    {
        $this->addColumn(
            $this->language->txt('title'),
            "etal_title",
            "auto"
        );
        $this->addColumn(
            $this->language->txt('type'),
            "etal_template",
            "auto"
        );
        $this->addColumn(
            $this->language->txt('date_of_talk'),
            "etal_date",
            "auto"
        );
        $this->addColumn(
            $this->language->txt('superior'),
            "etal_superior",
            "auto"
        );
        $this->addColumn(
            $this->language->txt('employee'),
            "etal_employee",
            "auto"
        );
        $this->addColumn(
            $this->language->txt('status'),
            "etal_status",
            "auto"
        );
        $this->addColumn(
            $this->language->txt('action'),
            "",
            "auto"
        );

        $this->setDefaultFilterVisiblity(true);
        $this->setDefaultOrderField("etal_date");
    }

    protected function fillRow($a_set): void
    {
        $talk_class = strtolower(ilObjEmployeeTalkGUI::class);
        $class_path = $this->getParentObject()->getClassPath();
        $class_path[] = $talk_class;

        $ref_id = $a_set['ref_id'];
        $this->ctrl->setParameterByClass($talk_class, 'ref_id', $ref_id);
        $url = $this->ctrl->getLinkTargetByClass($class_path, ControlFlowCommand::DEFAULT);

        $buttons = [];
        if ($this->talk_access->canEdit($ref_id)) {
            $buttons[] = $this->ui_factory->link()->standard(
                $this->language->txt('edit'),
                $this->ctrl->getLinkTargetByClass($class_path, ControlFlowCommand::UPDATE)
            );
        } else {
            $buttons[] = $this->ui_factory->link()->standard(
                $this->language->txt('view'),
                $this->ctrl->getLinkTargetByClass($class_path, ControlFlowCommand::INDEX)
            );
        }

        if ($this->talk_access->canDelete($ref_id)) {
            $modal = $this->getConfirmationModal(
                $a_set['etal_title'],
                $ref_id,
                $this->ctrl->getLinkTargetByClass($class_path, ControlFlowCommand::DELETE)
            );
            $buttons[] = $this->ui_factory->button()->shy(
                $this->language->txt('delete'),
                ''
            )->withOnClick($modal->getShowSignal());
            $this->delete_modals[] = $modal;
        }
        $this->ctrl->clearParametersByClass($talk_class);
        $actions = $this->ui_factory->dropdown()->standard($buttons)->withLabel(
            $this->language->txt('actions')
        );

        $this->tpl->setVariable("HREF_ETAL_TITLE", $url);
        $this->tpl->setVariable("VAL_ETAL_TITLE", $a_set['etal_title']);
        $this->tpl->setVariable("VAL_ETAL_TEMPLATE", $a_set['etal_template']);
        $this->tpl->setVariable("VAL_ETAL_DATE", ilDatePresentation::formatDate($a_set['etal_date']));
        $this->tpl->setVariable("VAL_ETAL_SUPERIOR", $a_set['etal_superior']);
        $this->tpl->setVariable("VAL_ETAL_EMPLOYEE", $a_set['etal_employee']);
        $this->tpl->setVariable("VAL_ETAL_STATUS", $a_set['etal_status']);
        $this->tpl->setVariable("ACTIONS", $this->ui_renderer->render($actions));
    }

    public function setTalkData(array $talks): void
    {
        $filter = $this->getCurrentState()['filter_values'];

        $data = [];
        foreach ($talks as $val) {
            if (!ilObject::_hasUntrashedReference($val->getObjectId())) {
                continue;
            }

            if ($filter['etal_employee'] !== "") {
                $filterUser = ilObjUser::getUserIdByLogin($filter['etal_employee']);
                if ($val->getEmployee() !== $filterUser) {
                    continue;
                }
            }

            $refIds = ilObjEmployeeTalk::_getAllReferences($val->getObjectId());
            $talk = new ilObjEmployeeTalk(array_pop($refIds), true);
            $talkData = $talk->getData();
            $employeeName = $this->language->txt('etal_unknown_username');
            $superiorName = $this->language->txt('etal_unknown_username');
            $ownerId = $talk->getOwner();
            if (ilObjUser::_exists($talk->getOwner())) {
                $superiorName = ilObjUser::_lookupLogin($ownerId);
            }
            if (ilObjUser::_exists($talkData->getEmployee())) {
                $employeeName = ilObjUser::_lookupLogin($talkData->getEmployee());
            }

            if ($filter['etal_superior'] !== "") {
                $filterUser = ilObjUser::getUserIdByLogin($filter['etal_superior']);
                if ($talk->getOwner() !== $filterUser) {
                    continue;
                }
            }

            if ($filter['etal_title'] !== "") {
                if (strpos($talk->getTitle(), $filter['etal_title']) === false) {
                    continue;
                }
            }

            if ($filter['etal_date'] !== false && $filter['etal_date'] !== null && $filter['etal_date'] !== '') {
                $filterDate = new ilDateTime($filter['etal_date'], IL_CAL_DATE);
                if (
                    !ilDateTime::_equals($filterDate, $val->getStartDate(), IL_CAL_DAY)
                ) {
                    continue;
                }
            }
            if ($filter['etal_status'] !== "" && intval($filter['etal_status'] !== 0)) {
                $filterCompleted = intval($filter['etal_status']) === ilEmployeeTalkTableGUI::STATUS_COMPLETED;
                if ($filterCompleted && !$val->isCompleted()) {
                    continue;
                }

                if (!$filterCompleted && $val->isCompleted()) {
                    continue;
                }
            }

            $template_title = '';
            if ($talkData->getTemplateId() > 0) {
                $template = ilObjectFactory::getInstanceByObjId($talkData->getTemplateId());
                $template_title = $template->getTitle();
            }
            if ($filter['etal_template'] !== "") {
                if (strpos($template_title, $filter['etal_template']) === false) {
                    continue;
                }
            }

            $data[] = [
                "ref_id" => $talk->getRefId(),
                "etal_title" => $talk->getTitle(),
                "etal_template" => $template_title,
                "etal_date" => $talkData->getStartDate(),
                "etal_superior" => $superiorName,
                "etal_employee" => $employeeName,
                "etal_status" => $talkData->isCompleted() ? $this->language->txt('etal_status_completed') : $this->language->txt('etal_status_pending'),
                "permission_write" => false,
                "permission_delete" => false
            ];
        }

        $offset = $this->getOffset();
        $limit = $this->getLimit() + 1;

        $this->setMaxCount(count($data));

        $dataSlice = array_slice($data, $offset, $limit, true);
        $this->setData($dataSlice);
    }

    protected function getConfirmationModal(
        string $talk_title,
        int $talk_ref_id,
        string $action
    ): InterruptiveModal {
        $item = $this->ui_factory->modal()->interruptiveItem()->standard(
            (string) $talk_ref_id,
            $talk_title
        );

        return $this->ui_factory->modal()->interruptive(
            $this->language->txt('confirm'),
            $this->language->txt('etal_delete_confirmation_msg'),
            $action
        )->withAffectedItems([$item]);
    }

    public function getHTML(): string
    {
        return parent::getHTML() . $this->ui_renderer->render($this->delete_modals);
    }
}
