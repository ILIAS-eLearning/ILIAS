<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Survey phrases GUI class
*
* The ilSurveyPhrases GUI class creates the GUI output for
* survey phrases (collections of survey categories)
* of ordinal survey question types.
*
* @author		Helmut Schottmüller <ilias@aurealis.de>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
class ilSurveyPhrasesGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilDB
     */
    protected $db;

    public $object;
    public $gui_object;
    public $lng;
    public $tpl;
    public $ctrl;
    public $tree;
    public $ref_id;
    
    /**
    * ilSurveyPhrasesGUI constructor
    *
    */
    public function __construct($a_object)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->db = $DIC->database();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $ilCtrl = $DIC->ctrl();
        $tree = $DIC->repositoryTree();

        include_once "./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrases.php";
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->gui_object = $a_object;
        $this->object = new ilSurveyPhrases();
        $this->tree = $tree;
        $this->ref_id = $a_object->ref_id;
        $this->ctrl->saveParameter($this, "p_id");
    }
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->getCommand($cmd);
        switch ($next_class) {
            default:
                $ret =&$this->$cmd();
                break;
        }
        return $ret;
    }

    /**
    * Retrieves the ilCtrl command
    */
    public function getCommand($cmd)
    {
        return $cmd;
    }
    
    /**
    * Creates a confirmation form to delete personal phases from the database
    */
    public function deletePhrase()
    {
        ilUtil::sendInfo();

        $checked_phrases = $_POST['phrase'];
        if (count($checked_phrases)) {
            ilUtil::sendQuestion($this->lng->txt("qpl_confirm_delete_phrases"));
            $this->deletePhrasesForm($checked_phrases);
            return;
        } else {
            ilUtil::sendInfo($this->lng->txt("qpl_delete_phrase_select_none"));
            $this->phrases();
            return;
        }
    }

    /**
    * Displays a form to manage the user created phrases
    *
    * @access	public
    */
    public function phrases()
    {
        $rbacsystem = $this->rbacsystem;
        $ilToolbar = $this->toolbar;
        
        $this->ctrl->setParameter($this, "p_id", "");
        
        if ($rbacsystem->checkAccess("write", $this->ref_id)) {
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("phrase_new");
            $button->setUrl($this->ctrl->getLinkTarget($this, "newPhrase"));
            $ilToolbar->addButtonInstance($button);
        
            include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyPhrasesTableGUI.php";
            $table_gui = new ilSurveyPhrasesTableGUI($this, 'phrases');
            $phrases =&ilSurveyPhrases::_getAvailablePhrases(1);
            $data = array();
            foreach ($phrases as $phrase_id => $phrase_array) {
                $categories =&ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
                array_push($data, array('phrase_id' => $phrase_id, 'phrase' => $phrase_array["title"], 'answers' => join($categories, ", ")));
            }
            $table_gui->setData($data);
            $this->tpl->setContent($table_gui->getHTML());
        } else {
            ilUtil::sendInfo($this->lng->txt("cannot_manage_phrases"));
        }
    }

    /**
    * cancel delete phrases
    */
    public function cancelDeletePhrase()
    {
        $this->ctrl->redirect($this, "phrases");
    }
    
    /**
    * confirm delete phrases
    */
    public function confirmDeletePhrase()
    {
        $phrases = $_POST['phrase'];
        $this->object->deletePhrases($phrases);
        ilUtil::sendSuccess($this->lng->txt("qpl_phrases_deleted"), true);
        $this->ctrl->redirect($this, "phrases");
    }

    protected function getCategoriesForPhrase($phrase_id)
    {
        $ilDB = $this->db;
        
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
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
    
    protected function getPhraseTitle($phrase_id)
    {
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
        return null;
    }
    
    /**
    * Creates a confirmation form to delete personal phases from the database
    *
    * @param array $checked_phrases An array with the id's of the phrases checked for deletion
    */
    public function deletePhrasesForm($checked_phrases)
    {
        include_once "./Modules/SurveyQuestionPool/classes/tables/class.ilSurveyPhrasesTableGUI.php";
        $table_gui = new ilSurveyPhrasesTableGUI($this, 'phrases', true);
        $phrases =&ilSurveyPhrases::_getAvailablePhrases(1);
        $data = array();
        foreach ($checked_phrases as $phrase_id) {
            $phrase_array = $phrases[$phrase_id];
            $categories =&ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
            array_push($data, array('phrase_id' => $phrase_id, 'phrase' => $phrase_array["title"], 'answers' => join($categories, ", ")));
        }
        $table_gui->setData($data);
        $this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());
    }
    
    public function cancelEditPhrase()
    {
        $this->ctrl->redirect($this, 'phrases');
    }
    
    public function saveEditPhrase()
    {
        $result = $this->writePostData();
        if ($result == 0) {
            if ($_GET['p_id']) {
                $this->object->updatePhrase($_GET['p_id']);
                ilUtil::sendSuccess($this->lng->txt('phrase_saved'), true);
            } else {
                $this->object->savePhrase();
                ilUtil::sendSuccess($this->lng->txt('phrase_added'), true);
            }
            $this->ctrl->redirect($this, 'phrases');
        }
    }

    /**
    * Evaluates a posted edit form and writes the form data in the question object
    *
    * @return integer A positive value, if one of the required fields wasn't set, else 0
    * @access private
    */
    public function writePostData($always = false)
    {
        $ilDB = $this->db;
        $hasErrors = (!$always) ? $this->phraseEditor(true) : false;
        if (!$hasErrors) {
            $this->object->title = $_POST["title"];
            include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
            $categories = new SurveyCategories();
            foreach ($_POST['answers']['answer'] as $key => $value) {
                if (strlen($value)) {
                    $categories->addCategory($value, $_POST['answers']['other'][$key], 0, null, $_POST['answers']['scale'][$key]);
                }
            }
            if (strlen($_POST['answers']['neutral'])) {
                $categories->addCategory($_POST['answers']['neutral'], 0, 1, null, $_POST['answers_neutral_scale']);
            }
            $this->object->categories = $categories;
            return 0;
        } else {
            return 1;
        }
    }
    
    public function newPhrase()
    {
        $this->ctrl->redirect($this, 'phraseEditor');
    }
    
    public function editPhrase()
    {
        if (!array_key_exists('phrase', $_POST)) {
            ilUtil::sendFailure($this->lng->txt('no_phrase_selected'), true);
            $this->ctrl->redirect($this, 'phrases');
        }
        if ((array_key_exists('phrase', $_POST)) && count($_POST['phrase']) > 1) {
            ilUtil::sendFailure($this->lng->txt('select_max_one_item'), true);
            $this->ctrl->redirect($this, 'phrases');
        }
        $phrase_id = (array_key_exists('phrase', $_POST)) ? $_POST['phrase'][key($_POST['phrase'])] : null;
        if ($phrase_id) {
            $this->ctrl->setParameter($this, 'p_id', $phrase_id);
        }
        $this->ctrl->redirect($this, 'phraseEditor');
    }

    public function phraseEditor($checkonly = false)
    {
        $save = (strcmp($this->ctrl->getCmd(), "saveEditPhrase") == 0) ? true : false;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'phraseEditor'));
        $form->setTitle($this->lng->txt('edit_phrase'));
        $form->setMultipart(false);
        $form->setTableWidth("100%");
        $form->setId("phraseeditor");

        $phrase_id = $_GET['p_id'];

        // title
        $title = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title->setValue($this->getPhraseTitle($phrase_id));
        $title->setRequired(true);
        $form->addItem($title);

        // Answers
        include_once "./Modules/SurveyQuestionPool/classes/class.ilCategoryWizardInputGUI.php";
        $answers = new ilCategoryWizardInputGUI($this->lng->txt("answers"), "answers");
        $answers->setRequired(true);
        $answers->setAllowMove(true);
        $answers->setShowWizard(false);
        $answers->setShowSavePhrase(false);
        $answers->setUseOtherAnswer(false);
        $answers->setShowNeutralCategory(true);
        $answers->setNeutralCategoryTitle($this->lng->txt('matrix_neutral_answer'));
        include_once "./Modules/SurveyQuestionPool/classes/class.SurveyCategories.php";
        $categories =&$this->getCategoriesForPhrase($phrase_id);
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
        return $errors;
    }
}
