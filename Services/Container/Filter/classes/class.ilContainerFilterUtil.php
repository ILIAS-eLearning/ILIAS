<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Utilities for container filter
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterUtil
{
    /**
     * @var ilContainerFilterAdvMDAdapter
     */
    protected $adv_adapter;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilContainerFilterService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct(ilContainerFilterService $service, ilContainerFilterAdvMDAdapter $adv_adapter, ilLanguage $lng)
    {
        $this->adv_adapter = $adv_adapter;
        $this->lng = $lng;
        $this->service = $service;
    }

    /**
     * Get title of field
     * @param $record_id
     * @param $field_id
     * @return string
     * @throws ilException
     */
    public function getContainerFieldTitle($record_id, $field_id)
    {
        $lng = $this->lng;

        if ($record_id == 0) {
            return $lng->txt("cont_std_filter_title_" . $field_id);
        }

        $field = ilAdvancedMDFieldDefinition::getInstance($field_id);
        return $field->getTitle();
    }

    /**
     * Get title of record
     * @param $record_id
     * @return string
     * @throws ilException
     */
    public function getContainerRecordTitle($record_id)
    {
        $lng = $this->lng;

        if ($record_id == 0) {
            return $lng->txt("cont_std_record_title");
        }

        $rec = ilAdvancedMDRecord::_getInstanceByRecordId($record_id);
        return $rec->getTitle();
    }

    /**
     * Get filter element for reference id
     *
     * @param $ref_id
     * @param $action
     * @return \ILIAS\UI\Component\Input\Container\Filter\Standard
     * @throws ilException
     */
    public function getFilterForRefId($ref_id, $action, bool $admin = false) : \ILIAS\UI\Component\Input\Container\Filter\Standard
    {
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
            if ($field->getRecordSetId() == 0) {
                $title = $service->util()->getContainerFieldTitle(0, $field->getFieldId());
                $key = "std_" . $field->getFieldId();
                switch ($field->getFieldId()) {
                    case ilContainerFilterField::STD_FIELD_TITLE:
                        $fields[$key] = $ui->input()->field()->text($title);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_DESCRIPTION:
                        $fields[$key] = $ui->input()->field()->text($title);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_TITLE_DESCRIPTION:
                        $fields[$key] = $ui->input()->field()->text($title);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_KEYWORD:
                        $fields[$key] = $ui->input()->field()->text($title);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_AUTHOR:
                        $fields[$key] = $ui->input()->field()->text($title);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_COPYRIGHT:
                        $md_settings = ilMDSettings::_getInstance();
                        $entries = ilMDCopyrightSelectionEntry::_getEntries();
                        $use_selection = ($md_settings->isCopyrightSelectionActive() && count($entries));
                        $options = [];
                        if ($use_selection) {
                            foreach ($entries as $entry) {
                                $options[$entry->getEntryId()] = $entry->getTitle();
                            }
                        }
                        $fields[$key] = $ui->input()->field()->select($title, $options);
                        $fields_act[] = false;
                        break;
                    case ilContainerFilterField::STD_FIELD_TUTORIAL_SUPPORT:
                        $fields[$key] = $ui->input()->field()->text($title);
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
                        $options = [];
                        foreach ($service->advancedMetadata()->getOptions($field->getFieldId()) as $id => $op) {
                            /**
                             * Workaround: Adding 1 to the value for selects is necessary in
                             * R7 since KS selects are confused by the value 0. For R8 this
                             * is somehow not a problem.
                             */
                            $options[$id + 1] = $op;
                        }
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
