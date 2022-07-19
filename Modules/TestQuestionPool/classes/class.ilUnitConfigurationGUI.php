<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

/**
 * Class ilUnitConfigurationGUI
 */
abstract class ilUnitConfigurationGUI
{
    protected ilUnitConfigurationRepository $repository;
    protected \ILIAS\TestQuestionPool\InternalRequestService $request;
    protected ?ilPropertyFormGUI $unit_cat_form = null;
    protected ?ilPropertyFormGUI $unit_form = null;
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrlInterface $ctrl;

    public function __construct(ilUnitConfigurationRepository $repository)
    {
        global $DIC;

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->testQuestionPool()->internal()->request();

        $this->repository = $repository;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;

        $this->lng->loadLanguageModule('assessment');
    }

    abstract protected function getDefaultCommand() : string;

    abstract public function getUnitCategoryOverviewCommand() : string;

    abstract public function isCRUDContext() : bool;

    abstract public function getUniqueId() : string;

    abstract protected function showUnitCategories(array $categories) : void;

    protected function getCategoryById(int $id, bool $for_CRUD = true) : assFormulaQuestionUnitCategory
    {
        $category = $this->repository->getUnitCategoryById($id);
        if ($for_CRUD && $category->getQuestionFi() !== $this->repository->getConsumerId()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('change_adm_categories_not_allowed'), true);
            $this->ctrl->redirect($this, $this->getDefaultCommand());
        }

