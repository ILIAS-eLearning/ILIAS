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
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Custom\Transformation;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 * @ilCtrl_Calls ilBulkEditQuestionsGUI: ilFormPropertyDispatchGUI
 */
class ilBulkEditQuestionsGUI
{
    public const PARAM_IDS = 'qids';
    public const CMD_EDITTAUTHOR = 'bulkedit_author';
    public const CMD_SAVEAUTHOR = 'bulksave_author';
    public const CMD_EDITLIFECYCLE = 'bulkedit_lifecycle';
    public const CMD_SAVELIFECYCLE = 'bulksave_lifecycle';
    public const CMD_EDITTAXONOMIES = 'bulkedit_taxonomies';
    public const CMD_SAVETAXONOMIES = 'bulksave_taxonomies';
    public const CMD_SAVETAXONOMIESADD = 'bulksave_taxonomies_add';

    public function __construct(
        protected ilGlobalTemplateInterface $tpl,
        protected ilCtrl $ctrl,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected Refinery $refinery,
        protected ServerRequestInterface $request,
        protected RequestWrapper $request_wrapper,
        protected int $qpl_obj_id,
    ) {
    }

    protected array $question_ids = [];

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        $this->ctrl->saveParameter($this, self::PARAM_IDS);

        $this->question_ids = $this->getQuestionIds();

        $out = [];

        if ($this->question_ids === []) {
            $out[] = $this->ui_factory->messageBox()->failure(
                $this->lng->txt('qpl_bulkedit_no_ids')
            );

        } else {

            switch ($cmd) {
                case self::CMD_EDITTAUTHOR:
                    $out[] = $this->getFormAuthor();
                    break;
                case self::CMD_SAVEAUTHOR:
                    $out = array_merge($out, $this->store(
                        $this->getFormAuthor(),
                        $this->getAuthorUpdater()
                    ));
                    break;

                case self::CMD_EDITLIFECYCLE:
                    $out[] = $this->getFormLifecycle();
                    break;
                case self::CMD_SAVELIFECYCLE:
                    $out = array_merge($out, $this->store(
                        $this->getFormLifecycle(),
                        $this->getLifecycleUpdater()
                    ));
                    break;

                case self::CMD_EDITTAXONOMIES:
                    $out[] = $this->ui_factory->legacy()->content($this->getFormTaxonomies()->getHTML());
                    break;
                case self::CMD_SAVETAXONOMIES:
                case self::CMD_SAVETAXONOMIESADD:
                    $out = array_merge($out, $this->storeTaxonomies($this->getFormTaxonomies()));
                    break;

                default:
                    throw new \Exception("'$cmd'" . " not implemented");
            }
        }

