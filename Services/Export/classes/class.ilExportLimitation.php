<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Export limitation checker
 *
 * @author killing@leifos.de
 */
class ilExportLimitation
{
    const SET_LIMIT_NUMBER = "rep_export_limit_number";
    const SET_LIMITATION = "rep_export_limitation";
    const SET_EXPORT_DISABLED = 1;
    const SET_EXPORT_LIMITED = 0;


    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
    }

    /**
     * Get limitation mode
     *
     * @return int
     */
    public function getLimitationMode()
    {
        return (int) $this->settings->get(self::SET_LIMITATION);
    }

    /**
     * Get limitation number
     *
     * @return int
     */
    public function getLimitationNumber()
    {
        return (int) $this->settings->get(self::SET_LIMIT_NUMBER);
    }

    /**
     * @param $ref_id
     * @param $options
     * @throws ilExportLimitationExceptionException
     */
    public function checkLimitation($ref_id, $options)
    {
        $tree = $this->tree;
        $settings = $this->settings;
        $lng = $this->lng;

        $max = (int) $settings->get(self::SET_LIMIT_NUMBER);

        if ($this->getLimitationMode() == self::SET_EXPORT_DISABLED) {
            throw new ilExportLimitationExceptionException(
                $lng->txt("exp_error_disabled")
            );
        }

        $cnt = 0;
        foreach ($tree->getSubTree($root = $tree->getNodeData($ref_id)) as $node) {
            if (isset($options[$node["child"]]) && (in_array((int) $options[$node["child"]]["type"], [1,2]))) {
                $cnt++;
            }
        }
        if ($max > 0 && $cnt > $max) {
            throw new ilExportLimitationExceptionException(str_replace(
                "%1",
                $max,
                $lng->txt("exp_error_too_many_objects")
            ));
        }
    }
}
