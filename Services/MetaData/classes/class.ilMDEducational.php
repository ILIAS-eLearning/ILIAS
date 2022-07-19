<?php declare(strict_types=1);
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
 * @package ilias-core
 * @version $Id$
 */
class ilMDEducational extends ilMDBase
{
    private string $interactivity_type = '';
    private string $learning_resource_type = '';
    private string $interactivity_level = '';
    private string $semantic_density = '';
    private string $intended_end_user_role = '';
    private string $context = '';
    private string $difficulty = '';
    private string $typical_learning_time = '';

    /**
     * @return int[]
     */
    public function getTypicalAgeRangeIds() : array
    {
        return ilMDTypicalAgeRange::_getIds(
            $this->getRBACId(),
            $this->getObjId(),
            $this->getMetaId(),
            'meta_educational'
        );
    }

    public function getTypicalAgeRange(int $a_typical_age_range_id) : ?ilMDTypicalAgeRange
    {
        if (!$a_typical_age_range_id) {
            return null;
        }
        $typ = new ilMDTypicalAgeRange();
        $typ->setMetaId($a_typical_age_range_id);

        return $typ;
    }

    public function addTypicalAgeRange() : ilMDTypicalAgeRange
    {
        $typ = new ilMDTypicalAgeRange($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $typ->setParentId($this->getMetaId());
        $typ->setParentType('meta_educational');

        return $typ;
    }

    /**
     * @return int[]
     */
    public function getDescriptionIds() : array
    {
        return ilMDDescription::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_educational');
    }

    public function getDescription(int $a_description_id) : ?ilMDDescription
    {
        if (!$a_description_id) {
            return null;
        }
        $des = new ilMDDescription();
        $des->setMetaId($a_description_id);

        return $des;
    }

    public function addDescription() : ilMDDescription
    {
        $des = new ilMDDescription($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $des->setParentId($this->getMetaId());
        $des->setParentType('meta_educational');

        return $des;
    }

    /**
     * @return int[]
     */
    public function getLanguageIds() : array
    {
        return ilMDLanguage::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_educational');
    }

    public function getLanguage(int $a_language_id) : ?ilMDLanguage
    {
        if (!$a_language_id) {
            return null;
        }
        $lan = new ilMDLanguage();
        $lan->setMetaId($a_language_id);

        return $lan;
    }

    public function addLanguage() : ilMDLanguage
    {
        $lan = new ilMDLanguage($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $lan->setParentId($this->getMetaId());
        $lan->setParentType('meta_educational');

        return $lan;
    }

    // SET/GET
    public function setInteractivityType(string $a_iat) : bool
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

    public function getInteractivityType() : string
    {
        return $this->interactivity_type;
    }

    public function setLearningResourceType(string $a_lrt) : bool
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

    public function getLearningResourceType() : string
    {
        return $this->learning_resource_type;
    }

    public function setInteractivityLevel(string $a_iat) : bool
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

    public function getInteractivityLevel() : string
    {
        return $this->interactivity_level;
    }

    public function setSemanticDensity(string $a_sd) : bool
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

    public function getSemanticDensity() : string
    {
        return $this->semantic_density;
    }

    public function setIntendedEndUserRole(string $a_ieur) : bool
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

    public function getIntendedEndUserRole() : string
    {
        return $this->intended_end_user_role;
    }

    public function setContext(string $a_context) : bool
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

    public function getContext() : string
    {
        return $this->context;
    }

    public function setDifficulty(string $a_difficulty) : bool
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

    public function getDifficulty() : string
    {
        return $this->difficulty;
    }

    public function setPhysicalTypicalLearningTime(
        int $months,
        int $days,
        int $hours,
        int $minutes,
        int $seconds
    ) : bool {
        if (!$months && !$days && !$hours && !$minutes && !$seconds) {
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
        if ($hours || $minutes || $seconds) {
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

    public function setTypicalLearningTime(string $a_tlt) : void
    {
        $this->typical_learning_time = $a_tlt;
    }

    public function getTypicalLearningTime() : string
    {
        return $this->typical_learning_time;
    }

    public function getTypicalLearningTimeSeconds() : int
    {
        $time_arr = ilMDUtils::_LOMDurationToArray($this->getTypicalLearningTime());
        if ($time_arr === []) {
            return 0;
        }
        return 60 * 60 * 24 * 30 * $time_arr[0] + 60 * 60 * 24 * $time_arr[1] + 60 * 60 * $time_arr[2] + 60 * $time_arr[3] + $time_arr[4];
    }

    public function save() : int
    {
        $fields = $this->__getFields();
        $fields['meta_educational_id'] = array('integer', $next_id = $this->db->nextId('il_meta_educational'));

        if ($this->db->insert('il_meta_educational', $fields)) {
            $this->setMetaId($next_id);
            return $this->getMetaId();
        }
        return 0;
    }

    public function update() : bool
    {
        return $this->getMetaId() && $this->db->update(
            'il_meta_educational',
            $this->__getFields(),
            array("meta_educational_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete() : bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_educational " .
                "WHERE meta_educational_id = " . $this->db->quote($this->getMetaId(), ilDBConstants::T_INTEGER);
            $res = $this->db->manipulate($query);

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

    /**
     * @return array<string, array<string, mixed>>
     */
    public function __getFields() : array
    {
        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'interactivity_type' => array('text', $this->getInteractivityType()),
            'learning_resource_type' => array('text', $this->getLearningResourceType()),
            'interactivity_level' => array('text', $this->getInteractivityLevel()),
            'semantic_density' => array('text', $this->getSemanticDensity()),
            'intended_end_user_role' => array('text', $this->getIntendedEndUserRole()),
            'context' => array('text', $this->getContext()),
            'difficulty' => array('text', $this->getDifficulty()),
            'typical_learning_time' => array('text', $this->getTypicalLearningTime())
        );
    }

    public function read() : bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_educational " .
                "WHERE meta_educational_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType((string) $row->obj_type);
                $this->setInteractivityType((string) $row->interactivity_type);
                $this->setLearningResourceType((string) $row->learning_resource_type);
                $this->setInteractivityLevel((string) $row->interactivity_level);
                $this->setSemanticDensity((string) $row->semantic_density);
                $this->setIntendedEndUserRole((string) $row->intended_end_user_role);
                $this->setContext((string) $row->context);
                $this->setDifficulty((string) $row->difficulty);
                $this->setTypicalLearningTime((string) $row->typical_learning_time);
            }
            return true;
        }
        return false;
    }

    public function toXML(ilXmlWriter $writer) : void
    {
        $writer->xmlStartTag(
            'Educational',
            array(
                'InteractivityType' => $this->getInteractivityType() ?: 'Active',
                'LearningResourceType' => $this->getLearningResourceType() ?: 'Exercise',
                'InteractivityLevel' => $this->getInteractivityLevel() ?: 'Medium',
                'SemanticDensity' => $this->getSemanticDensity() ?: 'Medium',
                'IntendedEndUserRole' => $this->getIntendedEndUserRole() ?: 'Learner',
                'Context' => $this->getContext() ?: 'Other',
                'Difficulty' => $this->getDifficulty() ?: 'Medium'
            )
        );

        // TypicalAgeRange
        $typ_ages = $this->getTypicalAgeRangeIds();
        foreach ($typ_ages as $id) {
            $key = $this->getTypicalAgeRange($id);

            // extra test due to bug 5316 (may be due to eLaix import)
            if (is_object($key)) {
                $key->toXML($writer);
            }
        }
        if (!count($typ_ages)) {
            $typ = new ilMDTypicalAgeRange($this->getRBACId(), $this->getObjId());
            $typ->toXML($writer);
        }

        // TypicalLearningTime
        $writer->xmlElement('TypicalLearningTime', null, $this->getTypicalLearningTime());

        // Description
        foreach ($this->getDescriptionIds() as $id) {
            $key = $this->getDescription($id);
            $key->toXML($writer);
        }
        // Language
        foreach ($this->getLanguageIds() as $id) {
            $lang = $this->getLanguage($id);
            $lang->toXML($writer);
        }
        $writer->xmlEndTag('Educational');
    }

    // STATIC
    public static function _getId(int $a_rbac_id, int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT meta_educational_id FROM il_meta_educational " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->meta_educational_id;
        }
        return 0;
    }

    public static function _getTypicalLearningTimeSeconds(int $a_rbac_id, int $a_obj_id = 0) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $a_obj_id = $a_obj_id ?: $a_rbac_id;

        $query = "SELECT typical_learning_time FROM il_meta_educational " .
            "WHERE rbac_id = " . $ilDB->quote($a_rbac_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
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
