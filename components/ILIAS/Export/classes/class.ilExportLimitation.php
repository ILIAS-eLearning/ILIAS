<?php

declare(strict_types=1);

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

    public function getLimitationMode(): int
    {
        return (int) $this->settings->get(self::SET_LIMITATION);
    }

    public function getLimitationNumber(): int
    {
        return (int) $this->settings->get(self::SET_LIMIT_NUMBER);
    }

    public function checkLimitation(int $ref_id, array $options): void
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
