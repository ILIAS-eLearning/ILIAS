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
 * Meta Data class (element educational)
 * @package ilias-core
 * @version $Id$
 */
class ilMDEducational extends ilMDBase
{
    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    private const INTERACTIVITY_TYPE_TRANSLATION = [
        'active' => 'Active',
        'expositive' => 'Expositive',
        'mixed' => 'Mixed'
    ];

    private const LEARNING_RESOURCE_TYPE_TRANSLATION = [
        'exercise' => 'Exercise',
        'simulation' => 'Simulation',
        'questionnaire' => 'Questionnaire',
        'diagram' => 'Diagram',
        'figure' => 'Figure',
        'graph' => 'Graph',
        'index' => 'Index',
        'slide' => 'Slide',
        'table' => 'Table',
        'narrative text' => 'NarrativeText',
        'exam' => 'Exam',
        'experiment' => 'Experiment',
        'problem statement' => 'ProblemStatement',
        'self assessment' => 'SelfAssessment',
        'lecture' => 'Lecture'
    ];

    private const INTERACTIVITY_LEVEL_TRANSLATION = [
        'very low' => 'VeryLow',
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'very high' => 'VeryHigh'
    ];

    private const SEMANTIC_DENSITY_TRANSLATION = [
        'very low' => 'VeryLow',
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'very high' => 'VeryHigh'
    ];

    private const INTENDED_END_USER_ROLE_TRANSLATION = [
        'teacher' => 'Teacher',
        'author' => 'Author',
        'learner' => 'Learner',
        'manager' => 'Manager'
    ];

    private const CONTEXT_TRANSLATION = [
        'school' => 'School',
        'higher education' => 'HigherEducation',
        'training' => 'Training',
        'other' => 'Other'
    ];

    private const DIFFICULTY_TRANSLATION = [
        'very easy' => 'VeryEasy',
        'easy' => 'Easy',
        'medium' => 'Medium',
        'difficult' => 'Difficult',
        'very difficult' => 'VeryDifficult'
    ];

    private string $interactivity_type = '';
    private string $learning_resource_type = '';
    private string $interactivity_level = '';
    private string $semantic_density = '';
    private string $intended_end_user_role = '';
    private string $context = '';
    private string $difficulty = '';
    private string $typical_learning_time = '';


    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    private int $learning_resource_type_id = 0;
    private int $intended_end_user_role_id = 0;
    private int $context_id = 0;

    /**
     * @return int[]
     */
    public function getTypicalAgeRangeIds(): array
    {
        return ilMDTypicalAgeRange::_getIds(
            $this->getRBACId(),
            $this->getObjId(),
            $this->getMetaId(),
            'meta_educational'
        );
    }

    public function getTypicalAgeRange(int $a_typical_age_range_id): ?ilMDTypicalAgeRange
    {
        if (!$a_typical_age_range_id) {
            return null;
        }
        $typ = new ilMDTypicalAgeRange();
        $typ->setMetaId($a_typical_age_range_id);

        return $typ;
    }

