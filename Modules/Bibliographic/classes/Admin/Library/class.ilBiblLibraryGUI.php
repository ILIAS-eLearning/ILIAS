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
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    public const F_LIB_ID = 'lib_id';
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
            $b = ilLinkButton::getInstance();
            $b->setCaption(self::CMD_ADD);
            $b->setUrl($this->ctrl()->getLinkTarget($this, self::CMD_ADD));
            $b->setPrimary(true);

            $this->toolbar()->addButtonInstance($b);
        }

        $a_table = $this->initTable();
        $this->tpl()->setContent($a_table->getHTML());

        return true;
    }


    protected function initTable(): \ilBiblLibraryTableGUI
    {
        $table = new ilBiblLibraryTableGUI($this, $this->checkPermissionBoolAndReturn('write'));
        $settings = $this->facade->libraryFactory()->getAll();
        $result = array();
        foreach ($settings as $set) {
            $result[] = array(
                "id" => $set->getId(),
                "name" => $set->getName(),
                "url" => $set->getUrl(),
                "img" => $set->getImg(),
            );
        }
        $table->setData($result);

        return $table;
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
        $ilBibliographicSetting = $this->getInstanceFromRequest();
        $ilBibliographicSetting->delete();
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
        $ilBibliographicSetting = $this->getInstanceFromRequest();
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
        $this->ctrl()->saveParameter($this, self::F_LIB_ID);
        $ilBibliographicSetting = $this->getInstanceFromRequest();
        $form = new ilBiblLibraryFormGUI($ilBibliographicSetting);
        $this->tpl()->setContent($form->getHTML());
    }

    private function getInstanceFromRequest(): \ilBiblLibraryInterface
    {
        // check Query
        if ($this->wrapper->query()->has(self::F_LIB_ID)) {
            return $this->facade->libraryFactory()
                                ->findById(
                                    $this->wrapper->query()->retrieve(
                                        self::F_LIB_ID,
                                        $this->refinery->kindlyTo()->int()
                                    )
                                );
        }
        // check post
        if ($this->wrapper->post()->has(self::F_LIB_ID)) {
            return $this->facade->libraryFactory()
                                ->findById(
                                    $this->wrapper->post()->retrieve(
                                        self::F_LIB_ID,
                                        $this->refinery->kindlyTo()->int()
                                    )
                                );
        }
        throw new ilException('library not found');
    }
}
