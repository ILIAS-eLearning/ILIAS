<?php

/**
 * Class ilBiblFieldFilterPresentationGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterPresentationGUI
{
    protected \ilBiblFactoryFacadeInterface $facade;
    protected \ilBiblFieldFilterInterface $filter;


    /**
     * ilBiblFieldFilterPresentationGUI constructor.
     *
     * @param \ilBiblFieldFilterInterface   $filter
     * @param \ilBiblFactoryFacadeInterface $facade
     */
    public function __construct(\ilBiblFieldFilterInterface $filter, ilBiblFactoryFacadeInterface $facade)
    {
        $this->facade = $facade;
        $this->filter = $filter;
        $this->lng()->loadLanguageModule('bibl');
    }


    public function getFilterItem(): \ilTableFilterItem
    {
        $field = $this->facade->fieldFactory()->findById($this->getFilter()->getFieldId());
        $translated = $this->facade->translationFactory()->translate($field);

        $ilBiblFieldFilter = $this->getFilter();

        $obj_id = $this->facade->iliasObjId();
        $f = $this->facade->attributeFactory();

        switch ($ilBiblFieldFilter->getFilterType()) {
            case ilBiblFieldFilterInterface::FILTER_TYPE_TEXT_INPUT:
                $filter = new ilTextInputGUI($translated, $field->getIdentifier());
                break;
            case ilBiblFieldFilterInterface::FILTER_TYPE_SELECT_INPUT:
                $filter = new ilSelectInputGUI($translated, $field->getIdentifier());
                $options[null] = $this->lng()->txt("please_select");
                $options = $options + $f->getPossibleValuesForFieldAndObject($field, $obj_id);
                $filter->setOptions($options);
                break;
            case ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT:
                $filter = new ilMultiSelectInputGUI($translated, $field->getIdentifier());
                $options = $f->getPossibleValuesForFieldAndObject($field, $obj_id);
                $filter->setOptions($options);
                break;
            default:
                throw new LogicException('no filter type used');
                break;
        }

        return $filter;
    }


    public function getFilter(): \ilBiblFieldFilterInterface
    {
        return $this->filter;
    }


    public function setFilter(\ilBiblFieldFilterInterface $filter): void
    {
        $this->filter = $filter;
    }
}
