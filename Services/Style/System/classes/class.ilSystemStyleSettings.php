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

/**
 * This class acts as Model for all system styles settings related settings
 * such as activated or default system styles etc, be it in database or inifile.
 * Do not use this class to get the current system style, use ilStyleDefinition insteaed.
 * Semantics of terms style, sub style, skin, template --> see ilStyleDefinition
 */
class ilSystemStyleSettings
{
    /**
     * lookup if a style is activated
     */
    public static function _lookupActivatedStyle(string $a_skin, string $a_style) : bool
    {
        global $DIC;

        $q = 'SELECT count(*) cnt FROM settings_deactivated_s' .
            ' WHERE skin = ' . $DIC->database()->quote($a_skin, 'text') .
            ' AND style = ' . $DIC->database()->quote($a_style, 'text') . ' ';

        $cnt_set = $DIC->database()->query($q);
        $cnt_rec = $DIC->database()->fetchAssoc($cnt_set);

        if ($cnt_rec['cnt'] > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * deactivate system style
     */
    public static function _deactivateStyle(string $a_skin, string $a_style) : void
    {
        global $DIC;

        ilSystemStyleSettings::_activateStyle($a_skin, $a_style);
        $q = 'INSERT into settings_deactivated_s' .
            ' (skin, style) VALUES ' .
            ' (' . $DIC->database()->quote($a_skin, 'text') . ',' .
            ' ' . $DIC->database()->quote($a_style, 'text') . ')';

        $DIC->database()->manipulate($q);
    }

    /**
     * activate system style
     */
    public static function _activateStyle(string $a_skin, string $a_style) : void
    {
        global $DIC;

        $q = 'DELETE FROM settings_deactivated_s' .
            ' WHERE skin = ' . $DIC->database()->quote($a_skin, 'text') .
            ' AND style = ' . $DIC->database()->quote($a_style, 'text');

        $DIC->database()->manipulate($q);
    }

    /**
     * Get all system sub styles category assignments. This is used to check wheter a system sub style is to be used
     * in a particular category.
     * @return array ('substyle' => substyle_id, 'ref id' => cat_ref_id)
     */
    public static function getSystemStyleCategoryAssignments(string $a_skin_id, string $a_style_id) : array
    {
        global $DIC;

        $assignments = [];
        $set = $DIC->database()->query(
            'SELECT substyle, category_ref_id FROM syst_style_cat ' .
            ' WHERE skin_id = ' . $DIC->database()->quote($a_skin_id, 'text') .
            ' AND style_id = ' . $DIC->database()->quote($a_style_id, 'text')
        );
        while (($rec = $DIC->database()->fetchAssoc($set))) {
            $assignments[] = [
                'substyle' => $rec['substyle'],
                'ref_id' => $rec['category_ref_id']
            ];
        }
        return $assignments;
    }

    /**
     * Get all system category assignments of exactly one substyle. This is used to check wheter a system sub style is to be used
     * in a particular category.
     */
    public static function getSubStyleCategoryAssignments(
        string $a_skin_id,
        string $a_style_id,
        string $a_sub_style_id
    ) : array {
        global $DIC;

        $assignmnts = [];

        $set = $DIC->database()->query(
            'SELECT substyle, category_ref_id FROM syst_style_cat ' .
            ' WHERE skin_id = ' . $DIC->database()->quote($a_skin_id, 'text') .
            ' AND substyle = ' . $DIC->database()->quote($a_sub_style_id, 'text') .
            ' AND style_id = ' . $DIC->database()->quote($a_style_id, 'text')
        );
        while (($rec = $DIC->database()->fetchAssoc($set))) {
            $assignmnts[] = [
                'substyle' => $rec['substyle'],
                'ref_id' => $rec['category_ref_id']
            ];
        }
        return $assignmnts;
    }

    /**
     * Sets a substyle category assignment.
     * @throws ilSystemStyleException
     */
    public static function writeSystemStyleCategoryAssignment(
        string $a_skin_id,
        string $a_style_id,
        string $a_substyle,
        string $a_ref_id
    ) : void {
        global $DIC;

        $assignments = self::getSubStyleCategoryAssignments($a_skin_id, $a_style_id, $a_substyle);

        foreach ($assignments as $assignment) {
            if ($assignment['ref_id'] == $a_ref_id) {
                throw new ilSystemStyleException(
                    ilSystemStyleException::SUBSTYLE_ASSIGNMENT_EXISTS,
                    $a_substyle . ': ' . $a_ref_id
                );
            }
        }
        $DIC->database()->manipulate('INSERT INTO syst_style_cat ' .
            '(skin_id, style_id, substyle, category_ref_id) VALUES (' .
            $DIC->database()->quote($a_skin_id, 'text') . ',' .
            $DIC->database()->quote($a_style_id, 'text') . ',' .
            $DIC->database()->quote($a_substyle, 'text') . ',' .
            $DIC->database()->quote($a_ref_id, 'integer') .
            ')');
    }

    /**
     * Deletes all sub style category assignment of a system style. This is used if a system style is deleted
     * completely
     */
    public static function deleteSystemStyleCategoryAssignment(
        string $a_skin_id,
        string $a_style_id,
        string $a_substyle,
        string $a_ref_id
    ) : void {
        global $DIC;

        $DIC->database()->manipulate('DELETE FROM syst_style_cat WHERE ' .
            ' skin_id = ' . $DIC->database()->quote($a_skin_id, 'text') .
            ' AND style_id = ' . $DIC->database()->quote($a_style_id, 'text') .
            ' AND substyle = ' . $DIC->database()->quote($a_substyle, 'text') .
            ' AND category_ref_id = ' . $DIC->database()->quote($a_ref_id, 'integer'));
    }

    /**
     * Delets a sub styles category assignment.
     */
    public static function deleteSubStyleCategoryAssignments(string $a_skin_id, string $a_style_id, string $a_substyle) : void
    {
        global $DIC;

        $DIC->database()->manipulate('DELETE FROM syst_style_cat WHERE ' .
            ' skin_id = ' . $DIC->database()->quote($a_skin_id, 'text') .
            ' AND style_id = ' . $DIC->database()->quote($a_style_id, 'text') .
            ' AND substyle = ' . $DIC->database()->quote($a_substyle, 'text'));
    }

    /**
     * Updates an assignment, e.g. in case of ID Change through GUI.
     */
    public static function updateSkinIdAndStyleIDOfSubStyleCategoryAssignments(
        string $old_skin_id,
        string $old_style_id,
        string $new_skin_id,
        string $new_style_id
    ) : void {
        global $DIC;

        $DIC->database()->manipulate('UPDATE syst_style_cat ' .
            ' SET skin_id = ' . $DIC->database()->quote($new_skin_id, 'text')
            . ', style_id = ' . $DIC->database()->quote($new_style_id, 'text') .
            ' WHERE skin_id = ' . $DIC->database()->quote($old_skin_id, 'text') .
            ' AND style_id = ' . $DIC->database()->quote($old_style_id, 'text'));
    }

    /**
     * Updates an assignment, e.g. in case of ID Change through GUI.
     */
    public static function updateSubStyleIdfSubStyleCategoryAssignments(
        string $old_substyle_id,
        string $new_substyle_id
    ) : void {
        global $DIC;

        $DIC->database()->manipulate('UPDATE syst_style_cat ' .
            ' SET substyle = ' . $DIC->database()->quote($new_substyle_id, 'text') .
            ' WHERE substyle = ' . $DIC->database()->quote($old_substyle_id, 'text'));
    }

    /**
     * Sets a users preferred system skin/style by using the user object.
     */
    public static function setCurrentUserPrefStyle(string $skin_id, string $style_id) : void
    {
        global $DIC;

        $DIC->user()->setPref('skin', $skin_id);
        $DIC->user()->setPref('style', $style_id);
        $DIC->user()->update();
    }

    /**
     * Gets a users preferred skin by using the user object.
     */
    public static function getCurrentUserPrefSkin() : string
    {
        global $DIC;

        return $DIC->user()->getPref('skin');
    }

    /**
     * Gets a users preferred style by using the user object.
     */
    public static function getCurrentUserPrefStyle() : string
    {
        global $DIC;

        return $DIC->user()->getPref('style');
    }

    /**
     * Sets the default style of the system
     */
    public static function setCurrentDefaultStyle(string $skin_id, string $style_id) : void
    {
        global $DIC;

        $DIC->clientIni()->setVariable('layout', 'skin', $skin_id);
        $DIC->clientIni()->setVariable('layout', 'style', $style_id);
        $DIC->clientIni()->write();
        self::_activateStyle($skin_id, $style_id);
    }

    public static function resetDefaultToDelos() : void
    {
        $system_style_conf = new ilSystemStyleConfig();

        self::setCurrentDefaultStyle($system_style_conf->getDefaultSkinId(), $system_style_conf->getDefaultSkinId());
    }

    /**
     * Gets default Skin of the System
     * @return string
     */
    public static function getCurrentDefaultSkin() : string
    {
        global $DIC;

        $skin_id = $DIC->clientIni()->readVariable('layout', 'skin');

        if (!ilStyleDefinition::skinExists($skin_id)) {
            self::resetDefaultToDelos();
            $skin_id = $DIC->clientIni()->readVariable('layout', 'skin');
        }
        return $skin_id;
    }

    /**
     * Gets default style of the system
     * @throws ilSystemStyleException
     */
    public static function getCurrentDefaultStyle() : string
    {
        global $DIC;
        $skin_id = $DIC->clientIni()->readVariable('layout', 'skin');
        $style_id = $DIC->clientIni()->readVariable('layout', 'style');

        if (!ilStyleDefinition::styleExistsForSkinId($skin_id, $style_id)) {
            self::resetDefaultToDelos();
            $style_id = $DIC->clientIni()->readVariable('layout', 'style');
        }
        return $style_id;
    }
}
