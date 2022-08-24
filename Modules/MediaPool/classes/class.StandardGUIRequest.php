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

namespace ILIAS\MediaPool;

use ILIAS\Repository\BaseGUIRequest;

class StandardGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getNewType(): string
    {
        return $this->str("new_type");
    }

    public function getMode(): string
    {
        return $this->str("mep_mode");
    }

    public function getExportFormat(): string
    {
        return $this->str("format");
    }

    public function getUploadHash(): string
    {
        $hash = $this->str("mep_hash");
        if ($hash === "") {
            $hash = $this->str("ilfilehash");
        }
        return $hash;
    }

    public function getItemId(): int
    {
        return $this->int("mepitem_id");
    }

    /** @return int[] */
    public function getItemIds(): array
    {
        return $this->intArray("id");
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getOldNr(): int
    {
        return $this->int("old_nr");
    }

    public function getFolderEditMode(): bool
    {
        return (bool) $this->int("foldereditmode");
    }

    public function getForceFilter(): int
    {
        return $this->int("force_filter");
    }

    public function getFolderId($par): int
    {
        return $this->int($par);
    }

    public function getImportLang(): string
    {
        return $this->str("import_lang");
    }

    /** @return string[] */
    public function getFiles(): array
    {
        return $this->strArray("file");
    }

    public function getFileAction(): string
    {
        return $this->str("action");
    }
}
