<?php

declare(strict_types=1);

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

use ILIAS\UI\Component\Modal\RoundTrip;

/**
 * Class ilLTIConsumerGradeSynchronizationTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilLTIConsumerGradeSynchronizationTableGUI extends ilTable2GUI
{
    public const TABLE_ID = 'cmix_statements_table';

    protected bool $isMultiActorReport;
    protected array $filter = [];
    private \ILIAS\DI\Container $dic;
    private ilLanguage $language;

    /**
     * @param ilLTIConsumerGradeSynchronizationGUI $a_parent_obj
     * @param string                                                $a_parent_cmd
     * @param bool                                                  $isMultiActorReport
     * @throws ilCtrlException
     */
    public function __construct(?object $a_parent_obj, string $a_parent_cmd, bool $isMultiActorReport)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $this->dic = $DIC;
        $DIC->language()->loadLanguageModule('cmix');
        $this->language = $DIC->language();

        $this->isMultiActorReport = $isMultiActorReport;

        $this->setId(self::TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $DIC->language()->loadLanguageModule('form');

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.lti_grade_synchronization_table_row.html', 'Modules/LTIConsumer');

        $this->initColumns();
        $this->initFilter();

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setDefaultOrderField('date');
        $this->setDefaultOrderDirection('desc');
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->language->txt('tbl_grade_date'), 'date');

        if ($this->isMultiActorReport) {
            $this->addColumn($this->language->txt('tbl_grade_actor'), 'actor');
        }

        $this->addColumn($this->language->txt('tbl_grade_object'), 'object'); //label otherwise id

        $this->addColumn($this->language->txt('tbl_grade_activityProgress'), 'verb'); //activityProgress
//        gradingProgress necessary?
        $this->addColumn($this->language->txt('tbl_grade_score'), 'score');

        $this->addColumn('', '', '1%');
    }

    public function initFilter(): void
    {
        if ($this->isMultiActorReport) {
            $ti = new ilTextInputGUI($this->language->txt('tbl_grade_actor'), "actor");
            $ti->setDataSource($this->dic->ctrl()->getLinkTarget($this->parent_obj, 'asyncUserAutocomplete', '', true));
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["actor"] = $ti->getValue();
        }

        $options = array(
            '' => $this->language->txt('grade_all_verbs'),
            'completed' => $this->language->txt('grade_activityProgress_completed'),
            'passed' => $this->language->txt('grade_activityProgress_passed'),
        );

        $si = new ilSelectInputGUI($this->language->txt('tbl_grade_activityProgress_select'), "verb");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["verb"] = $si->getValue();

        $dp = new ilCmiXapiDateDurationInputGUI($this->language->txt('tbl_grade_period'), 'period');
        $dp->setShowTime(true);
        $this->addFilterItem($dp);
        $dp->readFromSession();
        $this->filter["period"] = $dp->getValue();
    }

    protected function fillRow(array $a_set): void
    {
        $r = $this->dic->ui()->renderer();
        $a_set['rowkey'] = md5(serialize($a_set['statement']));
//        $rawDataModal = $this->getRawDataModal($a_set);

        $date = ilDatePresentation::formatDate(
            ilCmiXapiDateTime::fromXapiTimestamp($a_set['date'])
        );

        $this->tpl->setVariable('STMT_DATE', $date);

        if ($this->isMultiActorReport) {
            $actor = $a_set['actor'];
            if (empty($actor)) {
                $this->tpl->setVariable('STMT_ACTOR', 'user_not_found');
            } else {
                $this->tpl->setVariable('STMT_ACTOR', $this->getUsername($a_set['actor']));
            }
        }

        $this->tpl->setVariable('STMT_OBJECT', $a_set['object']);
        $this->tpl->setVariable('STMT_OBJECT_INFO', $a_set['object_info']);

        $this->tpl->setVariable('STMT_VERB', $this->language->txt('grade_activityProgress_' . $a_set['verb_id']));

        $this->tpl->setVariable('STMT_SCORE', $a_set['score']); //scoreMaximum
    }

    protected function getUsername(ilCmiXapiUser $cmixUser): string
    {
        $ret = 'not found';
        try {
            $userObj = ilObjectFactory::getInstanceByObjId($cmixUser->getUsrId());
            $ret = $userObj->getFullname();
        } catch (Exception $e) {
            $ret = $this->language->txt('deleted_user');
        }
        return $ret;
    }
}
