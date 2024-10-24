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
 * Class ilBiblLibraryGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblLibraryGUI
{
    use \ILIAS\components\OrgUnit\ARHelper\DIC;
    public const F_LIB_ID = 'lib_id';
    public const F_LIB_IDS = 'lib_ids';
    public const CMD_DELETE = 'delete';
    public const CMD_EDIT = 'edit';
    public const CMD_INDEX = 'index';
    public const CMD_ADD = 'add';
    protected \ilBiblAdminLibraryFacadeInterface $facade;
    private \ilGlobalTemplateInterface $main_tpl;
    protected \ILIAS\Refinery\Factory $refinery;
    protected \ILIAS\HTTP\Wrapper\WrapperFactory $wrapper;

    /**
     * ilBiblLibraryGUI constructor.
     */
    public function __construct(ilBiblAdminLibraryFacadeInterface $facade)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->facade = $facade;
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();
    }


    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand(): void
    {
        switch ($this->ctrl()->getNextClass()) {
            case null:
                $cmd = $this->ctrl()->getCmd(self::CMD_INDEX);
                $this->{$cmd}();
                break;
        }
    }


    /**
     * @global $ilToolbar ilToolbarGUI;
     *
     */
    public function index(): bool
    {
        if ($this->checkPermissionBoolAndReturn('write')) {
            $btn_add = $this->ui()->factory()->button()->primary(
                $this->lng()->txt(self::CMD_ADD),
                $this->ctrl()->getLinkTarget($this, self::CMD_ADD)
            );
            $this->toolbar()->addComponent($btn_add);

        }

        $table_gui = new ilBiblLibraryTableGUI($this->facade);
        $this->tpl()->setContent($table_gui->getRenderedTable());

        return true;
    }


    /**
     * add library
     */
    public function add(): void
    {
        $this->checkPermissionAndFail('write');
        $form = new ilBiblLibraryFormGUI($this->facade->libraryFactory()->getEmptyInstance());
        $this->tpl()->setContent($form->getHTML());
    }


    /**
     * delete library
     */
    public function delete(): void
    {
        $this->checkPermissionAndFail('write');
        $ilBibliographicSettings = $this->getInstancesFromRequest();
        foreach ($ilBibliographicSettings as $ilBibliographicSetting) {
            $ilBibliographicSetting->delete();
        }
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }


    /**
     * cancel
     */
    public function cancel(): void
    {
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }


    /**
     * save changes in library
     */
    public function update(): void
    {
        $this->checkPermissionAndFail('write');
        $ilBibliographicSetting = $this->getInstancesFromRequest()[0];
        $form = new ilBiblLibraryFormGUI($ilBibliographicSetting);
        $form->setValuesByPost();
        if ($form->saveObject()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng()->txt("settings_saved"), true);
            $this->ctrl()->redirect($this, self::CMD_INDEX);
        }
        $this->tpl()->setContent($form->getHTML());
    }


    /**
     * create library
     */
    public function create(): void
    {
        $this->checkPermissionAndFail('write');
        $form = new ilBiblLibraryFormGUI($this->facade->libraryFactory()->getEmptyInstance());
        $form->setValuesByPost();
        if ($form->saveObject()) {
            $this->main_tpl->setOnScreenMessage('success', $this->lng()->txt("settings_saved"), true);
            $this->ctrl()->redirect($this, self::CMD_INDEX);
        }
        $this->tpl()->setContent($form->getHTML());
    }


    /**
     * edit library
     */
    public function edit(): void
    {
        $this->checkPermissionAndFail('write');
        $ilBibliographicSetting = $this->getInstancesFromRequest()[0];
        $form = new ilBiblLibraryFormGUI($ilBibliographicSetting);
        $this->tpl()->setContent($form->getHTML());
    }

    /**
     * return ilBiblLibraryInterface[]
     */
    private function getInstancesFromRequest(): array
    {
        $lib_ids = null;
        $to_int = $this->refinery->kindlyTo()->int();
        $to_int_array = $this->refinery->kindlyTo()->listOf($to_int);
        if ($this->wrapper->query()->has(self::F_LIB_IDS)) {
            $lib_ids = $this->wrapper->query()->retrieve(self::F_LIB_IDS, $to_int_array);
        } elseif ($this->wrapper->query()->has(self::F_LIB_ID)) {
            $lib_ids[] = $this->wrapper->query()->retrieve(self::F_LIB_ID, $to_int);
        } elseif ($this->wrapper->post()->has(self::F_LIB_IDS)) {
            $lib_ids = $this->wrapper->post()->retrieve(self::F_LIB_IDS, $to_int_array);
        } elseif ($this->wrapper->post()->has(self::F_LIB_ID)) {
            $lib_ids[] = $this->wrapper->post()->retrieve(self::F_LIB_ID, $to_int);
        }

        if ($lib_ids === null) {
            throw new ilException('library not found');
        }

        $instances = [];
        foreach ($lib_ids as $lib_id) {
            $instances[] = $this->facade->libraryFactory()->findById($lib_id);
        }
        return $instances;
    }
}
