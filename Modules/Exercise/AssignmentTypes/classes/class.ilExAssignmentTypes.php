<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Assignment types. Gives information on available types and acts as factory
 * to get assignment type objects.
 *
 * @author killing@leifos.de
 * @ingroup ModulesExercise
 */
class ilExAssignmentTypes
{
    const STR_IDENTIFIER_PORTFOLIO = "prtf";

    /**
     * @var ilExerciseInternalService
     */
    protected $service;


    /**
     * Constructor
     */
    protected function __construct(ilExerciseInternalService $service = null)
    {
        global $DIC;

        $this->service = ($service == null)
            ? $DIC->exercise()->internal()->service()
            : $service;
    }

    /**
     * Get instance
     *
     * @return ilExAssignmentTypes
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Get all ids
     *
     * @param
     * @return
     */
    public function getAllIds()
    {
        return [
            ilExAssignment::TYPE_UPLOAD,
            ilExAssignment::TYPE_UPLOAD_TEAM,
            ilExAssignment::TYPE_TEXT,
            ilExAssignment::TYPE_BLOG,
            ilExAssignment::TYPE_PORTFOLIO,
            ilExAssignment::TYPE_WIKI_TEAM
        ];
    }

    /**
     * Is valid id
     *
     * @param int $a_id
     * @return bool
     */
    public function isValidId($a_id)
    {
        return in_array($a_id, $this->getAllIds());
    }



    /**
     * Get all
     *
     * @param
     * @return
     */
    public function getAll()
    {
        return array_column(
            array_map(
                function ($id) {
                    return [$id, $this->getById($id)];
                },
                $this->getAllIds()
            ),
            1,
            0
        );
    }
    
    /**
     * Get all activated
     *
     * @param
     * @return
     */
    public function getAllActivated()
    {
        return array_filter($this->getAll(), function (ilExAssignmentTypeInterface $at) {
            return $at->isActive();
        });
    }

    /**
     * Get all allowed types for an exercise for an exercise
     *
     * @param ilObjExercise $exc
     * @return array
     */
    public function getAllAllowed(ilObjExercise $exc)
    {
        $random_manager = $this->service->getRandomAssignmentManager($exc);
        $active = $this->getAllActivated();

        // no team assignments, if random mandatory assignments is activated
        if ($random_manager->isActivated()) {
            $active = array_filter($active, function (ilExAssignmentTypeInterface $at) {
                return !$at->usesTeams();
            });
        }
        return $active;
    }

    /**
     * Get type object by id
     *
     * Centralized ID management is still an issue to be tackled in the future and caused
     * by initial consts definition.
     *
     * @param int $a_id type id
     * @return ilExAssignmentTypeInterface
     */
    public function getById(int $a_id) : ilExAssignmentTypeInterface
    {
        switch ($a_id) {
            case ilExAssignment::TYPE_UPLOAD:
                return new ilExAssTypeUpload();
                break;

            case ilExAssignment::TYPE_BLOG:
                return new ilExAssTypeBlog();
                break;

            case ilExAssignment::TYPE_PORTFOLIO:
                return new ilExAssTypePortfolio();
                break;

            case ilExAssignment::TYPE_UPLOAD_TEAM:
                return new ilExAssTypeUploadTeam();
                break;

            case ilExAssignment::TYPE_TEXT:
                return new ilExAssTypeText();
                break;

            case ilExAssignment::TYPE_WIKI_TEAM:
                return new ilExAssTypeWikiTeam();
                break;
        }

        throw new ilExcUnknownAssignmentTypeException("Unknown Assignment Type ($a_id).");
    }

    /**
     * Get assignment type IDs for given submission type
     *
     * @param int $a_submission_type
     * @return array
     */
    public function getIdsForSubmissionType($a_submission_type)
    {
        $ids = [];
        foreach ($this->getAllIds() as $id) {
            if ($this->getById($id)->getSubmissionType() == $a_submission_type) {
                $ids[] = $id;
            }
        }
        return $ids;
    }
}
