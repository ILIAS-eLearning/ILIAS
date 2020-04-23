<?php

/**
 * Trait ilHelpDisplayed
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait ilHelpDisplayed
{
    /**
     * Show help tool?
     * @param
     * @return
     */
    protected function showHelpTool() : bool
    {
        static $show;
        if (!isset($show)) {
            global $DIC;

            $user = $DIC->user();
            $settings = $DIC->settings();

            if ($user->getLanguage() != "de") {
                return $show = false;
            }

            //if (ilSession::get("show_help_tool") != "1") {
            //    return $show = false;
            //}

            if ($settings->get("help_mode") == "2") {
                return $show = false;
            }

            if ((defined("OH_REF_ID") && OH_REF_ID > 0)) {
                return $show = true;
            } else {
                $module = (int) $settings->get("help_module");
                if ($module == 0) {
                    return $show = false;
                }
            }

            return $show = true;
        }

        return $show;
    }
}
