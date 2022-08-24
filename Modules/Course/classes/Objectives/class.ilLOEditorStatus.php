<?php

declare(strict_types=0);

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

use ILIAS\UI\Implementation\Component\Listing\Workflow\Step;
use ILIAS\UI\Renderer as UiRenderer;
use ILIAS\UI\Component\Listing\Workflow\Factory as UiWorkflow;

/**
 * Presentation of the status of single steps during the configuration process.
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOEditorStatus
{
    public const SECTION_UNDEFINED = 0;
    public const SECTION_SETTINGS = 1;
    public const SECTION_MATERIALS = 2;
    public const SECTION_ITES = 3;
    public const SECTION_QTEST = 4;
    public const SECTION_OBJECTIVES = 5;
    public const SECTION_OBJECTIVES_NEW = 6;

    protected static ?self $instance = null;

    protected int $section = self::SECTION_UNDEFINED;

    /** @var string[] */
    protected array $failures_by_section = [];

    /** @var int[] */
    protected array $error_by_section = [];

    protected array $objectives = [];
    protected int $forced_test_type = 0;

    protected ilLOSettings $settings;
    protected ilLOTestAssignments $assignments;
    protected ilObject $parent_obj;
    protected ?object $cmd_class = null;
    protected string $html = '';

    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilTree $tree;
    protected ilGlobalTemplateInterface $tpl;
    protected UiRenderer $ui_renderer;
    protected UiWorkflow $workflow;
    protected \ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\HTTP\Services $http;

    public function __construct(ilObject $a_parent)
    {
        global $DIC;

        $this->parent_obj = $a_parent;
        $this->settings = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId());
        $this->assignments = ilLOTestAssignments::getInstance($this->getParentObject()->getId());
        $this->objectives = ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId());

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tree = $DIC->repositoryTree();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->workflow = $DIC->ui()->factory()->listing()->workflow();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    public static function getInstance(ilObject $a_parent): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        return self::$instance = new self($a_parent);
    }

    protected function initTestTypeFromQuery(): int
    {
        if ($this->forced_test_type > 0) {
            return $this->forced_test_type;
        }

        if ($this->http->wrapper()->query()->has('tt')) {
            return $this->http->wrapper()->query()->retrieve(
                'tt',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    /**
     * @return int[]
     */
    public function getObjectives(): array
    {
        return $this->objectives;
    }

    public function getAssignments(): ilLOTestAssignments
    {
        return $this->assignments;
    }

    public function setSection(int $a_section): void
    {
        $this->section = $a_section;
    }

    public function getSection(): int
    {
        return $this->section;
    }

    public function getFailures(int $a_section): array
    {
        return (array) ($this->failures_by_section[$a_section] ?? []);
    }

    protected function appendFailure(int $a_section, string $a_failure_msg_key, bool $is_error = false): void
    {
        $this->failures_by_section[$a_section][] = $a_failure_msg_key;
        if ($is_error) {
            $this->error_by_section[$a_section] = $a_section;
        }
    }

    public function setCmdClass(object $a_cmd_class): void
    {
        $this->cmd_class = $a_cmd_class;
    }

    public function getCmdClass(): object
    {
        return $this->cmd_class;
    }

    public function getParentObject(): ilObject
    {
        return $this->parent_obj;
    }

    public function getSettings(): ilLOSettings
    {
        return $this->settings;
    }

    public function getFirstFailedStep(): string
    {
        if (!$this->getSettingsStatus()) {
            return 'settings';
        }
        if (!$this->getObjectivesAvailableStatus()) {
            return 'showObjectiveCreation';
        }
        if ($this->getSettings()->worksWithInitialTest()) {
            if (!$this->getInitialTestStatus(false)) {
                $this->forced_test_type = ilLOSettings::TYPE_TEST_INITIAL;
                if ($this->getSettings()->hasSeparateInitialTests()) {
                    return 'testsOverview';
                } else {
                    return 'testOverview';
                }
            }
        }
        if (!$this->getQualifiedTestStatus(false)) {
            $this->forced_test_type = ilLOSettings::TYPE_TEST_QUALIFIED;
            if ($this->getSettings()->hasSeparateQualifiedTests()) {
                return 'testsOverview';
            } else {
                return 'testOverview';
            }
        }
        return 'listObjectives';
    }

    public function getHTML(): string
    {
        $steps = [];
        // Step 1
        // course settings
        $done = $this->getSettingsStatus();

        $steps[] = $this->workflow->step(
            $this->lng->txt('crs_objective_status_settings'),
            implode(" ", $this->getFailureMessages(self::SECTION_SETTINGS)),
            $this->ctrl->getLinkTarget($this->getCmdClass(), 'settings')
        )->withStatus($this->determineStatus($done, self::SECTION_SETTINGS));

        // Step 1.1
        $done = $this->getObjectivesAvailableStatus(true);

        $steps[] = $this->workflow->step(
            $this->lng->txt('crs_objective_status_objective_creation'),
            implode(" ", $this->getFailureMessages(self::SECTION_OBJECTIVES_NEW)),
            $done
                ? $this->ctrl->getLinkTarget($this->getCmdClass(), 'listObjectives')
                : $this->ctrl->getLinkTarget($this->getCmdClass(), 'showObjectiveCreation')
        )->withStatus($this->determineStatus($done, self::SECTION_OBJECTIVES_NEW));

        // Step 2
        // course material
        $done = $this->getMaterialsStatus(true);
        $this->ctrl->setParameterByClass('ilobjcoursegui', 'cmd', 'enableAdministrationPanel');

        $steps[] = $this->workflow->step(
            $this->lng->txt('crs_objective_status_materials'),
            implode(" ", $this->getFailureMessages(self::SECTION_MATERIALS)),
            $this->ctrl->getLinkTargetByClass('ilobjcoursegui', '')
        )->withStatus($this->determineStatus($done, self::SECTION_MATERIALS));

        // Step 3
        // course itest
        if (ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->worksWithInitialTest()) {
            $done = $this->getInitialTestStatus();
            $command = $this->getSettings()->hasSeparateInitialTests() ?
                'testsOverview' :
                'testOverview';
            $this->ctrl->setParameter($this->getCmdClass(), 'tt', ilLOSettings::TYPE_TEST_INITIAL);

            $steps[] = $this->workflow->step(
                $this->lng->txt('crs_objective_status_itest'),
                implode(" ", $this->getFailureMessages(self::SECTION_ITES)),
                $this->ctrl->getLinkTarget($this->getCmdClass(), $command)
            )->withStatus($this->determineStatus($done, self::SECTION_ITES));
        }

        // Step 4
        // course qtest
        $done = $this->getQualifiedTestStatus();
        $command = $this->getSettings()->hasSeparateQualifiedTests() ?
            'testsOverview' :
            'testOverview';
        $this->ctrl->setParameter($this->getCmdClass(), 'tt', ilLOSettings::TYPE_TEST_QUALIFIED);

        $steps[] = $this->workflow->step(
            $this->lng->txt('crs_objective_status_qtest'),
            implode(" ", $this->getFailureMessages(self::SECTION_QTEST)),
            $this->ctrl->getLinkTarget($this->getCmdClass(), $command)
        )->withStatus($this->determineStatus($done, self::SECTION_QTEST));

        // Step 5
        // course qtest
        $done = $this->getObjectivesStatus();
        $this->ctrl->setParameter($this->getCmdClass(), 'tt', $this->initTestTypeFromQuery());

        $steps[] = $this->workflow->step(
            $this->lng->txt('crs_objective_status_objectives'),
            implode(" ", $this->getFailureMessages(self::SECTION_OBJECTIVES)),
            $this->ctrl->getLinkTarget($this->getCmdClass(), 'listObjectives')
        )->withStatus($this->determineStatus($done, self::SECTION_OBJECTIVES));

        $list = $this->workflow->linear(
            $this->lng->txt('crs_objective_status_configure'),
            $steps
        )->withActive($this->determineActiveSection());
        return $this->ui_renderer->render($list);
    }

    /**
     * @return string[]
     */
    public function getFailureMessages(int $a_section): array
    {
        $mess = array();
        foreach ($this->getFailures($a_section) as $failure_code) {
            $mess[] = $this->lng->txt($failure_code);
        }
        return $mess;
    }

    public function determineStatus(bool $done, int $section): int
    {
        if ($done) {
            return Step::SUCCESSFULLY;
        } elseif ($this->hasSectionErrors($section)) {
            return Step::UNSUCCESSFULLY;
        } elseif ($this->section === $section) {
            return Step::IN_PROGRESS;
        } else {
            return Step::NOT_STARTED;
        }
    }

    public function determineActiveSection(): int
    {
        $itest_enabled = ilLOSettings::getInstanceByObjId($this->getParentObject()->getId())->worksWithInitialTest();
        $active_map = array(
            self::SECTION_SETTINGS => 0,
            self::SECTION_OBJECTIVES_NEW => 1,
            self::SECTION_MATERIALS => 2,
            self::SECTION_ITES => 3,
            self::SECTION_QTEST => $itest_enabled ? 4 : 3,
            self::SECTION_OBJECTIVES => $itest_enabled ? 5 : 4
        );

        return $active_map[$this->section];
    }

    public function hasSectionErrors(int $a_section): bool
    {
        return isset($this->error_by_section[$a_section]);
    }

    protected function getSettingsStatus(): bool
    {
        return $this->getSettings()->settingsExist();
    }

    protected function getObjectivesAvailableStatus($a_set_errors = false): bool
    {
        $ret = count($this->getObjectives());

        if (!$ret && $a_set_errors) {
            $this->appendFailure(self::SECTION_OBJECTIVES_NEW, 'crs_no_objectives_created');
            return false;
        }
        return true;
    }

    protected function getMaterialsStatus(bool $a_set_errors = true): bool
    {
        $childs = $this->tree->getChilds($this->getParentObject()->getRefId());
        foreach ($childs as $tnode) {
            if ($tnode['type'] == 'rolf') {
                continue;
            }
            if ($tnode['child'] == $this->getSettings()->getInitialTest()) {
                continue;
            }
            if ($tnode['child'] == $this->getSettings()->getQualifiedTest()) {
                continue;
            }
            return true;
        }
        if ($a_set_errors) {
            $this->appendFailure(self::SECTION_MATERIALS, 'crs_loc_err_stat_no_materials');
        }
        return false;
    }

    protected function getInitialTestStatus(bool $a_set_errors = true): bool
    {
        if ($this->getSettings()->hasSeparateInitialTests()) {
            if (count($this->objectives) <= 0) {
                return false;
            }

            foreach ($this->getObjectives() as $objective_id) {
                $tst_ref = $this->getAssignments()->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL);
                if (!$this->tree->isInTree($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_no_it');
                    }
                    return false;
                }
                if (!$this->checkTestOnline($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_tst_offline', true);
                    }
                    return false;
                }
            }
            return true;
        }

        $tst_ref = $this->getSettings()->getInitialTest();
        if (!$this->tree->isInTree($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_no_it');
            }
            return false;
        }
        if (!$this->checkTestOnline($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_ITES, 'crs_loc_err_stat_tst_offline', true);
            }
            return false;
        }
        return true;
    }

    protected function getQualifiedTestStatus(bool $a_set_errors = true): bool
    {
        if ($this->getSettings()->hasSeparateQualifiedTests()) {
            if (count($this->objectives) <= 0) {
                return false;
            }

            foreach ($this->getObjectives() as $objective_id) {
                $tst_ref = $this->getAssignments()->getTestByObjective(
                    $objective_id,
                    ilLOSettings::TYPE_TEST_QUALIFIED
                );
                if (!$this->tree->isInTree($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_no_qt');
                    }
                    return false;
                }
                if (!$this->checkTestOnline($tst_ref)) {
                    if ($a_set_errors) {
                        $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_tst_offline', true);
                    }
                    return false;
                }
            }
            return true;
        }
        $tst_ref = $this->getSettings()->getQualifiedTest();
        if (!$this->tree->isInTree($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_no_qt');
            }
            return false;
        }
        if (!$this->checkTestOnline($tst_ref)) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_QTEST, 'crs_loc_err_stat_tst_offline', true);
            }
            return false;
        }
        return true;
    }

    protected function lookupQuestionsAssigned(int $a_test_ref_id): bool
    {
        if (ilLOUtils::lookupRandomTest(ilObject::_lookupObjId($a_test_ref_id))) {
            foreach ($this->getObjectives() as $objective_id) {
                $seq = ilLORandomTestQuestionPools::lookupSequences(
                    $this->parent_obj->getId(),
                    $objective_id,
                    ilObject::_lookupObjId($a_test_ref_id)
                );
                if ($seq === []) {
                    return false;
                }
            }
        } else {
            foreach ($this->getObjectives() as $objective_id) {
                $qsts = ilCourseObjectiveQuestion::lookupQuestionsByObjective(
                    ilObject::_lookupObjId($a_test_ref_id),
                    $objective_id
                );
                if ($qsts === []) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function getObjectivesStatus(bool $a_set_errors = true): bool
    {
        if (!$this->getObjectivesAvailableStatus($a_set_errors)) {
            return false;
        }

        $num_active = ilCourseObjective::_getCountObjectives($this->getParentObject()->getId(), true);
        if ($num_active === 0) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_lo');
            }
            return false;
        }
        foreach (ilCourseObjective::_getObjectiveIds($this->getParentObject()->getId(), true) as $objective_id) {
            $obj = new ilCourseObjectiveMaterials($objective_id);
            if ($obj->getMaterials() === []) {
                if ($a_set_errors) {
                    $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_mat');
                }
                return false;
            }
        }
        // check for assigned initial test questions
        if ($this->getSettings()->worksWithInitialTest() && !$this->getSettings()->hasSeparateInitialTests()) {
            // check for assigned questions
            if (!$this->lookupQuestionsAssigned($this->getSettings()->getInitialTest())) {
                if ($a_set_errors) {
                    $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_qst');
                }
                return false;
            }
        }
        // check for assigned questions
        if (!$this->getSettings()->hasSeparateQualifiedTests() && !$this->lookupQuestionsAssigned($this->getSettings()->getQualifiedTest())) {
            if ($a_set_errors) {
                $this->appendFailure(self::SECTION_OBJECTIVES, 'crs_loc_err_no_active_qst');
            }
            return false;
        }
        return true;
    }

    protected function getStartStatus(): bool
    {
        return true;
    }

    protected function checkNumberOfTries(): bool
    {
        $qt = $this->getSettings()->getQualifiedTest();
        if (!$qt) {
            return true;
        }

        $factory = new ilObjectFactory();
        $tst = $factory->getInstanceByRefId($qt, false);

        if (!$tst instanceof ilObjTest) {
            return true;
        }
        $tries = $tst->getNrOfTries();
        if (!$tries) {
            return true;
        }

        $obj_tries = 0;
        foreach ($this->getObjectives() as $objective) {
            $obj_tries += ilCourseObjective::lookupMaxPasses($objective);
        }
        return $obj_tries <= $tries;
    }

    protected function checkTestOnline(int $a_ref_id): bool
    {
        return !ilObjTestAccess::_isOffline(ilObject::_lookupObjId($a_ref_id));
    }
}
