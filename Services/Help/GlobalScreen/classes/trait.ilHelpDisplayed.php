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
 * Trait ilHelpDisplayed
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilHelpDisplayed
{
    protected function showHelpTool(): bool
    {
        static $show;

        if (!isset($show)) {
            global $DIC;

            $user = $DIC->user();
            $settings = $DIC->settings();

            if ($user->getLanguage() !== "de") {
                return $show = false;
            }

            if ($settings->get("help_mode") === "2") {
                return $show = false;
            }

            if (defined("OH_REF_ID") && (int) OH_REF_ID > 0) {
                return $show = true;
            } else {
                $module = (int) $settings->get("help_module");
                if ($module === 0) {
                    return $show = false;
                }
            }

            return $show = true;
        }

        return $show;
    }
}
