<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Assignment types gui.
 *
 * @author killing@leifos.de
 * @ingroup ModulesExercise
 */
class ilExAssignmentTypesGUI
{
    protected $class_names = array(
        ilExAssignment::TYPE_UPLOAD => "ilExAssTypeUploadGUI",
        ilExAssignment::TYPE_BLOG => "ilExAssTypeBlogGUI",
        ilExAssignment::TYPE_PORTFOLIO => "ilExAssTypePortfolioGUI",
        ilExAssignment::TYPE_UPLOAD_TEAM => "ilExAssTypeUploadTeamGUI",
        ilExAssignment::TYPE_TEXT => "ilExAssTypeTextGUI",
        ilExAssignment::TYPE_WIKI_TEAM => "ilExAssTypeWikiTeamGUI"
    );

    /**
     * Constructor
     */
    protected function __construct()
    {
    }

    /**
     * Get instance
     *
     * @return ilExAssignmentTypesGUI
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Get type gui object by id
     *
     * Centralized ID management is still an issue to be tackled in the future and caused
     * by initial consts definition.
     *
     * @param int $a_id type id
     * @return ilExAssignmentTypeGUIInterface
     */
    public function getById($a_id)
    {
        // @todo: check id

        switch ($a_id) {
            case ilExAssignment::TYPE_UPLOAD:
                include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssTypeUploadGUI.php");
                return new ilExAssTypeUploadGUI();
                break;

            case ilExAssignment::TYPE_BLOG:
                include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssTypeBlogGUI.php");
                return new ilExAssTypeBlogGUI();
                break;

            case ilExAssignment::TYPE_PORTFOLIO:
                include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssTypePortfolioGUI.php");
                return new ilExAssTypePortfolioGUI();
                break;

            case ilExAssignment::TYPE_UPLOAD_TEAM:
                include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssTypeUploadTeamGUI.php");
                return new ilExAssTypeUploadTeamGUI();
                break;

            case ilExAssignment::TYPE_TEXT:
                include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssTypeTextGUI.php");
                return new ilExAssTypeTextGUI();
                break;

            case ilExAssignment::TYPE_WIKI_TEAM:
                include_once("./Modules/Exercise/AssignmentTypes/GUI/classes/class.ilExAssTypeWikiTeamGUI.php");
                return new ilExAssTypeWikiTeamGUI();
                break;
        }

        // we should throw some exception here
    }

    /**
     * Get type gui object by classname
     *
     * @param
     * @return
     */
    public function getByClassName($a_class_name)
    {
        $id = $this->getIdForClassName($a_class_name);
        return $this->getById($id);
    }


    /**
     * Checks if a class name is a valid exercise assignment type GUI class
     * (case insensitive, since ilCtrl uses lower keys due to historic reasons)
     *
     * @param string
     * @return bool
     */
    public function isExAssTypeGUIClass($a_string)
    {
        foreach ($this->class_names as $cn) {
            if (strtolower($cn) == strtolower($a_string)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get type id for class name
     *
     * @param $a_string
     * @return null|int
     */
    public function getIdForClassName($a_string)
    {
        foreach ($this->class_names as $k => $cn) {
            if (strtolower($cn) == strtolower($a_string)) {
                return $k;
            }
        }
        return null;
    }
}
