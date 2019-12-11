<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class acts as Model for all system styles settings related settings
 * such as activated or default system styles etc, be it in database or inifile.
 * Do not use this class to get the current system style, use ilStyleDefinition insteaed.
 *
 * Semantics of terms style, sub style, skin, template --> see ilStyleDefinition
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 *
 * @version $Id$
 * @ingroup ServicesStyle
 *
 */
class ilSystemStyleSettings
{
    /**
     * lookup if a style is activated
     *
     * @param $a_skin
     * @param $a_style
     * @return bool
     */
    public static function _lookupActivatedStyle($a_skin, $a_style)
    {
        global $DIC;

        $q = "SELECT count(*) cnt FROM settings_deactivated_s" .
            " WHERE skin = " . $DIC->database()->quote($a_skin, "text") .
            " AND style = " . $DIC->database()->quote($a_style, "text") . " ";

        $cnt_set = $DIC->database()->query($q);
        $cnt_rec = $DIC->database()->fetchAssoc($cnt_set);

        if ($cnt_rec["cnt"] > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * deactivate system style
     *
     * @param $a_skin
     * @param $a_style
     */
    public static function _deactivateStyle($a_skin, $a_style)
    {
        global $DIC;

        ilSystemStyleSettings::_activateStyle($a_skin, $a_style);
        $q = "INSERT into settings_deactivated_s" .
            " (skin, style) VALUES " .
            " (" . $DIC->database()->quote($a_skin, "text") . "," .
            " " . $DIC->database()->quote($a_style, "text") . ")";

        $DIC->database()->manipulate($q);
    }

    /**
     * activate system style
     *
     * @param $a_skin
     * @param $a_style
     */
    public static function _activateStyle($a_skin, $a_style)
    {
        global $DIC;

        $q = "DELETE FROM settings_deactivated_s" .
            " WHERE skin = " . $DIC->database()->quote($a_skin, "text") .
            " AND style = " . $DIC->database()->quote($a_style, "text");

        $DIC->database()->manipulate($q);
    }

    /**
     * Get all system sub styles category assignments. This is used to check wheter a system sub style is to be used
     * in a particular category.
     *
     * @param string $a_skin_id skin id
     * @param string $a_style_id style id
     * @return array ('substyle' => substyle_id, 'ref id' => cat_ref_id)
     */
    public static function getSystemStyleCategoryAssignments($a_skin_id, $a_style_id)
    {
        global $DIC;

        $assignments = [];
        $set = $DIC->database()->query(
            "SELECT substyle, category_ref_id FROM syst_style_cat " .
                " WHERE skin_id = " . $DIC->database()->quote($a_skin_id, "text") .
                " AND style_id = " . $DIC->database()->quote($a_style_id, "text")
        );
        while (($rec = $DIC->database()->fetchAssoc($set))) {
            $assignments[] = [
                    "substyle" => $rec["substyle"],
                    "ref_id" => $rec["category_ref_id"]
            ];
        }
        return $assignments;
    }

    /**
     * Get all system category assignments of exactly one substyle. This is used to check wheter a system sub style is to be used
     * in a particular category.
     *
     * @param $a_skin_id
     * @param $a_style_id
     * @param $a_sub_style_id
     * @return array
     */
    public static function getSubStyleCategoryAssignments($a_skin_id, $a_style_id, $a_sub_style_id)
    {
        global $DIC;

        $assignmnts = [];

        $set = $DIC->database()->query(
            "SELECT substyle, category_ref_id FROM syst_style_cat " .
                " WHERE skin_id = " . $DIC->database()->quote($a_skin_id, "text") .
                " AND substyle = " . $DIC->database()->quote($a_sub_style_id, "text") .
                " AND style_id = " . $DIC->database()->quote($a_style_id, "text")
        );
        while (($rec = $DIC->database()->fetchAssoc($set))) {
            $assignmnts[] = [
                    "substyle" => $rec["substyle"],
                    "ref_id" => $rec["category_ref_id"]
            ];
        }
        return $assignmnts;
    }

    /**
     * Sets a substyle category assignment.
     *
     * @param $a_skin_id
     * @param $a_style_id
     * @param $a_substyle
     * @param $a_ref_id
     * @throws ilSystemStyleException
     */
    public static function writeSystemStyleCategoryAssignment(
        $a_skin_id,
        $a_style_id,
        $a_substyle,
        $a_ref_id
    ) {
        global $DIC;

        $assignments = self::getSubStyleCategoryAssignments($a_skin_id, $a_style_id, $a_substyle);

        foreach ($assignments as $assignment) {
            if ($assignment["ref_id"] == $a_ref_id) {
                throw new ilSystemStyleException(ilSystemStyleException::SUBSTYLE_ASSIGNMENT_EXISTS, $a_substyle . ": " . $a_ref_id);
            }
        }
        $DIC->database()->manipulate("INSERT INTO syst_style_cat " .
                "(skin_id, style_id, substyle, category_ref_id) VALUES (" .
                $DIC->database()->quote($a_skin_id, "text") . "," .
                $DIC->database()->quote($a_style_id, "text") . "," .
                $DIC->database()->quote($a_substyle, "text") . "," .
                $DIC->database()->quote($a_ref_id, "integer") .
                ")");
    }

    /**
     * Deletes all sub style category assignment of a system style. This is used if a system style is deleted
     * completely
     *
     * @param $a_skin_id
     * @param $a_style_id
     * @param $a_substyle
     * @param $a_ref_id
     */
    public static function deleteSystemStyleCategoryAssignment(
        $a_skin_id,
        $a_style_id,
        $a_substyle,
        $a_ref_id
    ) {
        global $DIC;

        $DIC->database()->manipulate("DELETE FROM syst_style_cat WHERE " .
                " skin_id = " . $DIC->database()->quote($a_skin_id, "text") .
                " AND style_id = " . $DIC->database()->quote($a_style_id, "text") .
                " AND substyle = " . $DIC->database()->quote($a_substyle, "text") .
                " AND category_ref_id = " . $DIC->database()->quote($a_ref_id, "integer"));
    }

    /**
     * Delets a sub styles category assignment.
     *
     * @param $a_skin_id
     * @param $a_style_id
     * @param $a_substyle
     */
    public static function deleteSubStyleCategoryAssignments($a_skin_id, $a_style_id, $a_substyle)
    {
        global $DIC;

        $DIC->database()->manipulate("DELETE FROM syst_style_cat WHERE " .
                " skin_id = " . $DIC->database()->quote($a_skin_id, "text") .
                " AND style_id = " . $DIC->database()->quote($a_style_id, "text") .
                " AND substyle = " . $DIC->database()->quote($a_substyle, "text"));
    }

    /**
     * Sets a users preferred system skin/style by using the user object.
     *
     * @param $skin_id
     * @param $style_id
     */
    public static function setCurrentUserPrefStyle($skin_id, $style_id)
    {
        global $DIC;

        $DIC->user()->setPref("skin", $skin_id);
        $DIC->user()->setPref("style", $style_id);
        $DIC->user()->update();
    }

    /**
     * Gets a users preferred skin by using the user object.
     *
     * @return bool
     */
    public static function getCurrentUserPrefSkin()
    {
        global $DIC;

        return $DIC->user()->getPref("skin");
    }

    /**
     * Gets a users preferred style by using the user object.
     *
     * @return bool
     */
    public static function getCurrentUserPrefStyle()
    {
        global $DIC;

        return $DIC->user()->getPref("style");
    }

    /**
     * Sets the default style of the system
     *
     * @param $skin_id
     * @param $style_id
     */
    public static function setCurrentDefaultStyle($skin_id, $style_id)
    {
        global $DIC;

        $DIC->clientIni()->setVariable("layout", "skin", $skin_id);
        $DIC->clientIni()->setVariable("layout", "style", $style_id);
        $DIC->clientIni()->write();
        self::_activateStyle($skin_id, $style_id);
    }

    public static function resetDefaultToDelos()
    {
        $system_style_conf = new ilSystemStyleConfig();

        self::setCurrentDefaultStyle($system_style_conf->getDefaultSkinId(), $system_style_conf->getDefaultSkinId());
    }

    /**
     * Gets default Skin of the System
     *
     * @return string
     */
    public static function getCurrentDefaultSkin()
    {
        global $DIC;

        $skin_id = $DIC->clientIni()->readVariable("layout", "skin");

        if (!ilStyleDefinition::skinExists($skin_id)) {
            self::resetDefaultToDelos();
            $skin_id = $DIC->clientIni()->readVariable("layout", "skin");
        }
        return $skin_id;
    }

    /**
     * Gets default style of the system
     *
     * @return mixed
     * @throws ilSystemStyleException
     */
    public static function getCurrentDefaultStyle()
    {
        global $DIC;
        $skin_id = $DIC->clientIni()->readVariable("layout", "skin");
        $style_id = $DIC->clientIni()->readVariable("layout", "style");

        if (!ilStyleDefinition::styleExistsForSkinId($skin_id, $style_id)) {
            self::resetDefaultToDelos();
            $style_id = $DIC->clientIni()->readVariable("layout", "style");
        }
        return $style_id;
    }
}
