<?php

/**
 * Class ilBiblTranslationTableGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTranslationTableGUI extends ilTable2GUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    /**
     * @var \ilBiblFieldInterface
     */
    protected $field;
    /**
     * @var \ilBiblTranslationFactoryInterface
     */
    protected $translation_facory;


    /**
     * @inheritDoc
     */
    public function __construct($a_parent_obj, ilBiblFieldInterface $bibl_field, ilBiblTranslationFactoryInterface $translation_factory)
    {
        $this->translation_facory = $translation_factory;
        $this->field = $bibl_field;
        $table_id = 'bibl_trans_field_' . $bibl_field->getId();
        $this->setId($table_id);
        $this->setPrefix($table_id);
        $this->setFormName($table_id);
        $this->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());
        $this->setRowTemplate("tpl.bibl_admin_translation_row.html", "Modules/Bibliographic");
        parent::__construct($a_parent_obj);
        $this->setFormAction($this->ctrl()->getFormAction($a_parent_obj));
        $this->setExternalSorting(true);
        $this->setDefaultOrderField("id");
        $this->setDefaultOrderDirection("asc");
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->initColumns();

        $this->addCommandButton(ilBiblTranslationGUI::CMD_SAVE_TRANSLATIONS, $this->lng()
                                                                                  ->txt("save"));
        $this->addMultiCommand(ilBiblTranslationGUI::CMD_DELETE_TRANSLATIONS, $this->lng()
                                                                                   ->txt("delete"));

        $this->parseData();
    }


    protected function initColumns()
    {
        $this->addColumn($this->lng()->txt('bibl_translation_select'), '', '15px', true);
        $this->addColumn($this->lng()->txt('bibl_translation_lang'));
        $this->addColumn($this->lng()->txt('bibl_translation_trans'));
        $this->addColumn($this->lng()->txt('bibl_translation_desc'));
    }


    protected function parseData()
    {
        $data = $this->translation_facory->getAllTranslationsForFieldAsArray($this->field);
        $this->setData($data);
    }


    /**
     * @inheritDoc
     */
    protected function fillRow($a_set)
    {
        $translation = $this->translation_facory->findById($a_set['id']);
        $this->tpl->setVariable('ID', $translation->getId());
        $this->tpl->setVariable('LANGUAGE', $translation->getLanguageKey());
        $this->tpl->setVariable('TEXT', $translation->getTranslation());
        $this->tpl->setVariable('DESCRIPTION', $translation->getDescription());
    }
}
