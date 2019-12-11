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
* Meta Data class (element educational)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDEducational extends ilMDBase
{
    // Methods for child objects (TypicalAgeRange, Description, Language)
    public function &getTypicalAgeRangeIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTypicalAgeRange.php';

        return ilMDTypicalAgeRange::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_educational');
    }
    public function &getTypicalAgeRange($a_typical_age_range_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTypicalAgeRange.php';

        if (!$a_typical_age_range_id) {
            return false;
        }
        $typ = new ilMDTypicalAgeRange();
        $typ->setMetaId($a_typical_age_range_id);

        return $typ;
    }
    public function &addTypicalAgeRange()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTypicalAgeRange.php';

        $typ = new ilMDTypicalAgeRange($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $typ->setParentId($this->getMetaId());
        $typ->setParentType('meta_educational');

        return $typ;
    }
    public function &getDescriptionIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDDescription.php';

        return ilMDDescription::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_educational');
    }
    public function &getDescription($a_description_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDDescription.php';

        if (!$a_description_id) {
            return false;
        }
        $des = new ilMDDescription();
        $des->setMetaId($a_description_id);

        return $des;
    }
    public function &addDescription()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDDescription.php';

        $des = new ilMDDescription($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $des->setParentId($this->getMetaId());
        $des->setParentType('meta_educational');

        return $des;
    }
    public function &getLanguageIds()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLanguage.php';

        return ilMDLanguage::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_educational');
    }
    public function &getLanguage($a_language_id)
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLanguage.php';

        if (!$a_language_id) {
            return false;
        }
        $lan = new ilMDLanguage();
        $lan->setMetaId($a_language_id);

        return $lan;
    }
    public function &addLanguage()
    {
        include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDLanguage.php';
        
        $lan = new ilMDLanguage($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $lan->setParentId($this->getMetaId());
        $lan->setParentType('meta_educational');

        return $lan;
    }

    // SET/GET
    public function setInteractivityType($a_iat)
    {
        switch ($a_iat) {
            case 'Active':
            case 'Expositive':
            case 'Mixed':
                $this->interactivity_type = $a_iat;
                return true;

            default:
                return false;
        }
    }
    public function getInteractivityType()
    {
        return $this->interactivity_type;
    }
    public function setLearningResourceType($a_lrt)
    {
        switch ($a_lrt) {
            case 'Exercise':
            case 'Simulation':
            case 'Questionnaire':
            case 'Diagram':
            case 'Figure':
            case 'Graph':
            case 'Index':
            case 'Slide':
            case 'Table':
            case 'NarrativeText':
            case 'Exam':
            case 'Experiment':
            case 'ProblemStatement':
            case 'SelfAssessment':
            case 'Lecture':
                $this->learning_resource_type = $a_lrt;
                return true;

            default:
                return false;
        }
    }
    public function getLearningResourceType()
    {
        return $this->learning_resource_type;
    }
    public function setInteractivityLevel($a_iat)
    {
        switch ($a_iat) {
            case 'VeryLow':
            case 'Low':
            case 'Medium':
            case 'High':
            case 'VeryHigh':
                $this->interactivity_level = $a_iat;
                return true;

            default:
                return false;
        }
    }
    public function getInteractivityLevel()
    {
        return $this->interactivity_level;
    }
    public function setSemanticDensity($a_sd)
    {
        switch ($a_sd) {
            case 'VeryLow':
            case 'Low':
            case 'Medium':
            case 'High':
            case 'VeryHigh':
                $this->semantic_density = $a_sd;
                return true;

            default:
                return false;
        }
    }
    public function getSemanticDensity()
    {
        return $this->semantic_density;
    }
    public function setIntendedEndUserRole($a_ieur)
    {
        switch ($a_ieur) {
            case 'Teacher':
            case 'Author':
            case 'Learner':
            case 'Manager':
                $this->intended_end_user_role = $a_ieur;
                return true;

            default:
                return false;
        }
    }
    public function getIntendedEndUserRole()
    {
        return $this->intended_end_user_role;
    }
    public function setContext($a_context)
    {
        switch ($a_context) {
            case 'School':
            case 'HigherEducation':
            case 'Training':
            case 'Other':
                $this->context = $a_context;
                return true;

            default:
                return false;
        }
    }
    public function getContext()
    {
        return $this->context;
    }
    public function setDifficulty($a_difficulty)
    {
        switch ($a_difficulty) {
            case 'VeryEasy':
            case 'Easy':
            case 'Medium':
            case 'Difficult':
            case 'VeryDifficult':
                $this->difficulty = $a_difficulty;
                return true;

            default:
                return false;
        }
    }
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function setPhysicalTypicalLearningTime($months, $days, $hours, $minutes, $seconds)
    {
        if (!$months and !$days and !$hours and !$minutes and !$seconds) {
            $this->setTypicalLearningTime('PT00H00M');
            return true;
        }
        $tlt = 'P';
        if ($months) {
            $tlt .= ($months . 'M');
        }
        if ($days) {
            $tlt .= ($days . 'D');
        }
        if ($hours or $minutes or $seconds) {
            $tlt .= 'T';
        }
        if ($hours) {
            $tlt .= ($hours . 'H');
        }
        if ($minutes) {
            $tlt .= ($minutes . 'M');
        }
        if ($seconds) {
            $tlt .= ($seconds . 'S');
        }
        $this->setTypicalLearningTime($tlt);
        return true;
    }


    public function setTypicalLearningTime($a_tlt)
    {
        $this->typical_learning_time = $a_tlt;
    }
    public function getTypicalLearningTime()
    {
        return $this->typical_learning_time;
    }

    public function getTypicalLearningTimeSeconds()
    {
        include_once './Services/Migration/DBUpdate_5295/classes/class.ilMDUtils.php';

        $time_arr = ilMDUtils::_LOMDurationToArray($this->getTypicalLearningTime());

        return 60 * 60 * 24 * 30 * $time_arr[0] + 60 * 60 * 24 * $time_arr[1] + 60 * 60 * $time_arr[2] + 60 * $time_arr[3] + $time_arr[4];
    }
    
    public function save()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $fields = $this->__getFields();
        $fields['meta_educational_id'] = array('integer',$next_id = $ilDB->nextId('il_meta_educational'));
        
        if ($this->db->insert('il_meta_educational', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return false;
    }

    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            if ($this->db->update(
                'il_meta_educational',
                $this->__getFields(),
                array("meta_educational_id" => array('integer',$this->getMetaId()))
            )) {
                return true;
            }
        }
        return false;
    }

    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_educational " .
                "WHERE meta_educational_id = " . $ilDB->quote($this->getMetaId());
            $res = $ilDB->manipulate($query);

            foreach ($this->getTypicalAgeRangeIds() as $id) {
                $typ = $this->getTypicalAgeRange($id);
                $typ->delete();
            }
            foreach ($this->getDescriptionIds() as $id) {
                $des = $this->getDescription($id);
                $des->delete();
            }
            foreach ($this->getLanguageIds() as $id) {
                $lan = $this->getLanguage($id);
                $lan->delete();
            }

            
            return true;
        }
        return false;
    }
            

    public function __getFields()
    {
        return array('rbac_id'	=> array('integer',$this->getRBACId()),
                     'obj_id'	=> array('integer',$this->getObjId()),
                     'obj_type'	=> array('text',$this->getObjType()),
                     'interactivity_type' => array('text',$this->getInteractivityType()),
                     'learning_resource_type' => array('text',$this->getLearningResourceType()),
                     'interactivity_level' => array('text',$this->getInteractivityLevel()),
                     'semantic_density' => array('text',$this->getSemanticDensity()),
                     'intended_end_user_role' => array('text',$this->getIntendedEndUserRole()),
                     'context' => array('text',$this->getContext()),
                     'difficulty' => array('text',$this->getDifficulty()),
                     'typical_learning_time' => array('text',$this->getTypicalLearningTime()));
    }

    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_educational " .
                "WHERE meta_educational_id = " . $ilDB->quote($this->getMetaId(), 'integer');

        
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId($row->rbac_id);
                $this->setObjId($row->obj_id);
                $this->setObjType($row->obj_type);
                $this->setInteractivityType($row->interactivity_type);
                $this->setLearningResourceType($row->learning_resource_type);
                $this->setInteractivityLevel($row->interactivity_level);
                $this->setSemanticDensity($row->semantic_density);
                $this->setIntendedEndUserRole($row->intended_end_user_role);
                $this->setContext($row->context);
                $this->setDifficulty($row->difficulty);
                $this->setTypicalLearningTime($row->typical_learning_time);
            }
            return true;
        }
        return false;
    }
                
    /*
     * XML Export of all meta data
     * @param object (xml writer) see class.ilMD2XML.php
     *
     */
    public function toXML(&$writer)
    {
        $writer->xmlStartTag(
            'Educational',
            array('InteractivityType' => $this->getInteractivityType()
                                   ? $this->getInteractivityType()
                                   : 'Active',
                                   'LearningResourceType' => $this->getLearningResourceType()
                                   ? $this->getLearningResourceType()
                                   : 'Exercise',
                                   'InteractivityLevel' => $this->getInteractivityLevel()
                                   ? $this->getInteractivityLevel()
                                   : 'Medium',
                                   'SemanticDensity' => $this->getSemanticDensity()
                                   ? $this->getSemanticDensity()
                                   : 'Medium',
                                   'IntendedEndUserRole' => $this->getIntendedEndUserRole()
                                   ? $this->getIntendedEndUserRole()
                                   : 'Learner',
                                   'Context' => $this->getContext()
                                   ? $this->getContext()
                                   : 'Other',
                                   'Difficulty' => $this->getDifficulty()
                                   ? $this->getDifficulty()
                                   : 'Medium')
        );
                             
        // TypicalAgeRange
        $typ_ages = $this->getTypicalAgeRangeIds();
        foreach ($typ_ages as $id) {
            $key =&$this->getTypicalAgeRange($id);
            
            // extra test due to bug 5316 (may be due to eLaix import)
            if (is_object($key)) {
                $key->toXML($writer);
            }
        }
        if (!count($typ_ages)) {
            include_once 'Services/Migration/DBUpdate_5295/classes/class.ilMDTypicalAgeRange.php';
            $typ = new ilMDTypicalAgeRange($this->getRBACId(), $this->getObjId());
            $typ->toXML($writer);
        }

        // TypicalLearningTime
        $writer->xmlElement('TypicalLearningTime', null, $this->getTypicalLearningTime());

        // Description
        foreach ($this->getDescriptionIds() as $id) {
            $key =&$this->getDescription($id);
            $key->toXML($writer);
        }
        // Language
        foreach ($this->getLanguageIds() as $id) {
            $lang =&$this->getLanguage($id);
            $lang->toXML($writer);
        }
        $writer->xmlEndTag('Educational');
    }
    // STATIC
    public static function _getId($a_rbac_id, $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT meta_educational_id FROM il_meta_educational " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->meta_educational_id;
        }
        return false;
    }

    public static function _getTypicalLearningTimeSeconds($a_rbac_id, $a_obj_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $a_obj_id = $a_obj_id ? $a_obj_id : $a_rbac_id;

        $query = "SELECT typical_learning_time FROM il_meta_educational " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            include_once './Services/Migration/DBUpdate_5295/classes/class.ilMDUtils.php';

            $time_arr = ilMDUtils::_LOMDurationToArray($row->typical_learning_time);

            return 60 * 60 * 24 * 30 * $time_arr[0] +
                60 * 60 * 24 * $time_arr[1] +
                60 * 60 * $time_arr[2] +
                60 * $time_arr[3] +
                $time_arr[4];
        }
        return 0;
    }
}
