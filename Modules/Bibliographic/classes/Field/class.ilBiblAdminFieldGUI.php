<?php

/**
 * Class ilBiblAdminFieldGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBiblAdminFieldGUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    const CMD_INIT_OVERVIEW_MODELS = 'initOverviewModels';
    const CMD_INIT_DEFAULT_FIELDS_AND_SORTING = 'initDefaultFieldsAndSorting';
    const SUBTAB_RIS = 'subtab_ris';
    const SUBTAB_BIBTEX = 'subtab_bibtex';
    const FIELD_IDENTIFIER = 'field_id';
    const DATA_TYPE = 'data_type';
    const CMD_STANDARD = 'index';
    const CMD_CANCEL = 'cancel';
    const CMD_EDIT = 'edit';
    const CMD_UPDATE = 'update';
    const CMD_APPLY_FILTER = 'applyFilter';
    const CMD_RESET_FILTER = 'resetFilter';
    const CMD_SAVE = 'save';
    /**
     * @var \ilBiblAdminFactoryFacadeInterface
     */
    protected $facade;


    /**
     * ilBiblAdminFieldGUI constructor.
     *
     * @param \ilBiblAdminFactoryFacadeInterface $facade
     */
    public function __construct(ilBiblAdminFactoryFacadeInterface $facade)
    {
        $this->facade = $facade;
    }


    public function executeCommand()
    {
        $nextClass = $this->ctrl()->getNextClass();
        $this->tabs()->activateTab(ilObjBibliographicAdminGUI::TAB_FIELDS);
        switch ($nextClass) {
        case strtolower(ilBiblTranslationGUI::class):
            $this->tabs()->clearTargets();
            $target = $this->ctrl()->getLinkTarget($this);
            $this->tabs()->setBackTarget($this->lng()->txt('back'), $target);

            $field_id = $this->http()->request()->getQueryParams()[self::FIELD_IDENTIFIER];
            if (!$field_id) {
                throw new ilException("Field not found");
            }
            $this->ctrl()->saveParameter($this, self::FIELD_IDENTIFIER);
            $field = $this->facade->fieldFactory()->findById($field_id);

            $gui = new ilBiblTranslationGUI($this->facade, $field);
            $this->ctrl()->forwardCommand($gui);
            break;

        default:
            $this->performCommand();
        }
    }


    private function initDefaultFieldsAndSorting()
    {
        $this->checkPermissionAndFail('write');
        $tf = new ilBiblTypeFactory();
        $bib_default_sorting = [
            'title', 'author',
        ];
        $bib = $tf->getInstanceForType(ilBiblTypeFactory::DATA_TYPE_BIBTEX);
        $ff_bib = new ilBiblFieldFactory($bib);
        foreach ($bib->getStandardFieldIdentifiers() as $i => $identifier) {
            $field = $ff_bib->findOrCreateFieldByTypeAndIdentifier($bib->getId(), $identifier);
            $field->setPosition($i + 1);
            $field->store();
            $array_search = array_search($identifier, $bib_default_sorting);
            if ($array_search !== false) {
                $field->setPosition((int) $array_search + 1);
                $ff_bib->forcePosition($field);
            }
        }
        $ris_default_sorting = [
            'T1', 'AU',
        ];
        $ris = $tf->getInstanceForType(ilBiblTypeFactory::DATA_TYPE_RIS);
        $ff_ris = new ilBiblFieldFactory($ris);
        foreach ($ris->getStandardFieldIdentifiers() as $i => $identifier) {
            $field = $ff_ris->findOrCreateFieldByTypeAndIdentifier($ris->getId(), $identifier);
            $field->setPosition($i + 1);
            $field->store();
            $array_search = array_search($identifier, $ris_default_sorting);
            if ($array_search !== false) {
                $field->setPosition((int) $array_search + 1);
                $ff_bib->forcePosition($field);
            }
        }
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }


    private function initOverviewModels()
    {
        $this->checkPermissionAndFail('write');
        global $DIC;
        $ilDB = $DIC->database();

        // TODO fill filetype_id with the correct values
        if ($ilDB->tableExists('il_bibl_overview_model')) {
            $type = function ($filetype_string) {
                if (strtolower($filetype_string) == "bib"
                    || strtolower($filetype_string) == "bibtex"
                ) {
                    return 2;
                }

                return 1;
            };

            if (!$ilDB->tableColumnExists('il_bibl_overview_model', 'file_type_id')) {
                $ilDB->addTableColumn('il_bibl_overview_model', 'file_type_id', array("type" => "integer", 'length' => 4));
            }

            $res = $ilDB->query("SELECT * FROM il_bibl_overview_model");
            while ($d = $ilDB->fetchObject($res)) {
                $type_id = (int) $type($d->filetype);
                $ilDB->update(
                    "il_bibl_overview_model",
                    [
                    "file_type_id" => ["integer", $type_id],
                ],
                    ["ovm_id" => ["integer", $d->ovm_id]]
                );
            }
            //			$ilDB->dropTableColumn('il_bibl_overview_model', 'filetype');
        }

        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }


    protected function performCommand()
    {
        $cmd = $this->ctrl()->getCmd(self::CMD_STANDARD);
        switch ($cmd) {
        case self::CMD_STANDARD:
        case self::CMD_EDIT:
        case self::CMD_UPDATE:
        case self::CMD_SAVE:
        case self::CMD_APPLY_FILTER:
        case self::CMD_RESET_FILTER:
        case self::CMD_INIT_OVERVIEW_MODELS:
        case self::CMD_INIT_DEFAULT_FIELDS_AND_SORTING:
            if ($this->access()->checkAccess('write', "", $this->facade->iliasRefId())) {
                $this->{$cmd}();
                break;
            } else {
                ilUtil::sendFailure($this->lng()->txt("no_permission"), true);
                break;
            }
        }
    }


    protected function index()
    {
        $this->setSubTabs();
        // Buttons for restoring emthods
        /*$default_sorting = ilLinkButton::getInstance();
        $default_sorting->setCaption('init_default_fields');
        $default_sorting->setUrl($this->ctrl()->getLinkTarget($this, self::CMD_INIT_DEFAULT_FIELDS_AND_SORTING));
        $this->toolbar()->addButtonInstance($default_sorting);*/

        // Buttons for restoring emthods
        /*$overview_models = ilLinkButton::getInstance();
        $overview_models->setCaption('init_overview_models');
        $overview_models->setUrl($this->ctrl()->getLinkTarget($this, self::CMD_INIT_OVERVIEW_MODELS));
        $this->toolbar()->addButtonInstance($overview_models);*/

        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $this->tpl()->setContent($ilBiblAdminFieldTableGUI->getHTML());
    }


    protected function setSubTabs()
    {
        $this->tabs()->addSubTab(
            self::SUBTAB_RIS,
            $this->lng()->txt('ris'),
            $this->ctrl()->getLinkTargetByClass(
                array(
                ilObjBibliographicAdminGUI::class, ilBiblAdminRisFieldGUI::class,
            ),
                ilBiblAdminRisFieldGUI::CMD_STANDARD
            )
        );
        $this->tabs()->activateSubTab(self::SUBTAB_RIS);

        $this->tabs()->addSubTab(
            self::SUBTAB_BIBTEX,
            $this->lng()->txt('bibtex'),
            $this->ctrl()->getLinkTargetByClass(
                array(
                ilObjBibliographicAdminGUI::class, ilBiblAdminBibtexFieldGUI::class,
            ),
                ilBiblAdminBibtexFieldGUI::CMD_STANDARD
            )
        );
        switch ($this->facade->type()->getId()) {
        case ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX:
            $this->tabs()->activateSubTab(self::SUBTAB_BIBTEX);
            break;
        case ilBiblTypeFactoryInterface::DATA_TYPE_RIS:
            $this->tabs()->activateSubTab(self::SUBTAB_RIS);
            break;
        }
    }


    protected function save()
    {
        foreach ($_POST['position'] as $set) {
            $field_id = (int) key($set);
            $position = (int) current($set);

            $ilBiblField = $this->facade->fieldFactory()->findById((int) $field_id);
            $ilBiblField->setPosition((int) $position);
            $ilBiblField->store();
        }

        ilUtil::sendSuccess($this->lng()->txt("changes_successfully_saved"));
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }


    protected function applyFilter()
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->writeFilterToSession();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }


    protected function resetFilter()
    {
        $ilBiblAdminFieldTableGUI = new ilBiblAdminFieldTableGUI($this, $this->facade);
        $ilBiblAdminFieldTableGUI->resetFilter();
        $ilBiblAdminFieldTableGUI->resetOffset();
        $this->ctrl()->redirect($this, self::CMD_STANDARD);
    }
}
