<?php
/**
 * Class ilBiblFieldFilterFormGUI
 *
 * @author Benjamin Seglias   <bs@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

class ilBiblFieldFilterFormGUI extends ilPropertyFormGUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    const F_FIELD_ID = "field_id";
    const F_FILTER_TYPE = "filter_type";
    /**
     * @var \ilBiblFactoryFacade
     */
    protected $facade;
    /**
     * @var  \ilBiblFieldFilterInterface
     */
    protected $filter;
    /**
     * @var ilBiblFieldFilterGUI
     */
    protected $parent_gui;


    /**
     * ilBiblFieldFilterFormGUI constructor.
     *
     * @param \ilBiblFieldFilterGUI       $parent_gui
     * @param \ilBiblFieldFilterInterface $field_filter
     * @param \ilBiblFactoryFacade        $facade
     */
    public function __construct(ilBiblFieldFilterGUI $parent_gui, ilBiblFieldFilterInterface $field_filter, ilBiblFactoryFacade $facade)
    {
        $this->facade = $facade;
        $this->filter = $field_filter;
        $this->parent_gui = $parent_gui;

        $this->lng()->loadLanguageModule('bibl');
        $this->ctrl()->saveParameterByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::FILTER_ID);

        parent::__construct();
        $this->initForm();
    }


    public function initForm()
    {
        $this->setTarget('_top');

        $available_fields_for_object = $this->facade->fieldFactory()->getAvailableFieldsForObjId($this->facade->iliasObjId());

        $edited_filter = $this->facade->filterFactory()->findById(
            $this->http()->request()->getQueryParams()[ilBiblFieldFilterGUI::FILTER_ID]
        );

        //show only the fields as options which don't have already a filter
        $options = [];
        foreach ($available_fields_for_object as $available_field) {
            if (empty($this->facade->filterFactory()->findByFieldId($available_field->getId()))
                || (!empty($edited_filter)
                    && $edited_filter->getFieldId() == $available_field->getId())
            ) {
                if (!empty($edited_filter)
                    && $edited_filter->getFieldId() == $available_field->getId()
                ) {
                    array_unshift($options, $available_field);
                    continue;
                }
                $options[] = $available_field;
            }
        }

        $select_options = [];
        foreach ($options as $ilBiblField) {
            $select_options[$ilBiblField->getId()] = $this->facade->translationFactory()->translate($ilBiblField);
        }

        asort($select_options);
        $si = new ilSelectInputGUI($this->lng()->txt("field"), self::F_FIELD_ID);
        $si->setInfo($this->lng()->txt("filter_field_info"));
        $si->setOptions($select_options);
        $si->setRequired(true);
        $this->addItem($si);

        $options = [ilBiblFieldFilterInterface::FILTER_TYPE_TEXT_INPUT    => $this->lng()->txt(
            "filter_type_" . ilBiblFieldFilterInterface::FILTER_TYPE_TEXT_INPUT
        ), ilBiblFieldFilterInterface::FILTER_TYPE_SELECT_INPUT       => $this->lng()->txt(
                "filter_type_" . ilBiblFieldFilterInterface::FILTER_TYPE_SELECT_INPUT
            ), ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT => $this->lng()->txt(
                "filter_type_" . ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT
            ),];
        $si = new ilSelectInputGUI($this->lng()->txt("filter_type"), self::F_FILTER_TYPE);
        $si->setInfo($this->lng()->txt("filter_type_info"));
        $si->setOptions($options);
        $si->setRequired(true);
        $this->addItem($si);

        $this->setTitle($this->lng()->txt('filter_form_title'));

        $this->initButtons();

        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
    }


    public function fillForm()
    {
        $array = array(self::F_FIELD_ID => $this->filter->getFieldId(), self::F_FILTER_TYPE => $this->filter->getFilterType(),);
        $this->setValuesByArray($array);
    }


    protected function fillObject()
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->filter->setFieldId($this->getInput(self::F_FIELD_ID));
        $this->filter->setFilterType($this->getInput(self::F_FILTER_TYPE));

        if ($this->filter->getId()) {
            $this->filter->update();
        } else {
            $this->filter->create();
        }

        return true;
    }


    /**
     * @return bool|string
     */
    public function saveObject()
    {
        if (!$this->fillObject()) {
            return false;
        }

        return true;
    }


    protected function initButtons()
    {
        if ($this->filter->getId()) {
            $this->addCommandButton(ilBiblFieldFilterGUI::CMD_UPDATE, $this->lng()->txt('save'));
            $this->addCommandButton(ilBiblFieldFilterGUI::CMD_CANCEL, $this->lng()->txt("cancel"));
        } else {
            $this->addCommandButton(ilBiblFieldFilterGUI::CMD_CREATE, $this->lng()->txt('create'));
            $this->addCommandButton(ilBiblFieldFilterGUI::CMD_CANCEL, $this->lng()->txt("cancel"));
        }
    }
}
