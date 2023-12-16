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

namespace ILIAS\AdvancedMetaData\Services\SubObjectModes;

use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\AdvancedMetaData\Services\SubObjectIDInterface;
use ILIAS\StaticURL\Services as StaticURL;
use ILIAS\AdvancedMetaData\Services\Constants;

class DataTable implements DataTableInterface
{
    protected \ilObjUser $user;
    protected UIFactory $ui_factory;
    protected DataFactory $data_factory;
    protected StaticURL $static_url;

    /**
     * @var Column[]
     */
    protected array $columns = [];
    protected array $data = [];

    public function __construct(
        \ilObjUser $user,
        UIFactory $ui_factory,
        DataFactory $data_factory,
        StaticURL $static_url,
        string $type,
        int $ref_id,
        SubObjectIDInterface ...$sub_object_ids
    ) {
        $this->user = $user;
        $this->ui_factory = $ui_factory;
        $this->data_factory = $data_factory;
        $this->static_url = $static_url;
        $this->initColumnsAndData($type, $ref_id, ...$sub_object_ids);
    }

    /**
     * @return Column[];
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getData(SubObjectIDInterface $sub_object_id): array
    {
        return $this->data[$sub_object_id->subtype()][$sub_object_id->objID()][$sub_object_id->subID()] ?? [];
    }

    protected function initColumnsAndData(
        string $type,
        int $ref_id,
        SubObjectIDInterface ...$sub_object_ids
    ): void {
        $ids = [];
        foreach ($sub_object_ids as $sub_object_id) {
            $ids[$sub_object_id->subtype()][$sub_object_id->objID()][] = [
                'obj_id' => $sub_object_id->objID(),
                'sub_id' => $sub_object_id->subID()
            ];
        }

        foreach ($ids as $sub_type => $id) {
            $values = [];
            foreach ($id as $obj_id => $records) {
                $values = array_merge($values, \ilAdvancedMDValues::queryForRecords(
                    $ref_id,
                    $type,
                    $sub_type,
                    [$obj_id],
                    $sub_type,
                    $records,
                    'obj_id',
                    'sub_id'
                ));
            }

            foreach (\ilAdvancedMDRecord::_getSelectedRecordsByObject(
                $type,
                $ref_id,
                $sub_type,
                $ref_id !== 0
            ) as $record_obj) {
                $record_id = $record_obj->getRecordId();
                $translations = \ilAdvancedMDFieldTranslations::getInstanceByRecordId($record_id);
                $defs = \ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_id);
                foreach ($defs as $def) {
                    $key = Constants::ID_PREFIX . $def->getFieldId();
                    $this->columns[$key] = $this->initColumn($translations, $def);
                    foreach ($values as $value) {
                        $obj_id = $value['obj_id'];
                        $sub_id = $value['sub_id'];
                        $presentation = $value['md_' . $def->getFieldId() . '_presentation'] ?? null;
                        if (!$presentation) {
                            continue;
                        }
                        $this->data[$sub_type][$obj_id][$sub_id][$key] = $this->initData($def, $presentation);
                    }
                }
            }
        }
    }

    protected function initColumn(
        \ilAdvancedMDFieldTranslations $translations,
        \ilAdvancedMDFieldDefinition $def
    ): Column {
        $f = $this->ui_factory->table()->column();
        $adt_def = $def->getADTDefinition();
        $title = $translations->getTitleForLanguage($def->getFieldId(), $this->user->getLanguage());

        switch ($def->getType()) {
            case \ilAdvancedMDFieldDefinition::TYPE_INTEGER:
                $column = $f->number($title)
                            ->withUnit($adt_def->getSuffix());
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_FLOAT:
                $column = $f->number($title)
                            ->withDecimals($adt_def->getDecimals())
                            ->withUnit($adt_def->getSuffix());
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_DATE:
                $column = $f->date(
                    $title,
                    $this->user->getDateFormat()
                );
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_DATETIME:
                if ((int) $this->user->getTimeFormat() === \ilCalendarSettings::TIME_FORMAT_12) {
                    $format = $this->data_factory->dateFormat()->withTime12($this->user->getDateFormat());
                } else {
                    $format = $this->data_factory->dateFormat()->withTime24($this->user->getDateFormat());
                }
                $column = $f->date($title, $format);
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_INTERNAL_LINK:
            case \ilAdvancedMDFieldDefinition::TYPE_EXTERNAL_LINK:
                $column = $f->link($title);
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_SELECT:
            case \ilAdvancedMDFieldDefinition::TYPE_TEXT:
            case \ilAdvancedMDFieldDefinition::TYPE_LOCATION:
            case \ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI:
            case \ilAdvancedMDFieldDefinition::TYPE_ADDRESS:
            default:
                $column = $f->text($title);
        }

        return $column;
    }

    protected function initData(
        \ilAdvancedMDFieldDefinition $def,
        \ilADTPresentationBridge $presentation
    ): mixed {
        $adt = $presentation->getADT();

        switch ($def->getType()) {
            case \ilAdvancedMDFieldDefinition::TYPE_INTEGER:
            case \ilAdvancedMDFieldDefinition::TYPE_FLOAT:
                $val = $adt->getNumber();
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_DATE:
            case \ilAdvancedMDFieldDefinition::TYPE_DATETIME:
                $timestamp = $adt->getDate()->getUnixTime();
                $val = (new \DateTimeImmutable())->setTimestamp($timestamp);
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_INTERNAL_LINK:
                $target_ref_id = $adt->getTargetRefId();
                $target_obj_id = \ilObject::_lookupObjId($target_ref_id);
                $target_title = \ilObject::_lookupTitle($target_obj_id);
                $target_type = \ilObject::_lookupType($target_obj_id);
                $uri = $this->static_url->builder()->build(
                    $target_type,
                    $this->data_factory->refId($target_ref_id)
                );
                $val = $this->ui_factory->link()->standard($target_title, (string) $uri);
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_EXTERNAL_LINK:
                $title = $adt->getTitle();
                $url = $adt->getUrl();
                $val = $this->ui_factory->link()->standard($title, $url);
                break;

            case \ilAdvancedMDFieldDefinition::TYPE_SELECT:
            case \ilAdvancedMDFieldDefinition::TYPE_TEXT:
            case \ilAdvancedMDFieldDefinition::TYPE_LOCATION:
            case \ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI:
            case \ilAdvancedMDFieldDefinition::TYPE_ADDRESS:
            default:
                $val = $presentation->getList();
        }

        return $val;
    }
}
