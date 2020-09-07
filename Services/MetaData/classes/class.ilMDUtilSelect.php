<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Utility class to form select boxed for fixed meta data attributes
*
* @author Stefan Meyer <meyer@leifos.com>
* @package ilias-core
* @version $Id$
*/
class ilMDUtilSelect
{
    /**
     * Prepare a meta data language selector
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getLanguageSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';

        global $DIC;

        $lng = $DIC['lng'];

        foreach (ilMDLanguageItem::_getPossibleLanguageCodes() as $code) {
            $tmp_options[$code] = $lng->txt('meta_l_' . $code);
        }
        asort($tmp_options, SORT_STRING);

        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }
        $options = array_merge($options, $tmp_options);
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }

    /**
     * Prepare a meta general structure selector
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getStructureSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Atomic','Collection','Networked','Hierarchical','Linear');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $lng->txt('meta_' . strtolower($item));
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta lifecycle status selector
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getStatusSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Draft','Final','Revised','Unavailable');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $lng->txt('meta_' . strtolower($item));
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta lifecycle status selector
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getRoleSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Author','Publisher','Unknown','Initiator','Terminator','Editor','GraphicalDesigner','TechnicalImplementer',
                       'ContentProvider','TechnicalValidator','EducationalValidator','ScriptWriter','InstructionalDesigner',
                       'SubjectMatterExpert','Creator','Validator','PointOfContact');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $lng->txt('meta_' . strtolower($item));
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta technical os selector
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getOperatingSystemSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('PC-DOS','MS-Windows','MAC-OS','Unix','Multi-OS','None');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta technical browser selector
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getBrowserSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Any','NetscapeCommunicator','MS-InternetExplorer','Opera','Amaya','Mozilla');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta technical format selector
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getFormatSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        $ilDB->setLimit(200, 0);
        // In case an index is defined on field il_meta_format, this group by
        // statement takes advantage of it to improve the performance of the query.
        $query = "SELECT format as forma from il_meta_format GROUP BY format";
        $res = $ilDB->query($query);
        if (!$res->numRows()) {
            return '';
        }
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (strlen($row->format)) {
                $options[$row->format] = substr($row->format, 0, 48);
            }
        }
        
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta technical duration selector
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    public static function _getDurationSelect($a_selected, $a_name, $prepend = array())
    {
        global $DIC;

        $lng = $DIC['lng'];

        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }
        
        $items = array(15 => '15 ' . $lng->txt('minutes'),
                      30 => '30 ' . $lng->txt('minutes'),
                      45 => '45 ' . $lng->txt('minutes'),
                      60 => '1 ' . $lng->txt('hour'),
                      90 => '1 ' . $lng->txt('hour') . ' 30 ' . $lng->txt('minutes'),
                      120 => '2 ' . $lng->txt('hours'),
                      180 => '3 ' . $lng->txt('hours'),
                      240 => '4 ' . $lng->txt('hours'));

        foreach ($items as $key => $item) {
            $options[$key] = $item;
        }
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta educational interactivity type
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getInteractivityTypeSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Actice','Expositive','Mixed');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta educational learning resource type
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getLearningResourceTypeSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Exercise','Simulation','Questionnaire','Diagram','Figure','Graph','Index',
                       'Slide','Table','NarrativeText','Exam','Experiment','ProblemStatement','SelfAssessment','Lecture');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta educational interactivity level
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    public static function _getInteractivityLevelSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array(1 => 'VeryLow',2 => 'Low',3 => 'Medium',4 => 'High',5 => 'VeryHigh');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $key => $item) {
            $options[$key] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta educational semantic density
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getSemanticDensitySelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array(1 => 'VeryLow',2 => 'Low',3 => 'Medium',4 => 'High',5 => 'VeryHigh');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $key => $item) {
            $options[$key] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta educational intended end user role
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getIntendedEndUserRoleSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Teacher','Author','Learner','Manager');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta context
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getContextSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('School','HigherEducation','Training','Other');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }


    /**
     * Prepare a meta location type
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    public static function _getLocationTypeSelect($a_selected, $a_name, $prepend = array())
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('LocalFile','Reference');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta educational difficulty
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getDifficultySelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array(1 => 'VeryEasy',2 => 'Easy',3 => 'Medium',4 => 'Difficult',5 => 'VeryDifficult');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $key => $item) {
            $options[$key] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta educational typical age range
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    public static function _getTypicalAgeRangeSelect($a_selected, $a_name, $prepend = array())
    {
        global $DIC;

        $lng = $DIC['lng'];

        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }
        for ($i = 1; $i < 100 ; $i++) {
            $items[$i] = $i;
        }
        foreach ($items as $key => $item) {
            $options[$key] = $item;
        }
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }

    /**
     * Prepare a meta educational typical learning time
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    public static function _getTypicalLearningTimeSelect($a_selected, $a_name, $prepend = array())
    {
        global $DIC;

        $lng = $DIC['lng'];

        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }
        $items = array(15 => '15 ' . $lng->txt('minutes'),
                      30 => '30 ' . $lng->txt('minutes'),
                      45 => '45 ' . $lng->txt('minutes'),
                      60 => '1 ' . $lng->txt('hour'),
                      90 => '1 ' . $lng->txt('hour') . ' 30 ' . $lng->txt('minutes'),
                      120 => '2 ' . $lng->txt('hours'),
                      180 => '3 ' . $lng->txt('hours'),
                      240 => '4 ' . $lng->txt('hours'));

        foreach ($items as $key => $item) {
            $options[$key] = $item;
        }
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta rights costs
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getCostsSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Yes','No');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta rights copyright and other restrictions
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getCopyrightAndOtherRestrictionsSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Yes','No');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
    /**
     * Prepare a meta rights copyright and other restrictions
     * All possible entries in meta_format are shown
     *
     * @param string Checked item
     * @param string Name of form variable.
     * @param array  array(value => 'string') of first item. E.g: array(0,'-Please select-')
     *
     * @return string Complete html select
     */
    // BEGIN PATCH Lucene search
    public static function _getPurposeSelect($a_selected, $a_name, $prepend = array(), $a_options_only = false)
    // END PATCH Lucene Search
    {
        global $DIC;

        $lng = $DIC['lng'];

        $items = array('Discipline','Idea','Prerequisite','EducationalObjective','AccessibilityRestrictions',
                       'EducationalLevel','SkillLevel','SecurityLevel','Competency');
        
        foreach ($prepend as $value => $translation) {
            $options[$value] = $translation;
        }

        foreach ($items as $item) {
            $options[$item] = $item;
        }
        // BEGIN PATCH Lucene search
        return $a_options_only ? $options : ilUtil::formSelect($a_selected, $a_name, $options, false, true);
        // END PATCH Lucene Search
        return ilUtil::formSelect($a_selected, $a_name, $options, false, true);
    }
}
