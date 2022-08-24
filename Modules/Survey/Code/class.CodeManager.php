<?php

declare(strict_types=1);

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
    protected CodeDBRepo $code_repo;
    protected InternalDataService $data;
    protected int $survey_id;
    protected AccessManager $access;
    protected \ilLanguage $lng;

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
     * @throws \ilPermissionException
     */
    protected function checkPermission(): void
    {
        if (!$this->access->canManageCodes()) {
            throw new \ilPermissionException($this->lng->txt("permission_denied"));
        }
    }

    /**
     * Delete all codes of survey
     * @throws \ilPermissionException
     */
    public function deleteAll(): void
    {
        $this->checkPermission();
        $repo = $this->code_repo;
        $repo->deleteAll($this->survey_id);
    }

    /**
     * Delete single code
     * @throws \ilPermissionException
     */
    public function delete(string $code): void
    {
        $this->checkPermission();
        $repo = $this->code_repo;
        $repo->delete($this->survey_id, $code);
    }

    /**
     * Does code exist in survey?
     */
    public function exists(string $code): bool
    {
        $repo = $this->code_repo;
        return $repo->exists($this->survey_id, $code);
    }

    /**
     * Saves a survey access code for a registered user to the database
     * @throws \ilSurveyException
     */
    public function add(
        Code $code
    ): int {
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
     * @param int $nr number of codes that should be generated/added
     * @return int[]
     * @throws \ilSurveyException
     * @throws \ilPermissionException
     */
    public function addCodes(int $nr): array
    {
        $this->checkPermission();
        return $this->code_repo->addCodes($this->survey_id, $nr);
    }

    /**
     * Update external data of a code
     * @throws \ilPermissionException
     */
    public function updateExternalData(
        int $code_id,
        string $email,
        string $last_name,
        string $first_name,
        int $sent
    ): bool {
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
     * Get all access keys of a survey
     * @return string[]
     * @throws \ilPermissionException
     */
    public function getAll(): array
    {
        $this->checkPermission();
        return $this->code_repo->getAll($this->survey_id);
    }

    /**
     * Get all codes of a survey
     * @return Code[]
     * @throws \ilPermissionException
     */
    public function getAllData(): array
    {
        $this->checkPermission();
        return $this->code_repo->getAllData($this->survey_id);
    }

    /**
     * Bind registered user to a code
     * @throws \ilPermissionException
     */
    public function bindUser(
        string $code,
        int $user_id
    ): void {
        if ($user_id === ANONYMOUS_USER_ID) {
            return;
        }
        $this->code_repo->bindUser($this->survey_id, $code, $user_id);
    }

    /**
     * Get access key for a registered user
     */
    public function getByUserId(
        int $user_id
    ): string {
        return $this->code_repo->getByUserId($this->survey_id, $user_id);
    }

    /**
     * Get code object for an access key
     */
    public function getByUserKey(string $user_key): ?Code
    {
        return $this->code_repo->getByUserKey($this->survey_id, $user_key);
    }
}
