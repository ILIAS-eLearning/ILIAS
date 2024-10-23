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

use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

/**
 * Utilities for container filter
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerFilterUtil
{
    protected ilContainerFilterAdvMDAdapter $adv_adapter;
    protected ilLanguage $lng;
    protected ilContainerFilterService $service;
    protected LOMServices $lom_services;

    public function __construct(
        ilContainerFilterService $service,
        ilContainerFilterAdvMDAdapter $adv_adapter,
        ilLanguage $lng,
        LOMServices $lom_services
    ) {
        $this->adv_adapter = $adv_adapter;
        $this->lng = $lng;
        $this->service = $service;
        $this->lom_services = $lom_services;
    }

    /**
     * Get title of field
     * @throws ilException
     */
    public function getContainerFieldTitle(
        int $record_id,
        int $field_id
    ): string {
        $lng = $this->lng;

        if ($record_id === 0) {
            return $lng->txt("cont_std_filter_title_" . $field_id);
        }

        $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
        return $field->getTitle();
    }

    /**
     * Get title of record
     * @throws ilException
     */
    public function getContainerRecordTitle(int $record_id): string
    {
        $lng = $this->lng;

        if ($record_id === 0) {
            return $lng->txt("cont_std_record_title");
        }

        $rec = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
        return $rec->getTitle();
    }

    /**
     * Get filter element for reference id
     * @throws ilException
     */
    public function getFilterForRefId(
        int $ref_id,
        string $action,
        bool $admin = false
    ): Standard {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $ui = $DIC->ui()->factory();
        $service = $this->service;
        $lng = $this->lng;
        /** @var ilObjectDefinition $obj_definition */
        $obj_definition = $DIC["objDefinition"];

        $set = $service->data()->getFilterSetForRefId($ref_id);

        $fields = $fields_act = [];

        // if admin mode
        if ($admin) {
            // always add online/offline filter
            $options = [
                1 => $this->lng->txt("online"),
                2 => $this->lng->txt("offline")
            ];
            $fields["std_" . ilContainerFilterField::STD_FIELD_ONLINE] =
                $ui->input()->field()->select(
                    $service->util()->getContainerFieldTitle(0, ilContainerFilterField::STD_FIELD_ONLINE),
                    $options
                );
            $fields_act[] = false;
        }

        foreach ($set->getFields() as $field) {
            // standard fields
            if ($field->getRecordSetId() === 0) {
                $title = $service->util()->getContainerFieldTitle(0, $field->getFieldId());
                $key = "std_" . $field->getFieldId();
                switch ($field->getFieldId()) {
                    case ilContainerFilterField::STD_FIELD_DESCRIPTION:
                    case ilContainerFilterField::STD_FIELD_TITLE_DESCRIPTION:
                    case ilContainerFilterField::STD_FIELD_KEYWORD:
                    case ilContainerFilterField::STD_FIELD_AUTHOR:
                    case ilContainerFilterField::STD_FIELD_TUTORIAL_SUPPORT:
                    case ilContainerFilterField::STD_FIELD_TITLE:
                        $fields[$key] = $ui->input()->field()->text($title);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_COPYRIGHT:
                        $options = [];
                        foreach ($this->lom_services->copyrightHelper()->getAllCopyrightPresets() as $copyright) {
                            $options[$copyright->identifier()] = $copyright->title();
                        }
                        $fields[$key] = $ui->input()->field()->select($title, $options);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_OBJECT_TYPE:
                        $options = [];
                        foreach ($obj_definition->getCreatableSubObjects("cat") as $k => $t) {
                            $options[$k] = $lng->txt("obj_$k");
                        }
                        $fields[$key] = $ui->input()->field()->select($title, $options);
                        $fields_act[] = false;
                        break;
                }
            } else {
                // advanced metadata fields
                $title = $service->advancedMetadata()->getTitle($field->getRecordSetId(), $field->getFieldId());
                switch ($service->advancedMetadata()->getAdvType($field->getFieldId())) {
                    case ilAdvancedMDFieldDefinition::TYPE_SELECT:
                    case ilAdvancedMDFieldDefinition::TYPE_SELECT_MULTI:
                        $options = $service->advancedMetadata()->getOptions($field->getFieldId());
                        $fields["adv_" . $field->getFieldId()] =
                            $ui->input()->field()->select($title, $options);
                        $fields_act[] = false;
                        break;

                    case ilAdvancedMDFieldDefinition::TYPE_TEXT:
                    case ilAdvancedMDFieldDefinition::TYPE_INTEGER:
                        $fields["adv_" . $field->getFieldId()] =
                            $ui->input()->field()->text($title);
                        $fields_act[] = false;
                        break;
                }
            }
        }
        if (count($fields_act) > 0) {
            $fields_act[0] = true;
        }
        $filter = $DIC->uiService()->filter()->standard(
            "filter_ID",
            $action,
            $fields,
            $fields_act,
            false,
            false
        );
        return $filter;
    }
}
