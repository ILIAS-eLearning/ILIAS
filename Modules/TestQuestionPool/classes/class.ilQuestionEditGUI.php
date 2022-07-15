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

/**
 * Class ilQuestionEditGUI
 * @author		Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilQuestionEditGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI, assKprimChoiceGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assNumericGUI, assTextSubsetGUI, assSingleChoiceGUI, assTextQuestionGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assErrorTextGUI, assOrderingHorizontalGUI, assTextSubsetGUI, assFormulaQuestionGUI
 * @ilCtrl_Calls ilQuestionEditGUI: assLongMenuGUI
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilQuestionEditGUI
{
    private \ilGlobalTemplateInterface $main_tpl;
    private \ILIAS\TestQuestionPool\InternalRequestService $request;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lng;
    private ilRbacSystem $rbac_system;
    private ?int $questionid = null;
    private ?int $poolrefid = null;
    private ?int $poolobjid = null;
    private ?string $questiontype = null;
    /** @var array{object: object, method: string, parameters: string}[] */
    private array $new_id_listeners;
    private int $new_id_listener_cnt;
    private bool $selfassessmenteditingmode = false;
    private ?int $defaultnroftries = null;
    private ?ilPageConfig $page_config = null;

    public function __construct()
    {
        global $DIC;

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC['ilCtrl'];
        $this->request = $DIC->testQuestionPool()->internal()->request();
        $this->lng = $DIC->language();
        $this->rbac_system = $DIC->rbac()->system();

        if ($this->request->raw('qpool_ref_id')) {
            $this->setPoolRefId($this->request->raw('qpool_ref_id'));
        } elseif ($this->request->raw('qpool_obj_id')) {
            $this->setPoolObjId($this->request->raw('qpool_obj_id'));
        }
        $this->setQuestionId($this->request->getQuestionId());
        $this->setQuestionType($this->request->raw('q_type'));
        $this->lng->loadLanguageModule('assessment');

        $this->ctrl->saveParameter($this, ['qpool_ref_id', 'qpool_obj_id', 'q_id', 'q_type']);


        $this->new_id_listeners = [];
        $this->new_id_listener_cnt = 0;
    }
    
    public function setSelfAssessmentEditingMode(bool $a_selfassessmenteditingmode) : void
    {
        $this->selfassessmenteditingmode = $a_selfassessmenteditingmode;
    }

    public function getSelfAssessmentEditingMode() : bool
    {
        return $this->selfassessmenteditingmode;
    }
    
    public function setDefaultNrOfTries(?int $a_defaultnroftries) : void
    {
        $this->defaultnroftries = $a_defaultnroftries;
    }
    
    public function getDefaultNrOfTries() : ?int
    {
        return $this->defaultnroftries;
    }

    public function setPageConfig(ilPageConfig $a_val) : void
    {
        $this->page_config = $a_val;
    }

    public function getPageConfig() : ?ilPageConfig
    {
        return $this->page_config;
    }

    public function addNewIdListener(object $a_object, string $a_method, string $a_parameters = '') : void
    {
        $cnt = $this->new_id_listener_cnt;
        $this->new_id_listeners[$cnt]['object'] = $a_object;
        $this->new_id_listeners[$cnt]['method'] = $a_method;
        $this->new_id_listeners[$cnt]['parameters'] = $a_parameters;
        $this->new_id_listener_cnt++;
    }

    public function executeCommand() : string
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            default:
                $q_gui = assQuestionGUI::_getQuestionGUI(
                    $this->getQuestionType() ?? '',
                    $this->getQuestionId()
                );
                $q_gui->object->setSelfAssessmentEditingMode(
                    $this->getSelfAssessmentEditingMode()
                );
                if ($this->getDefaultNrOfTries() > 0) {
                    $q_gui->object->setDefaultNrOfTries(
                        $this->getDefaultNrOfTries()
                    );
                }

                if (is_object($this->page_config)) {
                    $q_gui->object->setPreventRteUsage($this->getPageConfig()->getPreventRteUsage());
                }
                $q_gui->object->setObjId((int) $this->getPoolObjId());

                for ($i = 0; $i < $this->new_id_listener_cnt; $i++) {
                    $object = $this->new_id_listeners[$i]['object'];
                    $method = $this->new_id_listeners[$i]['method'];
                    $parameters = $this->new_id_listeners[$i]['parameters'];
                    $q_gui->addNewIdListener(
                        $object,
                        $method,
                        $parameters
                    );
                }

                $count = $q_gui->object->usageNumber();
                if ($count > 0) {
                    if ($this->rbac_system->checkAccess('write', $this->getPoolRefId())) {
                        $this->main_tpl->setOnScreenMessage('info', sprintf($this->lng->txt('qpl_question_is_in_use'), $count));
                    }
                }
                $this->ctrl->setCmdClass(get_class($q_gui));
                $ret = (string) $this->ctrl->forwardCommand($q_gui);
                break;
        }
        
        return $ret;
    }
    
    public function setQuestionId(?int $a_questionid) : void
    {
        $this->questionid = $a_questionid;
        $this->ctrl->setParameter($this, 'q_id', $this->questionid);
    }

    public function getQuestionId() : ?int
    {
        return $this->questionid;
    }

    public function setPoolRefId(?int $a_poolrefid) : void
    {
        $this->poolrefid = $a_poolrefid;
        $this->ctrl->setParameter($this, 'qpool_ref_id', $this->poolrefid);
        
        if ($this->getPoolRefId() > 0) {
            $this->setPoolObjId(ilObject::_lookupObjId($this->getPoolRefId()));
        } else {
            $this->setPoolObjId(null);
        }
    }

    public function getPoolRefId() : ?int
    {
        return $this->poolrefid;
    }

    public function setPoolObjId(?int $a_poolobjid) : void
    {
        $this->poolobjid = $a_poolobjid;
        $this->ctrl->setParameter($this, 'qpool_obj_id', $this->poolobjid);
    }

    public function getPoolObjId() : ?int
    {
        return $this->poolobjid;
    }

    public function setQuestionType(?string $a_questiontype) : void
    {
        $this->questiontype = $a_questiontype;
        $this->ctrl->setParameter($this, 'q_type', $this->questiontype);
    }

    public function getQuestionType() : ?string
    {
        return $this->questiontype;
    }
}