        $this->tpl->setContent($this->ui_renderer->render($out));
    }

    protected function getQuestionIds(): array
    {
        if (!$this->request_wrapper->has(self::PARAM_IDS)) {
            return [];
        }
        $trafo = $this->refinery->custom()->transformation(
            fn($v) => array_map('intval', explode(',', $v))
        );
        return $this->request_wrapper->retrieve(self::PARAM_IDS, $trafo);
    }

    protected function store(Form\Standard $form, Closure $update): array
    {
        $out = [];
        $form = $form->withRequest($this->request);
        $data = $form->getData();
        $questions = $this->getQuestions();
        if ($data !== null && $update($questions, $data)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_bulkedit_success'), true);
            $this->ctrl->redirectByClass(ilObjQuestionPoolGUI::class, ilObjQuestionPoolGUI::DEFAULT_CMD);
        }
        $out[] = $form;
        return $out;
    }

    protected function getQuestions(): array
    {
        $questions = [];
        foreach ($this->question_ids as $qid) {
            $questions[] = \assQuestion::instantiateQuestion($qid);
        }
        return $questions;
    }

    protected function getShiftTrafo(): Transformation
    {
        return $this->refinery->custom()->transformation(
            fn(array $v) => array_shift($v)
        );
    }

    protected function getFormAuthor(): Form\Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_SAVEAUTHOR),
            [
                $this->ui_factory->input()->field()
                    ->text($this->lng->txt('author'))
                    ->withRequired(true)
            ]
        )
        ->withAdditionalTransformation($this->getShiftTrafo());
    }

    protected function getAuthorUpdater(): \Closure
    {
        return function (array $questions, string $author) {
            foreach ($questions as $q) {
                $q->setAuthor($author);
                $q->saveQuestionDataToDb();
            }
            return true;
        };
    }

    protected function getFormLifecycle(): Form\Standard
    {
        $lifecycle = \ilAssQuestionLifecycle::getDraftInstance();
        $options = $lifecycle->getSelectOptions($this->lng);
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, self::CMD_SAVELIFECYCLE),
            [
                $this->ui_factory->input()->field()
                    ->select($this->lng->txt('qst_lifecycle'), $options)
                    ->withRequired(true)
            ]
        )
        ->withAdditionalTransformation($this->getShiftTrafo());
    }

    protected function getLifecycleUpdater(): \Closure
    {
        return function (array $questions, string $lifecycle) {
            $lc = ilAssQuestionLifecycle::getInstance($lifecycle);
            foreach ($questions as $q) {
                $q->setLifecycle($lc);
                $q->saveToDb();
            }
            return true;
        };
    }

    protected function getFormTaxonomies(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVETAXONOMIES));
        $taxonomy_ids = \ilObjTaxonomy::getUsageOfObject($this->qpl_obj_id);

        //taken from assQuestionGUI::populateTaxonomyFormSection
        foreach ($taxonomy_ids as $taxonomy_id) {
            $taxonomy = new ilObjTaxonomy($taxonomy_id);
            $label = sprintf($this->lng->txt('qpl_qst_edit_form_taxonomy'), $taxonomy->getTitle());
            $postvar = "tax_node_assign_$taxonomy_id";
            // selector not working due to failing modals, actually:
            // $taxSelect = new ilTaxSelectInputGUI($taxonomy->getId(), $postvar, true);
            $taxSelect = new ilTaxAssignInputGUI($taxonomy->getId(), true, $label, $postvar, true);
            $taxSelect->setTitle($label);
            $form->addItem($taxSelect);
        }
        $form->addCommandButton(self::CMD_SAVETAXONOMIES, $this->lng->txt("qpl_bulk_save_overwrite"));
        $form->addCommandButton(self::CMD_SAVETAXONOMIESADD, $this->lng->txt("qpl_bulk_save_add"));
        return $form;
    }

    protected function storeTaxonomies(ilPropertyFormGUI $form): void
    {
        $questions = $this->getQuestions();
        $post = $this->request->getParsedBody();
        $form_cmd = array_shift($post['cmd']);
        $overwrite = ($form_cmd === $this->lng->txt('qpl_bulk_save_overwrite'));

        $taxonomy_ids = \ilObjTaxonomy::getUsageOfObject($this->qpl_obj_id);
        foreach ($taxonomy_ids as $taxonomy_id) {
            $postvar = "tax_node_assign_$taxonomy_id";
            foreach ($questions as $q) {
                $assignments = new ilTaxNodeAssignment(ilObject::_lookupType($q->getObjId()), $q->getObjId(), 'quest', $taxonomy_id);
                $assigned_nodes = $assignments->getAssignmentsOfItem($q->getId());

                $skip = [];
                foreach ($assigned_nodes as $existing) {
                    if ($overwrite && !in_array($existing["node_id"], $post[$postvar])
                    ) {
                        $assignments->deleteAssignment((int) $existing["node_id"], $q->getId());
                    } else {
                        $skip[] = (int) $existing["node_id"];
                    }
                }

                $values = $post[$postvar];
                foreach ($values as $value) {
                    if (!in_array((int) $value, $skip)) {
                        $assignments->addAssignment((int) $value, $q->getId());
                    }
                }
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_bulkedit_success'), true);
        $this->ctrl->redirectByClass(ilObjQuestionPoolGUI::class, ilObjQuestionPoolGUI::DEFAULT_CMD);
    }

}
