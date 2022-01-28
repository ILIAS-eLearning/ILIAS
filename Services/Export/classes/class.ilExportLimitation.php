<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Export limitation checker
 * @author killing@leifos.de
 */
class ilExportLimitation
{
    protected const SET_LIMIT_NUMBER = "rep_export_limit_number";
    protected const SET_LIMITATION = "rep_export_limitation";
    public const SET_EXPORT_DISABLED = 1;
    public const SET_EXPORT_LIMITED = 0;

    protected ilTree $tree;
    protected ilSetting $settings;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $this->lng = $DIC->language();
    }

    public function getLimitationMode() : int
    {
        return (int) $this->settings->get(self::SET_LIMITATION);
    }

    public function getLimitationNumber() : int
    {
        return (int) $this->settings->get(self::SET_LIMIT_NUMBER);
    }

    public function checkLimitation(int $ref_id, array $options) : void
    {
        $max = (int) $this->settings->get(self::SET_LIMIT_NUMBER);

        if ($this->getLimitationMode() == self::SET_EXPORT_DISABLED) {
            throw new ilExportLimitationExceptionException(
                $this->lng->txt("exp_error_disabled")
            );
        }

        $cnt = 0;
        foreach ($this->tree->getSubTree($root = $this->tree->getNodeData($ref_id)) as $node) {
            if (isset($options[$node["child"]]) && (in_array((int) $options[$node["child"]]["type"], [1, 2]))) {
                $cnt++;
            }
        }
        if ($max > 0 && $cnt > $max) {
            throw new ilExportLimitationExceptionException(str_replace(
                "%1",
                (string) $max,
                $this->lng->txt("exp_error_too_many_objects")
            ));
        }
    }
}
