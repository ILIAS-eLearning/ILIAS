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

use ILIAS\SurveyQuestionPool\Editing\EditingGUIRequest;

/**
 * Survey phrases GUI class
 * The ilSurveyPhrases GUI class creates the GUI output for
 * survey phrases (collections of survey categories)
 * of ordinal survey question types.
 * @author		Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyPhrasesGUI
{
    protected ilPropertyFormGUI $form;
    protected EditingGUIRequest $request;
    protected ilRbacSystem $rbacsystem;
    protected ilToolbarGUI $toolbar;
    protected ilDBInterface $db;

    protected ilSurveyPhrases $object;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrl $ctrl;
    protected ilTree $tree;
    protected int $ref_id;

    public function __construct(
        ilObjSurveyQuestionPoolGUI $a_object
    ) {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->db = $DIC->database();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->object = new ilSurveyPhrases();
        $this->tree = $tree;
        $this->ref_id = $a_object->getRefId();
        $this->ctrl->saveParameter($this, "p_id");
        $this->request = $DIC->surveyQuestionPool()
                                  ->internal()
                                  ->gui()
                                  ->editing()
                                  ->request();
    }

    public function executeCommand(): string
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }
        return (string) $ret;
    }

    /**
     * Creates a confirmation form to delete personal phases from the database
     */
    public function deletePhrase(): void
    {
        $checked_phrases = $this->request->getPhraseIds();
        if (count($checked_phrases) > 0) {
            $this->tpl->setOnScreenMessage('question', $this->lng->txt("qpl_confirm_delete_phrases"));
            $this->deletePhrasesForm($checked_phrases);
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("qpl_delete_phrase_select_none"));
            $this->phrases();
        }
    }

    /**
     * List phrases
     */
    public function phrases(): void
    {
        $rbacsystem = $this->rbacsystem;
        $ilToolbar = $this->toolbar;

        $this->ctrl->setParameter($this, "p_id", "");

        if ($rbacsystem->checkAccess("write", $this->ref_id)) {
            $button = ilLinkButton::getInstance();
            $button->setCaption("phrase_new");
            $button->setUrl($this->ctrl->getLinkTarget($this, "newPhrase"));
            $ilToolbar->addButtonInstance($button);

            $table_gui = new ilSurveyPhrasesTableGUI($this, 'phrases');
            $phrases = ilSurveyPhrases::_getAvailablePhrases(1);
            $data = array();
            foreach ($phrases as $phrase_id => $phrase_array) {
                $categories = ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
                $data[] = array('phrase_id' => $phrase_id,
                                'phrase' => $phrase_array["title"],
                                'answers' => implode(", ", $categories)
                );
            }
            $table_gui->setData($data);
            $this->tpl->setContent($table_gui->getHTML());
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("cannot_manage_phrases"));
        }
    }

    public function cancelDeletePhrase(): void
    {
        $this->ctrl->redirect($this, "phrases");
    }

    public function confirmDeletePhrase(): void
    {
        $phrases = $this->request->getPhraseIds();
        $this->object->deletePhrases($phrases);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("qpl_phrases_deleted"), true);
        $this->ctrl->redirect($this, "phrases");
    }

    /**
     * @todo move this to a repo class
     * @param int $phrase_id
     * @return SurveyCategories
     */
    protected function getCategoriesForPhrase(
        int $phrase_id
    ): SurveyCategories {
        $ilDB = $this->db;

        $categories = new SurveyCategories();
        $result = $ilDB->queryF(
            "SELECT svy_category.title, svy_category.neutral, svy_phrase_cat.sequence FROM svy_phrase_cat, svy_category WHERE svy_phrase_cat.phrase_fi = %s AND svy_phrase_cat.category_fi = svy_category.category_id ORDER BY svy_phrase_cat.sequence ASC",
            array('integer'),
            array($phrase_id)
        );
        if ($result->numRows() > 0) {
            while ($data = $ilDB->fetchAssoc($result)) {
                $categories->addCategory($data["title"], 0, $data["neutral"], null, $data['sequence']);
            }
        }
        return $categories;
    }

    /**
     * @todo move this to a repo class
     */
    protected function getPhraseTitle(
        int $phrase_id
    ): string {
        $ilDB = $this->db;

        $result = $ilDB->queryF(
            "SELECT svy_phrase.title FROM svy_phrase WHERE svy_phrase.phrase_id = %s",
            array('integer'),
            array($phrase_id)
        );
        if ($result->numRows() > 0) {
            $row = $ilDB->fetchAssoc($result);
            return $row['title'];
        }
        return "";
    }

    /**
     * Creates a confirmation form to delete personal phases from the database
     * @param array $checked_phrases array with the id's of the phrases checked for deletion
     */
    public function deletePhrasesForm(
        array $checked_phrases
    ): void {
        $table_gui = new ilSurveyPhrasesTableGUI($this, 'phrases', true);
        $phrases = ilSurveyPhrases::_getAvailablePhrases(1);
        $data = array();
        foreach ($checked_phrases as $phrase_id) {
            $phrase_array = $phrases[$phrase_id];
            $categories = ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
            $data[] = array('phrase_id' => $phrase_id,
                            'phrase' => $phrase_array["title"],
                            'answers' => implode(", ", $categories)
            );
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }

    public function cancelEditPhrase(): void
    {
        $this->ctrl->redirect($this, 'phrases');
    }

    public function saveEditPhrase(): void
    {
        $result = $this->writePostData();
        if ($result === 0) {
            if ($this->request->getPhraseId()) {
                $this->object->updatePhrase($this->request->getPhraseId());
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('phrase_saved'), true);
            } else {
                $this->object->savePhrase();
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('phrase_added'), true);
            }
            $this->ctrl->redirect($this, 'phrases');
        }
    }

    /**
     * Evaluates a posted edit form and writes the form data in the question object
     * @return int a positive value, if one of the required fields wasn't set, else 0
     */
    public function writePostData(
        bool $always = false
    ): int {
        $hasErrors = !$always && $this->phraseEditor(true);
        if (!$hasErrors) {
            $form = $this->form;
            $this->object->title = $form->getInput("title");
            $categories = new SurveyCategories();

            $answers = $this->request->getAnswers();
            foreach ($answers['answer'] as $key => $value) {
                if (strlen($value)) {
                    $categories->addCategory($value, $answers['other'][$key] ?? 0, 0, null, $answers['scale'][$key] ?? null);
                }
            }
            if ($this->request->getNeutral() !== "") {
                $categories->addCategory(
                    $this->request->getNeutral(),
                    0,
                    1,
                    null,
                    $this->request->getNeutralScale() ? (int) $this->request->getNeutralScale() : null
                );
            }
            $this->object->categories = $categories;
            return 0;
        }

        return 1;
    }

    public function newPhrase(): void
    {
        $this->ctrl->redirect($this, 'phraseEditor');
    }

    public function editPhrase(): void
    {
        $ids = $this->request->getPhraseIds();
        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_phrase_selected'), true);
            $this->ctrl->redirect($this, 'phrases');
        }
        if (count($ids) > 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_max_one_item'), true);
            $this->ctrl->redirect($this, 'phrases');
        }
        $phrase_id = $ids[key($ids)];
        if ($phrase_id) {
            $this->ctrl->setParameter($this, 'p_id', $phrase_id);
        }
        $this->ctrl->redirect($this, 'phraseEditor');
    }

    public function phraseEditor(
        bool $checkonly = false
    ): bool {
        $save = strcmp($this->ctrl->getCmd(), "saveEditPhrase") === 0;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'phraseEditor'));
        $form->setTitle($this->lng->txt('edit_phrase'));
        $form->setMultipart(false);
        $form->setTableWidth("100%");
        $form->setId("phraseeditor");

        $phrase_id = $this->request->getPhraseId();

        // title
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setValue($this->getPhraseTitle($phrase_id));
        $title->setRequired(true);
        $form->addItem($title);

        // Answers
        $answers = new ilCategoryWizardInputGUI($this->lng->txt("answers"), "answers");
        $answers->setRequired(true);
        $answers->setAllowMove(true);
        $answers->setShowWizard(false);
        $answers->setShowSavePhrase(false);
        $answers->setUseOtherAnswer(false);
        $answers->setShowNeutralCategory(true);
        $answers->setNeutralCategoryTitle($this->lng->txt('matrix_neutral_answer'));
        $categories = $this->getCategoriesForPhrase($phrase_id);
        if (!$categories->getCategoryCount()) {
            $categories->addCategory("");
        }
        $answers->setValues($categories);
        $answers->setDisabledScale(true);
        $form->addItem($answers);

        $form->addCommandButton("saveEditPhrase", $this->lng->txt("save"));
        $form->addCommandButton("cancelEditPhrase", $this->lng->txt("cancel"));

        $errors = false;

        if ($save) {
            $form->setValuesByPost();
            $errors = !$form->checkInput();
            $form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
            if ($errors) {
                $checkonly = false;
            }
        }

        if (!$checkonly) {
            $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
        }
        $this->form = $form;
        return $errors;
    }
}
