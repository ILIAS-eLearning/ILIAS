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
 * Container filter service factory.
 *
 * This is an Services/Container internal subservice currently not accessible via DIC API.
 * Do not use this outside of Services/Container.
 *
 * Main entry point.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilContainerFilterService
{
    protected ilLanguage $lng;
    protected ?ilContainerFilterFieldData $field_data;
    protected ilContainerFilterAdvMDAdapter $adv_adapter;

    public function __construct(
        ilLanguage $lng = null,
        ilContainerFilterAdvMDAdapter $adv_adapter = null,
        ilContainerFilterFieldData $container_field_data = null
    ) {
        global $DIC;

        $this->lng = (is_null($lng))
            ? $DIC->language()
            : $lng;

        $this->adv_adapter = (is_null($adv_adapter))
            ? new ilContainerFilterAdvMDAdapter()
            : $adv_adapter;

        $this->field_data = (is_null($container_field_data))
            ? new ilContainerFilterFieldData()
            : $container_field_data;
    }

    public function util() : ilContainerFilterUtil
    {
        return new ilContainerFilterUtil($this, $this->adv_adapter, $this->lng);
    }

    public function advancedMetadata() : ilContainerFilterAdvMDAdapter
    {
        return $this->adv_adapter;
    }

    public function data() : ilContainerFilterFieldData
    {
        return $this->field_data;
    }

    public function field(int $record_set_id, int $field_id) : ilContainerFilterField
    {
        return new ilContainerFilterField($record_set_id, $field_id);
    }

    /**
     * Filter Set
     */
    public function set(array $fields) : ilContainerFilterSet
    {
        return new ilContainerFilterSet($fields);
    }

    /**
     * Get standard set
     */
    public function standardSet() : ilContainerFilterSet
    {
        return new ilContainerFilterSet(
            [
                $this->field(0, ilContainerFilterField::STD_FIELD_TITLE),
                $this->field(0, ilContainerFilterField::STD_FIELD_DESCRIPTION),
                $this->field(0, ilContainerFilterField::STD_FIELD_TITLE_DESCRIPTION),
                $this->field(0, ilContainerFilterField::STD_FIELD_KEYWORD),
                $this->field(0, ilContainerFilterField::STD_FIELD_AUTHOR),
                $this->field(0, ilContainerFilterField::STD_FIELD_COPYRIGHT),
                $this->field(0, ilContainerFilterField::STD_FIELD_TUTORIAL_SUPPORT),
                $this->field(0, ilContainerFilterField::STD_FIELD_OBJECT_TYPE)
            ]
        );
    }

    /**
     * User filter
     */
    public function userFilter(?array $data) : ilContainerUserFilter
    {
        return new ilContainerUserFilter($data);
    }
}
