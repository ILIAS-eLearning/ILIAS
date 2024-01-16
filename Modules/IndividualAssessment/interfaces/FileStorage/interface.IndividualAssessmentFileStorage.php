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

use ILIAS\FileUpload\DTO\UploadResult;

/**
 * @deprecated in favor of ResourceStorage. This class is only used for migration.
 */
interface IndividualAssessmentFileStorage
{
    public function deleteAllFilesBut(string $file): void;
    public function uploadFile(UploadResult $file): string;
    public function create(): void;
    public function setUserId(int $user_id): void;
}
