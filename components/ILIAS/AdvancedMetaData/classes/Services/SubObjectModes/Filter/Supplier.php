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

declare(strict_types=1);

namespace ILIAS\AdvancedMetaData\Services\SubObjectModes\Filter;

use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\AdvancedMetaData\Services\SubObjectIDInterface;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\AdvancedMetaData\Services\Constants;

class Supplier implements SupplierInterface
{
    protected \ilObjUser $user;
    protected \ilLanguage $lng;
    protected UIFactory $ui_factory;
    protected DataFactory $data_factory;
    protected StaticURL $static_url;

    protected string $type;
    protected int $ref_id;

    /**
     * @var FilterInput[]
     */
    protected array $filter_inputs = [];

    public function __construct(
        \ilObjUser $user,
        \ilLanguage $lng,
        UIFactory $ui_factory,
        DataFactory $data_factory,
        StaticURL $static_url,
        string $type,
        int $ref_id,
        string ...$sub_types
    ) {
        $this->user = $user;
        $this->lng = $lng;
        $this->ui_factory = $ui_factory;
        $this->data_factory = $data_factory;
        $this->static_url = $static_url;
        $this->type = $type;
        $this->ref_id = $ref_id;
        $this->initFilterInputs(...$sub_types);
    }

    /**
     * @return FilterInput[];
     */
    public function getFilterInputs(): array
    {
        return $this->filter_inputs;
    }

    protected function initFilterInputs(
        string ...$sub_types
    ): void {
        foreach ($sub_types as $sub_type) {
            foreach ($this->getRecordIds($sub_type) as $record_id) {
                $translations = \ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);
                $defs = \ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);
                foreach ($defs as $def) {
                    if (!$def->isFilterSupported()) {
                        continue;
                    }
                    if ($filter_input = $this->initFilterInput($translations, $def)) {
                        $key = Constants::ID_PREFIX . $def->getFieldId();
                        $this->filter_inputs[$key] = $filter_input;
                    }
                }
            }
        }
    }

    protected function initFilterInput(
        \ilAdvancedMDFieldTranslations $translations,
        \ilAdvancedMDFieldDefinition $def
    ): ?FilterInput {
        $input_factory = $this->ui_factory->input()->field();
        $adt_def = $def->getADTDefinition();
        $title = $translations->getTitleForLanguage($def->getFieldId(), $this->user->getLanguage());

        switch ($def->getType()) {
            case \ilAdvancedMDFieldDefinition::TYPE_TEXT:
            case \ilAdvancedMDFieldDefinition::TYPE_INTERNAL_LINK:
            case \ilAdvancedMDFieldDefinition::TYPE_EXTERNAL_LINK:
                $input = $input_factory->text($title);
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_INTEGER:
            case \ilAdvancedMDFieldDefinition::TYPE_FLOAT:
                $input = $input_factory->numeric($title);     // isFilterSupported() currently returns false
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_DATE:
                $input = $input_factory->duration($title)
                                       ->withFormat($this->user->getDateFormat());
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_DATETIME:
                $input = $input_factory->duration($title)
                                       ->withUseTime(true)
                                       ->withFormat($this->user->getDateFormat());
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_SELECT:
            case \ilAdvancedMDFieldDefinition::TYPE_ADDRESS:
                $def = $def->getADT()->getCopyOfDefinition();
                $options = $def->getOptions();
                //asort($options);
                //$this->lng->loadLanguageModule("search");
                //$options = ["" => $this->lng->txt("search_any")] + $options;
                $input = $input_factory->select($title, $options);
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI:
                $def = $def->getADT()->getCopyOfDefinition();
                $options = $def->getOptions();
                //asort($options);
                $input = $input_factory->multiSelect($title, $options);
                break;
            default:
                return null;
        }

        return $input;
    }

    /**
     * @return int[]
     */
    protected function getRecordIds(string $sub_type): array
    {
        $ids = [];
        foreach (\ilAdvancedMDRecord::_getSelectedRecordsByObject(
            $this->type,
            $this->ref_id,
            $sub_type,
            $this->ref_id !== 0
        ) as $record_obj) {
            $ids[] = $record_obj->getRecordId();
        }
        return $ids;
    }
}
