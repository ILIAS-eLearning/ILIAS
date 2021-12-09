<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Code;

use ILIAS\Survey\InternalDataService;
use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\Access\AccessManager;

/**
 * Code manager
 * @author Alexander Killing <killing@leifos.de>
 */
class CodeManager
{
    /**
     * @var CodeDBRepo
     */
    protected $code_repo;

    /**
     * @var InternalDataService
     */
    protected $data;

    /**
     * @var int
     */
    protected $survey_id;

    /**
     * @var AccessManager
     */
    protected $access;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct(
        CodeDBRepo $code_repo,
        InternalDataService $data,
        \ilObjSurvey $survey,
        InternalDomainService $domain_service,
        int $user_id
    ) {
        $this->data = $data;
        $this->code_repo = $code_repo;
        $this->survey_id = $survey->getSurveyId();
        $this->access = $domain_service->access(
            $survey->getRefId(),
            $user_id
        );
        $this->lng = $domain_service->lng();
    }

    /**
     * Check permission
     * @throws \ilObjectException
     */
    protected function checkPermission()
    {
        if (!$this->access->canManageCodes()) {
            throw new \ilObjectException($this->lng->txt("permission_denied"));
        }
    }

    /**
     * Delete all codes of survey
     */
    public function deleteAll() : void
    {
        $this->checkPermission();
        $repo = $this->code_repo;
        $repo->deleteAll($this->survey_id);
    }

    /**
     * Delete code
     * @param string $code
     */
    public function delete(string $code) : void
    {
        $this->checkPermission();
        $repo = $this->code_repo;
        $repo->delete($this->survey_id, $code);
    }

    /**
     * Does code exist in survey?
     * @param string $code
     * @return bool
     */
    public function exists(string $code) : bool
    {
        $repo = $this->code_repo;
        return $repo->exists($this->survey_id, $code);
    }

    /**
     * Saves a survey access code for a registered user to the database
     * @param Code $code
     * @return int
     * @throws \ilObjectException
     * @throws \ilSurveyException
     */
    public function add(
        Code $code
    ) : int {
        //$this->checkPermission();
        $repo = $this->code_repo;
        return $repo->add(
            $this->survey_id,
            $code->getCode(),
            $code->getUserId(),
            $code->getEmail(),
            $code->getLastName(),
            $code->getFirstName(),
            $code->getSent(),
            $code->getTimestamp()
        );
    }

    /**
     * Add multiple new codes
     * @param int $nr
     * @return int[]
     * @throws \ilSurveyException
     */
    public function addCodes(int $nr) : array
    {
        $this->checkPermission();
        return $this->code_repo->addCodes($this->survey_id, $nr);
    }

    /**
     * @param int    $code_id
     * @param string $email
     * @param string $last_name
     * @param string $first_name
     * @param int    $sent
     * @return bool
     */
    public function updateExternalData(
        int $code_id,
        string $email,
        string $last_name,
        string $first_name,
        int $sent
    ) : bool {
        $this->checkPermission();
        return $this->code_repo->updateExternalData(
            $code_id,
            $email,
            $last_name,
            $first_name,
            $sent
        );
    }

    /**
     * Get all codes of a survey
     * @return string[]
     */
    public function getAll() : array
    {
        $this->checkPermission();
        return $this->code_repo->getAll($this->survey_id);
    }

    /**
     * Get all codes of a survey
     * @return Code[]
     */
    public function getAllData() : array
    {
        $this->checkPermission();
        return $this->code_repo->getAllData($this->survey_id);
    }

    /**
     * Bind registered user to a code
     * @param string $code
     * @param int    $user_id
     */
    public function bindUser(string $code, int $user_id) : void
    {
        $this->checkPermission();

        if ($user_id == ANONYMOUS_USER_ID) {
            return;
        }

        $this->code_repo->bindUser($this->survey_id, $code, $user_id);
    }

    /**
     * Get code for a registered user
     * @param int $user_id
     * @return string
     */
    public function getByUserId(int $user_id) : string
    {
        //$this->checkPermission();
        return $this->code_repo->getByUserId($this->survey_id, $user_id);
    }
}
