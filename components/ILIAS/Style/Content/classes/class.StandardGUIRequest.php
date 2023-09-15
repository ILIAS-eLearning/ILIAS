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

namespace ILIAS\Style\Content;

use ILIAS\Repository;
use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;

class StandardGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        Services $http,
        Factory $refinery,
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

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getObjId(): int
    {
        return $this->int("obj_id");
    }

    public function getId(): int
    {
        return $this->int("id");
    }

    public function getIds(): array
    {
        return $this->intArray("id");
    }

    public function getToStyleId(): int
    {
        return $this->int("to_style");
    }

    public function getFromStyleId(): int
    {
        return $this->int("from_style");
    }

    public function getCatId(): int
    {
        return $this->int("cat");
    }

    public function getStyleType(): string
    {
        return $this->str("style_type");
    }

    public function getTempType(): string
    {
        return $this->str("temp_type");
    }

    public function getAdminMode(): string
    {
        return $this->str("admin_mode");
    }

    public function getColorName(): string
    {
        return $this->str("c_name");
    }

    public function getMediaQueryId(): int
    {
        return $this->int("mq_id");
    }

    public function getMediaQueryIds(): array
    {
        return $this->intArray("mq_id");
    }

    public function getTemplateId(): int
    {
        return $this->int("t_id");
    }

    public function getTemplateIds(): array
    {
        return $this->intArray("tid");
    }

    public function getFile(): string
    {
        return $this->str("file");
    }

    public function getFiles(): array
    {
        $files = $this->strArray("file");
        if (count($files) == 0) {
            if ($this->str("file") != "") {
                $files[] = $this->str("file");
            }
        }
        return $files;
    }

    public function getCharacteristics(): array
    {
        return $this->strArray("char");
    }

    public function getCharacteristic(): string
    {
        return $this->str("char");
    }

    public function getTag(): string
    {
        return $this->str("tag");
    }

    public function getAllCharacteristics(): array
    {
        return $this->strArray("all_chars");
    }

    public function getHidden(): array
    {
        return $this->strArray("hide");
    }

    public function getOrder(): array
    {
        return $this->strArray("order");
    }

    public function getTitles(): array
    {
        return $this->strArray("title");
    }

    public function getConflictAction(): array
    {
        return $this->strArray("conflict_action");
    }

    public function getSelectedStandard($style_id): int
    {
        return $this->int("std_" . $style_id);
    }

    public function getColors(): array
    {
        return $this->strArray("color");
    }
}