    public function addTypicalAgeRange(): ilMDTypicalAgeRange
    {
        $typ = new ilMDTypicalAgeRange($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $typ->setParentId($this->getMetaId());
        $typ->setParentType('meta_educational');

        return $typ;
    }

    /**
     * @return int[]
     */
    public function getDescriptionIds(): array
    {
        return ilMDDescription::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_educational');
    }

    public function getDescription(int $a_description_id): ?ilMDDescription
    {
        if (!$a_description_id) {
            return null;
        }
        $des = new ilMDDescription();
        $des->setMetaId($a_description_id);

        return $des;
    }

    public function addDescription(): ilMDDescription
    {
        $des = new ilMDDescription($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $des->setParentId($this->getMetaId());
        $des->setParentType('meta_educational');

        return $des;
    }

    /**
     * @return int[]
     */
    public function getLanguageIds(): array
    {
        return ilMDLanguage::_getIds($this->getRBACId(), $this->getObjId(), $this->getMetaId(), 'meta_educational');
    }

    public function getLanguage(int $a_language_id): ?ilMDLanguage
    {
        if (!$a_language_id) {
            return null;
        }
        $lan = new ilMDLanguage();
        $lan->setMetaId($a_language_id);

        return $lan;
    }

    public function addLanguage(): ilMDLanguage
    {
        $lan = new ilMDLanguage($this->getRBACId(), $this->getObjId(), $this->getObjType());
        $lan->setParentId($this->getMetaId());
        $lan->setParentType('meta_educational');

        return $lan;
    }

    // SET/GET
    public function setInteractivityType(string $a_iat): bool
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

    public function getInteractivityType(): string
    {
        return $this->interactivity_type;
    }

    public function setLearningResourceType(string $a_lrt): bool
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

    public function getLearningResourceType(): string
    {
        return $this->learning_resource_type;
    }

    public function setInteractivityLevel(string $a_iat): bool
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

    public function getInteractivityLevel(): string
    {
        return $this->interactivity_level;
    }

    public function setSemanticDensity(string $a_sd): bool
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

    public function getSemanticDensity(): string
    {
        return $this->semantic_density;
    }

    public function setIntendedEndUserRole(string $a_ieur): bool
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

    public function getIntendedEndUserRole(): string
    {
        return $this->intended_end_user_role;
    }

    public function setContext(string $a_context): bool
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

    public function getContext(): string
    {
        return $this->context;
    }

    public function setDifficulty(string $a_difficulty): bool
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

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function setPhysicalTypicalLearningTime(
        int $months,
        int $days,
        int $hours,
        int $minutes,
        int $seconds
    ): bool {
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

    public function setTypicalLearningTime(string $a_tlt): void
    {
        $this->typical_learning_time = $a_tlt;
    }

    public function getTypicalLearningTime(): string
    {
        return $this->typical_learning_time;
    }

    public function getTypicalLearningTimeSeconds(): int
    {
        $time_arr = ilMDUtils::_LOMDurationToArray($this->getTypicalLearningTime());
        if ($time_arr === []) {
            return 0;
        }
        return 60 * 60 * 24 * 30 * $time_arr[0] + 60 * 60 * 24 * $time_arr[1] + 60 * 60 * $time_arr[2] + 60 * $time_arr[3] + $time_arr[4];
    }

    public function save(): int
    {
        $fields = $this->__getFields();
        $fields['meta_educational_id'] = array('integer', $next_id = $this->db->nextId('il_meta_educational'));

        if ($this->db->insert('il_meta_educational', $fields)) {
            $this->setMetaId($next_id);
            $this->createOrUpdateLearningResourceType();
            $this->createOrUpdateIntendedEndUserRole();
            $this->createOrUpdateContext();
            return $this->getMetaId();
        }
        return 0;
    }

    public function update(): bool
    {
        if (!$this->getMetaId()) {
            return false;
        }

        $this->createOrUpdateLearningResourceType();
        $this->createOrUpdateIntendedEndUserRole();
        $this->createOrUpdateContext();

        return (bool) $this->db->update(
            'il_meta_educational',
            $this->__getFields(),
            array("meta_educational_id" => array('integer', $this->getMetaId()))
        );
    }

    public function delete(): bool
    {
        if ($this->getMetaId()) {
            $query = "DELETE FROM il_meta_educational " .
                "WHERE meta_educational_id = " . $this->db->quote($this->getMetaId(), ilDBConstants::T_INTEGER);
            $res = $this->db->manipulate($query);

            $this->deleteAllLearningResourceTypes();
            $this->deleteAllIntendedEndUserRoles();
            $this->deleteAllContexts();

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
    public function __getFields(): array
    {
        /**
         * Compatibility fix for legacy MD classes for new db tables
         */
        $interactivity_type = (string) array_search(
            $this->getInteractivityType(),
            self::INTERACTIVITY_TYPE_TRANSLATION
        );
        $interactivity_level = (string) array_search(
            $this->getInteractivityLevel(),
            self::INTERACTIVITY_LEVEL_TRANSLATION
        );
        $semantic_density = (string) array_search(
            $this->getSemanticDensity(),
            self::SEMANTIC_DENSITY_TRANSLATION
        );
        $difficulty = (string) array_search(
            $this->getDifficulty(),
            self::DIFFICULTY_TRANSLATION
        );

        return array(
            'rbac_id' => array('integer', $this->getRBACId()),
            'obj_id' => array('integer', $this->getObjId()),
            'obj_type' => array('text', $this->getObjType()),
            'interactivity_type' => array('text', $interactivity_type),
            //'learning_resource_type' => array('text', $this->getLearningResourceType()),
            'interactivity_level' => array('text', $interactivity_level),
            'semantic_density' => array('text', $semantic_density),
            //'intended_end_user_role' => array('text', $this->getIntendedEndUserRole()),
            //'context' => array('text', $this->getContext()),
            'difficulty' => array('text', $difficulty),
            'typical_learning_time' => array('text', $this->getTypicalLearningTime())
        );
    }

    public function read(): bool
    {
        if ($this->getMetaId()) {
            $query = "SELECT * FROM il_meta_educational " .
                "WHERE meta_educational_id = " . $this->db->quote($this->getMetaId(), 'integer');

            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                /**
                 * Compatibility fix for legacy MD classes for new db tables
                 */
                if (key_exists($row->interactivity_type ?? '', self::INTERACTIVITY_TYPE_TRANSLATION)) {
                    $row->interactivity_type = self::INTERACTIVITY_TYPE_TRANSLATION[$row->interactivity_type ?? ''];
                }
                if (key_exists($row->interactivity_level ?? '', self::INTERACTIVITY_LEVEL_TRANSLATION)) {
                    $row->interactivity_level = self::INTERACTIVITY_LEVEL_TRANSLATION[$row->interactivity_level ?? ''];
                }
                if (key_exists($row->semantic_density ?? '', self::SEMANTIC_DENSITY_TRANSLATION)) {
                    $row->semantic_density = self::SEMANTIC_DENSITY_TRANSLATION[$row->semantic_density ?? ''];
                }
                if (key_exists($row->difficulty ?? '', self::DIFFICULTY_TRANSLATION)) {
                    $row->difficulty = self::DIFFICULTY_TRANSLATION[$row->difficulty ?? ''];
                }

                $this->setRBACId((int) $row->rbac_id);
                $this->setObjId((int) $row->obj_id);
                $this->setObjType($row->obj_type ?? '');
                $this->setInteractivityType($row->interactivity_type ?? '');
                //$this->setLearningResourceType($row->learning_resource_type ?? '');
                $this->setInteractivityLevel($row->interactivity_level ?? '');
                $this->setSemanticDensity($row->semantic_density ?? '');
                //$this->setIntendedEndUserRole($row->intended_end_user_role ?? '');
                //$this->setContext($row->context ?? '');
                $this->setDifficulty($row->difficulty ?? '');
                $this->setTypicalLearningTime($row->typical_learning_time ?? '');
            }

            $this->readFirstLearningResourceType();
            $this->readFirstIntendedEndUserRole();
            $this->readFirstContext();
            return true;
        }
        return false;
    }

    public function toXML(ilXmlWriter $writer): void
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
    public static function _getId(int $a_rbac_id, int $a_obj_id): int
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

    public static function _getTypicalLearningTimeSeconds(int $a_rbac_id, int $a_obj_id = 0): int
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
            if (!count($time_arr)) {
                return 0;
            }
            return 60 * 60 * 24 * 30 * $time_arr[0] +
                60 * 60 * 24 * $time_arr[1] +
                60 * 60 * $time_arr[2] +
                60 * $time_arr[3] +
                $time_arr[4];
        }
        return 0;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateLearningResourceType(): void
    {
        $learning_resource_type = (string) array_search(
            $this->getLearningResourceType(),
            self::LEARNING_RESOURCE_TYPE_TRANSLATION
        );

        $this->learning_resource_type_id = $this->createOrUpdateInNewTable(
            'il_meta_lr_type',
            'meta_lr_type_id',
            $this->getLearningResourceTypeId(),
            'learning_resource_type',
            $learning_resource_type
        );
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateIntendedEndUserRole(): void
    {
        $intended_end_user_role = (string) array_search(
            $this->getIntendedEndUserRole(),
            self::INTENDED_END_USER_ROLE_TRANSLATION
        );

        $this->intended_end_user_role_id = $this->createOrUpdateInNewTable(
            'il_meta_end_usr_role',
            'meta_end_usr_role_id',
            $this->getIntendedEndUserRoleId(),
            'intended_end_user_role',
            $intended_end_user_role
        );
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateContext(): void
    {
        $context = (string) array_search(
            $this->getContext(),
            self::CONTEXT_TRANSLATION
        );

        $this->context_id = $this->createOrUpdateInNewTable(
            'il_meta_context',
            'meta_context_id',
            $this->getContextId(),
            'context',
            $context
        );
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function createOrUpdateInNewTable(
        string $table,
        string $id_field,
        int $id,
        string $data_field,
        string $data_value
    ): int {
        if ($data_value === '') {
            return 0;
        }

        if (!$id) {
            $this->db->insert(
                $table,
                [
                    $id_field => ['integer', $next_id = $this->db->nextId($table)],
                    'rbac_id' => ['integer', $this->getRBACId()],
                    'obj_id' => ['integer', $this->getObjId()],
                    'obj_type' => ['text', $this->getObjType()],
                    'parent_type' => ['text', 'meta_educational'],
                    'parent_id' => ['integer', $this->getMetaId()],
                    $data_field => ['text', $data_value]
                ]
            );
            return $next_id;
        }

        $this->db->update(
            $table,
            [$data_field => ['text', $data_value]],
            [$id_field => ['integer', $id]]
        );
        return $id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readFirstLearningResourceType(): void
    {
        $query = "SELECT * FROM il_meta_lr_type WHERE meta_lr_type_id = " .
            $this->db->quote($this->getLearningResourceTypeId(), 'integer');

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            if (key_exists($row['learning_resource_type'], self::LEARNING_RESOURCE_TYPE_TRANSLATION)) {
                $row['learning_resource_type'] = self::LEARNING_RESOURCE_TYPE_TRANSLATION[$row['learning_resource_type']];
            }
            $this->setLearningResourceType((string) $row['learning_resource_type']);
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readFirstIntendedEndUserRole(): void
    {
        $query = "SELECT * FROM il_meta_end_usr_role WHERE meta_end_usr_role_id = " .
            $this->db->quote($this->getIntendedEndUserRoleId(), 'integer');

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            if (key_exists($row['intended_end_user_role'], self::INTENDED_END_USER_ROLE_TRANSLATION)) {
                $row['intended_end_user_role'] = self::INTENDED_END_USER_ROLE_TRANSLATION[$row['intended_end_user_role']];
            }
            $this->setIntendedEndUserRole((string) $row['intended_end_user_role']);
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readFirstContext(): void
    {
        $query = "SELECT * FROM il_meta_context WHERE meta_context_id = " .
            $this->db->quote($this->getContextId(), 'integer');

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            if (key_exists($row['context'], self::CONTEXT_TRANSLATION)) {
                $row['context'] = self::CONTEXT_TRANSLATION[$row['context']];
            }
            $this->setContext((string) $row['context']);
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function deleteAllLearningResourceTypes(): void
    {
        $query = "DELETE FROM il_meta_lr_type WHERE parent_type = 'meta_educational'
                AND parent_id = " . $this->db->quote($this->getMetaId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function deleteAllIntendedEndUserRoles(): void
    {
        $query = "DELETE FROM il_meta_end_usr_role WHERE parent_type = 'meta_educational'
                AND parent_id = " . $this->db->quote($this->getMetaId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function deleteAllContexts(): void
    {
        $query = "DELETE FROM il_meta_context WHERE parent_type = 'meta_educational'
                AND parent_id = " . $this->db->quote($this->getMetaId(), 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function getLearningResourceTypeId(): int
    {
        return $this->learning_resource_type_id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function getIntendedEndUserRoleId(): int
    {
        return $this->intended_end_user_role_id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function getContextId(): int
    {
        return $this->context_id;
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readLearningResourceTypeId(int $parent_id): void
    {
        $query = "SELECT meta_lr_type_id FROM il_meta_lr_type WHERE parent_type = 'meta_educational'
                AND parent_id = " . $this->db->quote($parent_id, 'integer') .
            " ORDER BY meta_lr_type_id";

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            $this->learning_resource_type_id = (int) $row['meta_lr_type_id'];
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readIntendedEndUserRoleId(int $parent_id): void
    {
        $query = "SELECT meta_end_usr_role_id FROM il_meta_end_usr_role WHERE parent_type = 'meta_educational'
                AND parent_id = " . $this->db->quote($parent_id, 'integer') .
            " ORDER BY meta_end_usr_role_id";

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            $this->intended_end_user_role_id = (int) $row['meta_end_usr_role_id'];
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    protected function readContextId(int $parent_id): void
    {
        $query = "SELECT meta_context_id FROM il_meta_context WHERE parent_type = 'meta_educational'
                AND parent_id = " . $this->db->quote($parent_id, 'integer') .
            " ORDER BY meta_context_id";

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            $this->context_id = (int) $row['meta_context_id'];
        }
    }

    /**
     * Compatibility fix for legacy MD classes for new db tables
     */
    public function setMetaId(int $a_meta_id, bool $a_read_data = true): void
    {
        $this->readLearningResourceTypeId($a_meta_id);
        $this->readIntendedEndUserRoleId($a_meta_id);
        $this->readContextId($a_meta_id);
        parent::setMetaId($a_meta_id, $a_read_data);
    }
}
