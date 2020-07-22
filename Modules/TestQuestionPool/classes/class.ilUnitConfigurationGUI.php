<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Class ilUnitConfigurationGUI
 * @abstract
 */
abstract class ilUnitConfigurationGUI
{
    /**
     * @var ilPropertyFormGUI
     */
    protected $unit_cat_form;

    /**
     * @var ilPropertyFormGUI
     */
    protected $unit_form;

    /**
     * @var $unitConfiguration ilUnitConfigurationRepository
     */
    protected $repository = null;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @param ilUnitConfigurationRepository $repository
     */
    public function __construct(ilUnitConfigurationRepository $repository)
    {
        /**
         * @var $lng    ilLanguage
         * @var $ilCtrl ilCtrl
         * @var $tpl    ilTemplate
         */
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];

        $this->repository = $repository;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;

        $this->lng->loadLanguageModule('assessment');
    }

    /**
     * @return string
     * @abstract
     */
    abstract protected function getDefaultCommand();

    /**
     * @return string
     * @abstract
     */
    abstract public function getUnitCategoryOverviewCommand();

    /**
     * @return boolean
     * @abstract
     */
    abstract public function isCRUDContext();

    /**
     * @return string
     * @abstract
     */
    abstract public function getUniqueId();

    /**
     * @param array $categories
     */
    abstract protected function showUnitCategories(array $categories);

    /**
     * @param int  $id
     * @param bool $for_CRUD
     * @return assFormulaQuestionUnitCategory
     */
    protected function getCategoryById($id, $for_CRUD = true)
    {
        $category = $this->repository->getUnitCategoryById($id);
        if ($for_CRUD && $category->getQuestionFi() != $this->repository->getConsumerId()) {
            ilUtil::sendFailure($this->lng->txt('change_adm_categories_not_allowed'), true);
            $this->ctrl->redirect($this, $this->getDefaultCommand());
        }
        return $category;
    }

    /**
     *
     */
    protected function handleSubtabs()
    {
    }

    /**
     * @param string $cmd
     */
    protected function checkPermissions($cmd)
    {
    }

    /**
     *
     */
    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'category_id');

        $cmd = $this->ctrl->getCmd($this->getDefaultCommand());
        $nextClass = $this->ctrl->getNextClass($this);
        switch ($nextClass) {
            default:
                $this->checkPermissions($cmd);
                $this->$cmd();
                break;
        }

        $this->handleSubtabs();
    }

    /**
     *
     */
    protected function confirmDeleteUnit()
    {
        if (!isset($_GET['unit_id'])) {
            $this->showUnitsOfCategory();
            return;
        }

        $_POST['unit_ids'] = array($_GET['unit_id']);
        $this->confirmDeleteUnits();
    }

    /**
     *
     */
    protected function confirmDeleteUnits()
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        if (!isset($_POST['unit_ids']) || !is_array($_POST['unit_ids'])) {
            $this->showUnitsOfCategory();
            return;
        }

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteUnits'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteUnits');
        $confirmation->setCancel($this->lng->txt('cancel'), 'showUnitsOfCategory');

        $errors = array();
        $num_to_confirm = 0;
        foreach ($_POST['unit_ids'] as $unit_id) {
            try {
                $unit = $this->repository->getUnit((int) $unit_id);
                if (!$unit) {
                    continue;
                }

                if ($check_result = $this->repository->checkDeleteUnit($unit->getId())) {
                    $errors[] = $unit->getDisplayString() . ' - ' . $check_result;
                    continue;
                }

                $confirmation->addItem('unit_ids[]', $unit->getId(), $unit->getDisplayString());
                ++$num_to_confirm;
            } catch (ilException $e) {
                continue;
            }
        }

        if ($errors) {
            $num_errors = count($errors);

            $error_message = array_map(function ($message) {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors == 1) {
                ilUtil::sendFailure($this->lng->txt('un_unit_deletion_errors_f_s') . '<ul>' . implode('', $error_message) . '<ul>');
            } else {
                ilUtil::sendFailure($this->lng->txt('un_unit_deletion_errors_f') . '<ul>' . implode('', $error_message) . '<ul>');
            }
        }

        if ($num_to_confirm) {
            if ($num_to_confirm == 1) {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_units_s'));
            } else {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_units'));
            }

            $this->tpl->setContent($confirmation->getHTML());
        } else {
            $this->showUnitsOfCategory();
        }
    }

    /**
     *
     */
    public function deleteUnits()
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        if (!is_array($_POST['unit_ids']) || !$_POST['unit_ids']) {
            $this->showUnitsOfCategory();
            return;
        }

        $errors = array();
        $num_deleted = 0;
        foreach ($_POST['unit_ids'] as $unit_id) {
            try {
                $unit = $this->repository->getUnit((int) $unit_id);
                if (!$unit) {
                    continue;
                }

                $check_result = $this->repository->deleteUnit($unit->getId());
                if (!is_null($check_result)) {
                    $errors[] = $unit->getDisplayString() . ' - ' . $check_result;
                    continue;
                }

                ++$num_deleted;
            } catch (ilException $e) {
                continue;
            }
        }

        if ($errors) {
            $num_errors = count($errors);

            $error_message = array_map(function ($message) {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors == 1) {
                ilUtil::sendFailure($this->lng->txt('un_unit_deletion_errors_p_s') . '<ul>' . implode('', $error_message) . '<ul>');
            } else {
                ilUtil::sendFailure($this->lng->txt('un_unit_deletion_errors_p') . '<ul>' . implode('', $error_message) . '<ul>');
            }
        }

        if ($num_deleted) {
            if ($num_deleted == 1) {
                ilUtil::sendSuccess($this->lng->txt('un_deleted_units_s'));
            } else {
                ilUtil::sendSuccess($this->lng->txt('un_deleted_units'));
            }
        }

        $this->showUnitsOfCategory();
    }

    /**
     *
     */
    protected function saveOrder()
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        if (!isset($_POST['sequence']) || !is_array($_POST['sequence'])) {
            $this->showUnitsOfCategory();
            return;
        }

        foreach ($_POST['sequence'] as $id => $sequence) {
            $sorting_value = str_replace(',', '.', $sequence);
            $sorting_value = (int) $sorting_value * 100;
            $this->repository->saveUnitOrder((int) $id, $sorting_value);
        }

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        $this->showUnitsOfCategory();
        return;
    }

    /**
     * Save a unit
     */
    protected function saveUnit()
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById((int) $_GET['category_id']);
        $unit = $this->repository->getUnit((int) $_GET['unit_id']);

        if ($this->repository->isUnitInUse($unit->getId())) {
            $this->showUnitModificationForm();
            return;
        }

        $this->initUnitForm($category, $unit);
        if ($this->unit_form->checkInput()) {
            $unit->setUnit($this->unit_form->getInput('unit_title'));
            $unit->setFactor((float) $this->unit_form->getInput('factor'));
            $unit->setBaseUnit((int) $this->unit_form->getInput('base_unit') != $unit->getId() ? (int) $this->unit_form->getInput('base_unit') : 0);
            $unit->setCategory($category->getId());
            $this->repository->saveUnit($unit);
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
            $this->showUnitsOfCategory();
            return;
        } else {
            $this->unit_form->setValuesByPost();
        }

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    /**
     *
     */
    protected function showUnitModificationForm()
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById((int) $_GET['category_id']);
        $unit = $this->repository->getUnit((int) $_GET['unit_id']);

        $this->initUnitForm($category, $unit);
        $this->unit_form->setValuesByArray(array(
            'factor' => $unit->getFactor(),
            'unit_title' => $unit->getUnit(),
            'base_unit' => ($unit->getBaseUnit() != $unit->getId() ? $unit->getBaseUnit() : 0)
        ));

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    /**
     * Adds a new unit
     */
    protected function addUnit()
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById((int) $_GET['category_id']);

        $this->initUnitForm($category);
        if ($this->unit_form->checkInput()) {
            $unit = new assFormulaQuestionUnit();
            $unit->setUnit($this->unit_form->getInput('unit_title'));
            $unit->setCategory($category->getId());

            $this->repository->createNewUnit($unit);

            $unit->setBaseUnit((int) $this->unit_form->getInput('base_unit'));
            $unit->setFactor((float) $this->unit_form->getInput('factor'));

            $this->repository->saveUnit($unit);

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
            $this->showUnitsOfCategory();
            return;
        }

        $this->unit_form->setValuesByPost();

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    /**
     *
     */
    protected function showUnitCreationForm()
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById((int) $_GET['category_id']);

        $this->initUnitForm($category);
        $this->unit_form->setValuesByArray(array(
            'factor' => 1,
            'unit_title' => $this->lng->txt('unit_placeholder')
        ));

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    /**
     * @param assFormulaQuestionUnitCategory $category
     * @param assFormulaQuestionUnit         $unit
     * @return ilPropertyFormGUI
     */
    protected function initUnitForm(assFormulaQuestionUnitCategory $category = null, assFormulaQuestionUnit $unit = null)
    {
        if ($this->unit_form instanceof ilPropertyFormGUI) {
            return $this->unit_form;
        }

        $unit_in_use = false;
        if ($unit instanceof assFormulaQuestionUnit && $this->repository->isUnitInUse($unit->getId())) {
            $unit_in_use = true;
        }

        $this->unit_form = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($this->lng->txt('unit'), 'unit_title');
        $title->setDisabled($unit_in_use);
        $title->setRequired(true);
        $this->unit_form->addItem($title);

        $baseunit = new ilSelectInputGUI($this->lng->txt('baseunit'), 'base_unit');
        $items = $this->repository->getCategorizedUnits();
        $options = array();
        $category_name = '';
        $new_category = false;
        foreach ((array) $items as $item) {
            if (
                $unit instanceof assFormulaQuestionUnit &&
                $unit->getId() == $item->getId()
            ) {
                continue;
            }
            
            /**
             * @var $item assFormulaQuestionUnitCategory|assFormulaQuestionUnitCategory
             */
            if ($item instanceof assFormulaQuestionUnitCategory) {
                if ($category_name != $item->getDisplayString()) {
                    $new_category = true;
                    $category_name = $item->getDisplayString();
                }
                continue;
            }
            $options[$item->getId()] = $item->getDisplayString() . ($new_category ? ' (' . $category_name . ')' : '');
            $new_category = false;
        }
        $baseunit->setDisabled($unit_in_use);
        $baseunit->setOptions(array(0 => $this->lng->txt('no_selection')) + $options);
        $this->unit_form->addItem($baseunit);

        $factor = new ilNumberInputGUI($this->lng->txt('factor'), 'factor');
        $factor->setRequired(true);
        $factor->setSize(3);
        $factor->setMinValue(0);
        $factor->allowDecimals(true);
        $factor->setDisabled($unit_in_use);
        $this->unit_form->addItem($factor);

        if (null === $unit) {
            $this->unit_form->setTitle($this->lng->txt('new_unit'));
            $this->unit_form->setFormAction($this->ctrl->getFormAction($this, 'addUnit'));
            $this->unit_form->addCommandButton('addUnit', $this->lng->txt('save'));
        } else {
            $this->ctrl->setParameter($this, 'unit_id', $unit->getId());
            if ($unit_in_use) {
                $this->unit_form->setFormAction($this->ctrl->getFormAction($this, 'showUnitsOfCategory'));
            } else {
                $this->unit_form->addCommandButton('saveUnit', $this->lng->txt('save'));
                $this->unit_form->setFormAction($this->ctrl->getFormAction($this, 'saveUnit'));
            }
            $this->unit_form->setTitle(sprintf($this->lng->txt('un_sel_cat_sel_unit'), $category->getDisplayString(), $unit->getDisplayString()));
        }

        $this->unit_form->addCommandButton('showUnitsOfCategory', $this->lng->txt('cancel'));
        return $this->unit_form;
    }

    /**
     *
     */
    protected function showUnitsOfCategory()
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         */
        global $DIC;
        $ilToolbar = $DIC['ilToolbar'];

        $category = $this->getCategoryById((int) $_GET['category_id'], false);

        $this->tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
        $this->tpl->addJavaScript("./Services/Form/js/Form.js");
        $this->lng->loadLanguageModule('form');

        require_once 'Modules/TestQuestionPool/classes/tables/class.ilUnitTableGUI.php';
        $ilToolbar->addButton($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, $this->getUnitCategoryOverviewCommand()));
        if ($this->isCRUDContext()) {
            $ilToolbar->addButton($this->lng->txt('un_add_unit'), $this->ctrl->getLinkTarget($this, 'showUnitCreationForm'));
        }
        $table = new ilUnitTableGUI($this, 'showUnitsOfCategory', $category);
        $units = $this->repository->loadUnitsForCategory($category->getId());
        $data = array();
        foreach ($units as $unit) {
            /**
             * @var $unit assFormulaQuestionUnit
             */
            $data[] = array(
                'unit_id' => $unit->getId(),
                'unit' => $unit->getUnit(),
                'baseunit' => $unit->getBaseunitTitle(),
                'baseunit_id' => $unit->getBaseUnit(),
                'factor' => $unit->getFactor(),
                'sequence' => $unit->getSequence(),
            );
        }
        $table->setData($data);

        $this->tpl->setContent($table->getHTML());
    }

    /**
     *
     */
    protected function showGlobalUnitCategories()
    {
        $categories = array_filter(
            $this->repository->getAllUnitCategories(),
            function (assFormulaQuestionUnitCategory $category) {
                return !$category->getQuestionFi() ? true : false;
            }
        );
        $data = array();
        foreach ($categories as $category) {
            /**
             * @var $category assFormulaQuestionUnitCategory
             */
            $data[] = array(
                'category_id' => $category->getId(),
                'category' => $category->getDisplayString()
            );
        }

        $this->showUnitCategories($data);
    }

    /**
     *
     */
    protected function confirmDeleteCategory()
    {
        if (!isset($_GET['category_id'])) {
            $this->{$this->getUnitCategoryOverviewCommand()}();
            return;
        }
        $_POST['category_ids'] = array($_GET['category_id']);

        $this->confirmDeleteCategories();
    }

    /**
     *
     */
    protected function confirmDeleteCategories()
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        if (!isset($_POST['category_ids']) || !is_array($_POST['category_ids'])) {
            $this->{$this->getUnitCategoryOverviewCommand()}();
            return;
        }

        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteCategories'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteCategories');
        $confirmation->setCancel($this->lng->txt('cancel'), $this->getUnitCategoryOverviewCommand());

        $errors = array();
        $num_to_confirm = 0;
        foreach ($_POST['category_ids'] as $category_id) {
            try {
                $category = $this->repository->getUnitCategoryById((int) $category_id);
            } catch (ilException $e) {
                continue;
            }

            if (!$this->repository->isCRUDAllowed((int) $category_id)) {
                $errors[] = $category->getDisplayString() . ' - ' . $this->lng->txt('change_adm_categories_not_allowed');
                continue;
            }

            $possible_error = $this->repository->checkDeleteCategory($category_id);
            if (strlen($possible_error)) {
                $errors[] = $category->getDisplayString() . ' - ' . $possible_error;
                continue;
            }

            $confirmation->addItem('category_ids[]', $category->getId(), $category->getDisplayString());
            ++$num_to_confirm;
        }

        if ($errors) {
            $num_errors = count($errors);

            $error_message = array_map(function ($message) {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors == 1) {
                ilUtil::sendFailure($this->lng->txt('un_cat_deletion_errors_f_s') . '<ul>' . implode('', $error_message) . '<ul>');
            } else {
                ilUtil::sendFailure($this->lng->txt('un_cat_deletion_errors_f') . '<ul>' . implode('', $error_message) . '<ul>');
            }
        }

        if ($num_to_confirm) {
            if ($num_to_confirm == 1) {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_categories_s'));
            } else {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_categories'));
            }

            $this->tpl->setContent($confirmation->getHTML());
        } else {
            $this->{$this->getUnitCategoryOverviewCommand()}();
        }
    }

    /**
     *
     */
    protected function deleteCategories()
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        if (!is_array($_POST['category_ids']) || !$_POST['category_ids']) {
            $this->{$this->getUnitCategoryOverviewCommand()}();
            return;
        }

        $errors = array();
        $num_deleted = 0;
        foreach ($_POST['category_ids'] as $category_id) {
            try {
                $category = $this->repository->getUnitCategoryById((int) $category_id);
            } catch (ilException $e) {
                continue;
            }

            if (!$this->repository->isCRUDAllowed((int) $category_id)) {
                $errors[] = $category->getDisplayString() . ' - ' . $this->lng->txt('change_adm_categories_not_allowed');
                continue;
            }

            $possible_error = $this->repository->deleteCategory($category_id);
            if (strlen($possible_error)) {
                $errors[] = $category->getDisplayString() . ' - ' . $possible_error;
                continue;
            }

            ++$num_deleted;
        }

        if ($errors) {
            $num_errors = count($errors);

            $error_message = array_map(function ($message) {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors == 1) {
                ilUtil::sendFailure($this->lng->txt('un_cat_deletion_errors_p_s') . '<ul>' . implode('', $error_message) . '<ul>');
            } else {
                ilUtil::sendFailure($this->lng->txt('un_cat_deletion_errors_p') . '<ul>' . implode('', $error_message) . '<ul>');
            }
        }

        if ($num_deleted) {
            if ($num_deleted == 1) {
                ilUtil::sendSuccess($this->lng->txt('un_deleted_categories_s'));
            } else {
                ilUtil::sendSuccess($this->lng->txt('un_deleted_categories'));
            }
        }

        $this->{$this->getUnitCategoryOverviewCommand()}();
    }

    /**
     * @param assFormulaQuestionUnitCategory $cat
     * @return ilPropertyFormGUI
     */
    protected function initUnitCategoryForm(assFormulaQuestionUnitCategory $cat = null)
    {
        if ($this->unit_cat_form instanceof ilPropertyFormGUI) {
            return $this->unit_cat_form;
        }

        $this->unit_cat_form = new ilPropertyFormGUI();

        $title = new ilTextInputGUI($this->lng->txt('title'), 'category_name');
        $title->setRequired(true);
        $this->unit_cat_form->addItem($title);

        if (null === $cat) {
            $this->unit_cat_form->setTitle($this->lng->txt('new_category'));
            $this->unit_cat_form->setFormAction($this->ctrl->getFormAction($this, 'addCategory'));
            $this->unit_cat_form->addCommandButton('addCategory', $this->lng->txt('save'));
        } else {
            $this->ctrl->setParameter($this, 'category_id', $cat->getId());
            $this->unit_cat_form->addCommandButton('saveCategory', $this->lng->txt('save'));
            $this->unit_cat_form->setFormAction($this->ctrl->getFormAction($this, 'saveCategory'));
            $this->unit_cat_form->setTitle(sprintf($this->lng->txt('selected_category'), $cat->getDisplayString()));
        }

        $this->unit_cat_form->addCommandButton($this->getUnitCategoryOverviewCommand(), $this->lng->txt('cancel'));
        return $this->unit_cat_form;
    }

    /**
     *
     */
    protected function addCategory()
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $this->initUnitCategoryForm();
        if ($this->unit_cat_form->checkInput()) {
            try {
                $category = new assFormulaQuestionUnitCategory();
                $category->setCategory($this->unit_cat_form->getInput('category_name'));
                $this->repository->saveNewUnitCategory($category);
                ilUtil::sendSuccess($this->lng->txt('saved_successfully'));

                $this->{$this->getUnitCategoryOverviewCommand()}();
                return;
            } catch (ilException $e) {
                $this->unit_cat_form->getItemByPostVar('category_name')->setAlert($this->lng->txt($e->getMessage()));
                ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            }
        }

        $this->unit_cat_form->setValuesByPost();

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }

    /**
     *
     */
    protected function showUnitCategoryCreationForm()
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $this->initUnitCategoryForm();

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }

    /**
     *
     */
    protected function saveCategory()
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $category = $this->getCategoryById((int) $_GET['category_id']);

        $this->initUnitCategoryForm($category);
        if ($this->unit_cat_form->checkInput()) {
            try {
                $category->setCategory($this->unit_cat_form->getInput('category_name'));
                $this->repository->saveCategory($category);
                ilUtil::sendSuccess($this->lng->txt('saved_successfully'));

                $this->{$this->getUnitCategoryOverviewCommand()}();
                return;
            } catch (ilException $e) {
                $this->unit_cat_form->getItemByPostVar('category_name')->setAlert($this->lng->txt($e->getMessage()));
                ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
            }
        }

        $this->unit_cat_form->setValuesByPost();

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }

    /**
     *
     */
    protected function showUnitCategoryModificationForm()
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $category = $this->getCategoryById((int) $_GET['category_id']);

        $this->initUnitCategoryForm($category);
        $this->unit_cat_form->setValuesByArray(array(
            'category_name' => $category->getCategory()
        ));

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }
}