        return $category;
    }

    protected function handleSubtabs() : void
    {
    }

    protected function checkPermissions(string $cmd) : void
    {
    }

    public function executeCommand() : void
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

    protected function confirmDeleteUnit() : void
    {
        if (!$this->request->isset('unit_id')) {
            $this->showUnitsOfCategory();
            return;
        }

        $this->confirmDeleteUnits([$this->request->int('unit_id')]);
    }

    /**
     * @param int[]|null $unit_ids
     * @return void
     * @throws ilCtrlException
     */
    protected function confirmDeleteUnits(array $unit_ids = null) : void
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $unit_ids = $unit_ids ?? $this->request->getUnitIds();
        if (count($unit_ids) === 0) {
            $this->showUnitsOfCategory();
            return;
        }

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteUnits'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteUnits');
        $confirmation->setCancel($this->lng->txt('cancel'), 'showUnitsOfCategory');

        $errors = [];
        $num_to_confirm = 0;
        foreach ($unit_ids as $unit_id) {
            try {
                $unit = $this->repository->getUnit((int) $unit_id);
                if (!$unit) {
                    continue;
                }

                if ($check_result = $this->repository->checkDeleteUnit($unit->getId())) {
                    $errors[] = $unit->getDisplayString() . ' - ' . $check_result;
                    continue;
                }

                $confirmation->addItem('unit_ids[]', (string) $unit->getId(), $unit->getDisplayString());
                ++$num_to_confirm;
            } catch (ilException $e) {
                continue;
            }
        }

        if ($errors) {
            $num_errors = count($errors);

            $error_message = array_map(static function (string $message) : string {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors === 1) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_unit_deletion_errors_f_s') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            } else {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_unit_deletion_errors_f') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            }
        }

        if ($num_to_confirm) {
            if ($num_to_confirm === 1) {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_units_s'));
            } else {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_units'));
            }

            $this->tpl->setContent($confirmation->getHTML());
        } else {
            $this->showUnitsOfCategory();
        }
    }

    public function deleteUnits() : void
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $unit_ids = $this->request->getUnitIds();
        if (count($unit_ids) === 0) {
            $this->showUnitsOfCategory();
            return;
        }

        $errors = [];
        $num_deleted = 0;
        foreach ($unit_ids as $unit_id) {
            try {
                $unit = $this->repository->getUnit($unit_id);
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

            $error_message = array_map(static function (string $message) : string {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors === 1) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_unit_deletion_errors_p_s') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            } else {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_unit_deletion_errors_p') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            }
        }

        if ($num_deleted) {
            if ($num_deleted === 1) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('un_deleted_units_s'));
            } else {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('un_deleted_units'));
            }
        }

        $this->showUnitsOfCategory();
    }

    protected function saveOrder() : void
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        if ($this->request->isset('sequence') || !is_array($this->request->raw('sequence'))) {
            $this->showUnitsOfCategory();
            return;
        }

        $sequences = $this->request->raw('sequence');
        foreach ($sequences as $id => $sequence) {
            $sorting_value = str_replace(',', '.', $sequence);
            $sorting_value = (int) $sorting_value * 100;
            $this->repository->saveUnitOrder((int) $id, $sorting_value);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
        $this->showUnitsOfCategory();
    }

    protected function saveUnit() : void
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById($this->request->int('category_id'));
        $unit = $this->repository->getUnit($this->request->int('unit_id'));

        if ($this->repository->isUnitInUse($unit->getId())) {
            $this->showUnitModificationForm();
            return;
        }

        $this->initUnitForm($category, $unit);
        if ($this->unit_form->checkInput()) {
            $unit->setUnit($this->unit_form->getInput('unit_title'));
            $unit->setFactor((float) $this->unit_form->getInput('factor'));
            $unit->setBaseUnit((int) $this->unit_form->getInput('base_unit') !== $unit->getId() ? (int) $this->unit_form->getInput('base_unit') : 0);
            $unit->setCategory($category->getId());
            $this->repository->saveUnit($unit);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
            $this->showUnitsOfCategory();
            return;
        }

        $this->unit_form->setValuesByPost();

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    protected function showUnitModificationForm() : void
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById($this->request->int('category_id'));
        $unit = $this->repository->getUnit($this->request->int('unit_id'));

        $this->initUnitForm($category, $unit);
        $this->unit_form->setValuesByArray([
            'factor' => $unit->getFactor(),
            'unit_title' => $unit->getUnit(),
            'base_unit' => $unit->getBaseUnit() !== $unit->getId() ? $unit->getBaseUnit() : 0
        ]);

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    protected function addUnit() : void
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById($this->request->int('category_id'));

        $this->initUnitForm($category);
        if ($this->unit_form->checkInput()) {
            $unit = new assFormulaQuestionUnit();
            $unit->setUnit($this->unit_form->getInput('unit_title'));
            $unit->setCategory($category->getId());

            $this->repository->createNewUnit($unit);

            $unit->setBaseUnit((int) $this->unit_form->getInput('base_unit'));
            $unit->setFactor((float) $this->unit_form->getInput('factor'));

            $this->repository->saveUnit($unit);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));
            $this->showUnitsOfCategory();
            return;
        }

        $this->unit_form->setValuesByPost();

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    protected function showUnitCreationForm() : void
    {
        if (!$this->isCRUDContext()) {
            $this->showUnitsOfCategory();
            return;
        }

        $category = $this->getCategoryById($this->request->int('category_id'));

        $this->initUnitForm($category);
        $this->unit_form->setValuesByArray([
            'factor' => 1,
            'unit_title' => $this->lng->txt('unit_placeholder')
        ]);

        $this->tpl->setContent($this->unit_form->getHtml());
    }

    protected function initUnitForm(
        assFormulaQuestionUnitCategory $category = null,
        assFormulaQuestionUnit $unit = null
    ) : ilPropertyFormGUI {
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
        $options = [];
        $category_name = '';
        $new_category = false;
        foreach ($items as $item) {
            if (
                $unit instanceof assFormulaQuestionUnit &&
                $unit->getId() === $item->getId()
            ) {
                continue;
            }

            if ($item instanceof assFormulaQuestionUnitCategory) {
                if ($category_name !== $item->getDisplayString()) {
                    $new_category = true;
                    $category_name = $item->getDisplayString();
                }
                continue;
            }

            $options[$item->getId()] = $item->getDisplayString() . ($new_category ? ' (' . $category_name . ')' : '');
            $new_category = false;
        }
        $baseunit->setDisabled($unit_in_use);
        $baseunit->setOptions([0 => $this->lng->txt('no_selection')] + $options);
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
            $this->unit_form->setTitle(sprintf(
                $this->lng->txt('un_sel_cat_sel_unit'),
                $category->getDisplayString(),
                $unit->getDisplayString()
            ));
        }

        $this->unit_form->addCommandButton('showUnitsOfCategory', $this->lng->txt('cancel'));
        return $this->unit_form;
    }

    protected function showUnitsOfCategory() : void
    {
        global $DIC;

        $ilToolbar = $DIC->toolbar();

        $category = $this->getCategoryById($this->request->int('category_id'), false);

        $this->tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
        $this->tpl->addJavaScript("./Services/Form/js/Form.js");
        $this->lng->loadLanguageModule('form');

        $ilToolbar->addButton(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTarget($this, $this->getUnitCategoryOverviewCommand())
        );
        if ($this->isCRUDContext()) {
            $ilToolbar->addButton(
                $this->lng->txt('un_add_unit'),
                $this->ctrl->getLinkTarget($this, 'showUnitCreationForm')
            );
        }
        $table = new ilUnitTableGUI($this, 'showUnitsOfCategory', $category);
        $units = $this->repository->loadUnitsForCategory($category->getId());
        $data = [];
        foreach ($units as $unit) {
            /** @var assFormulaQuestionUnit $unit */
            $data[] = [
                'unit_id' => $unit->getId(),
                'unit' => $unit->getUnit(),
                'baseunit' => $unit->getBaseunitTitle(),
                'baseunit_id' => $unit->getBaseUnit(),
                'factor' => $unit->getFactor(),
                'sequence' => $unit->getSequence(),
            ];
        }
        $table->setData($data);

        $this->tpl->setContent($table->getHTML());
    }

    protected function showGlobalUnitCategories() : void
    {
        $categories = array_filter(
            $this->repository->getAllUnitCategories(),
            static function (assFormulaQuestionUnitCategory $category) : bool {
                return !$category->getQuestionFi() ? true : false;
            }
        );
        $data = [];
        foreach ($categories as $category) {
            /** @var assFormulaQuestionUnitCategory $category */
            $data[] = [
                'category_id' => $category->getId(),
                'category' => $category->getDisplayString()
            ];
        }

        $this->showUnitCategories($data);
    }

    protected function confirmDeleteCategory() : void
    {
        if (!$this->request->isset('category_id')) {
            $this->{$this->getUnitCategoryOverviewCommand()}();
            return;
        }

        $this->confirmDeleteCategories([$this->request->int('category_id')]);
    }

    /**
     * @param int[]|null $category_ids
     * @return void
     * @throws ilCtrlException
     */
    protected function confirmDeleteCategories(array $category_ids = null) : void
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $category_ids = $category_ids ?? $this->request->getUnitCategoryIds();
        if (count($category_ids) === 0) {
            $this->{$this->getUnitCategoryOverviewCommand()}();
            return;
        }

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, 'deleteCategories'));
        $confirmation->setConfirm($this->lng->txt('confirm'), 'deleteCategories');
        $confirmation->setCancel($this->lng->txt('cancel'), $this->getUnitCategoryOverviewCommand());

        $errors = [];
        $num_to_confirm = 0;
        foreach ($category_ids as $category_id) {
            try {
                $category = $this->repository->getUnitCategoryById($category_id);
            } catch (ilException $e) {
                continue;
            }

            if (!$this->repository->isCRUDAllowed($category_id)) {
                $errors[] = $category->getDisplayString() . ' - ' . $this->lng->txt('change_adm_categories_not_allowed');
                continue;
            }

            $possible_error = $this->repository->checkDeleteCategory($category_id);
            if (is_string($possible_error) && $possible_error !== '') {
                $errors[] = $category->getDisplayString() . ' - ' . $possible_error;
                continue;
            }

            $confirmation->addItem('category_ids[]', (string) $category->getId(), $category->getDisplayString());
            ++$num_to_confirm;
        }

        if ($errors) {
            $num_errors = count($errors);

            $error_message = array_map(static function (string $message) : string {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors === 1) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_cat_deletion_errors_f_s') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            } else {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_cat_deletion_errors_f') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            }
        }

        if ($num_to_confirm) {
            if ($num_to_confirm === 1) {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_categories_s'));
            } else {
                $confirmation->setHeaderText($this->lng->txt('un_sure_delete_categories'));
            }

            $this->tpl->setContent($confirmation->getHTML());
        } else {
            $this->{$this->getUnitCategoryOverviewCommand()}();
        }
    }

    protected function deleteCategories() : void
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $category_ids = $this->request->getUnitCategoryIds();
        if (count($category_ids) === 0) {
            $this->{$this->getUnitCategoryOverviewCommand()}();
            return;
        }

        $errors = [];
        $num_deleted = 0;
        foreach ($category_ids as $category_id) {
            try {
                $category = $this->repository->getUnitCategoryById($category_id);
            } catch (ilException $e) {
                continue;
            }

            if (!$this->repository->isCRUDAllowed($category_id)) {
                $errors[] = $category->getDisplayString() . ' - ' . $this->lng->txt('change_adm_categories_not_allowed');
                continue;
            }

            $possible_error = $this->repository->deleteCategory($category_id);
            if (is_string($possible_error) && $possible_error !== '') {
                $errors[] = $category->getDisplayString() . ' - ' . $possible_error;
                continue;
            }

            ++$num_deleted;
        }

        if ($errors) {
            $num_errors = count($errors);

            $error_message = array_map(static function (string $message) : string {
                return '<li>' . $message . '</li>';
            }, $errors);
            if ($num_errors === 1) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_cat_deletion_errors_p_s') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            } else {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('un_cat_deletion_errors_p') . '<ul>' . implode('', $error_message) . '<ul>'
                );
            }
        }

        if ($num_deleted) {
            if ($num_deleted === 1) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('un_deleted_categories_s'));
            } else {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('un_deleted_categories'));
            }
        }

        $this->{$this->getUnitCategoryOverviewCommand()}();
    }

    protected function initUnitCategoryForm(assFormulaQuestionUnitCategory $cat = null) : ilPropertyFormGUI
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

    protected function addCategory() : void
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
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));

                $this->{$this->getUnitCategoryOverviewCommand()}();
                return;
            } catch (ilException $e) {
                $this->unit_cat_form->getItemByPostVar('category_name')->setAlert($this->lng->txt($e->getMessage()));
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            }
        }

        $this->unit_cat_form->setValuesByPost();

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }

    protected function showUnitCategoryCreationForm() : void
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $this->initUnitCategoryForm();

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }

    protected function saveCategory() : void
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $category = $this->getCategoryById($this->request->int('category_id'));

        $this->initUnitCategoryForm($category);
        if ($this->unit_cat_form->checkInput()) {
            try {
                $category->setCategory($this->unit_cat_form->getInput('category_name'));
                $this->repository->saveCategory($category);
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'));

                $this->{$this->getUnitCategoryOverviewCommand()}();
                return;
            } catch (ilException $e) {
                $this->unit_cat_form->getItemByPostVar('category_name')->setAlert($this->lng->txt($e->getMessage()));
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            }
        }

        $this->unit_cat_form->setValuesByPost();

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }

    protected function showUnitCategoryModificationForm() : void
    {
        if (!$this->isCRUDContext()) {
            $this->{$this->getDefaultCommand()}();
            return;
        }

        $category = $this->getCategoryById($this->request->int('category_id'));

        $this->initUnitCategoryForm($category);
        $this->unit_cat_form->setValuesByArray([
            'category_name' => $category->getCategory()
        ]);

        $this->tpl->setContent($this->unit_cat_form->getHtml());
    }
}
