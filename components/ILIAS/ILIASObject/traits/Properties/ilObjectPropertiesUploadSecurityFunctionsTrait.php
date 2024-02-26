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

use ILIAS\FileUpload\Handler\BasicHandlerResult;

trait ilObjectPropertiesUploadSecurityFunctionsTrait
{
    protected function getAccess(
        int $ref_id,
        ilAccessHandler $access
    ): bool {
        if ($ref_id !== null
            && $access->checkAccess('write', '', $ref_id)) {
            return true;
        }

        return false;
    }

    protected function getAccessFailureResult(
        string $file_identification_parameter_name,
        string $file_name,
        ilLanguage $language
    ): HandlerResult {
        $language->loadLanguageModule('content');
        return new BasicHandlerResult(
            $file_identification_parameter_name,
            HandlerResult::STATUS_FAILED,
            basename($file_name),
            $language->txt('cont_no_access')
        );
    }
}
