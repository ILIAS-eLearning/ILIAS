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
 * Class ilBiblFieldFilterPresentationGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblFieldFilterPresentationGUI
{
    use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
    protected \ilBiblFactoryFacadeInterface $facade;
    protected \ilBiblFieldFilterInterface $filter;


    /**
     * ilBiblFieldFilterPresentationGUI constructor.
     */
    public function __construct(\ilBiblFieldFilterInterface $filter, ilBiblFactoryFacadeInterface $facade)
    {
        $this->facade = $facade;
        $this->filter = $filter;
        $this->lng()->loadLanguageModule('bibl');
    }


    public function getFilterItem() : \ilTableFilterItem
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
                $options += $f->getPossibleValuesForFieldAndObject($field, $obj_id);
                $filter->setOptions($options);
                break;
            case ilBiblFieldFilterInterface::FILTER_TYPE_MULTI_SELECT_INPUT:
                $filter = new ilMultiSelectInputGUI($translated, $field->getIdentifier());
                $options = $f->getPossibleValuesForFieldAndObject($field, $obj_id);
                $filter->setOptions($options);
                break;
            default:
                throw new LogicException('no filter type used');
        }

        return $filter;
    }


    public function getFilter() : \ilBiblFieldFilterInterface
    {
        return $this->filter;
    }


    public function setFilter(\ilBiblFieldFilterInterface $filter) : void
    {
        $this->filter = $filter;
    }
}
