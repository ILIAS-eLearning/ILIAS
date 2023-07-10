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

namespace ILIAS\LearningModule\Presentation;

use ILIAS\Repository;

class PresentationGUIRequest
{
    use Repository\BaseGUIRequest;

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

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getObjId(): int
    {
        return $this->int("obj_id");
    }

    public function getObjType(): string
    {
        return $this->str("obj_type");
    }

    public function getTranslation(): string
    {
        return $this->str("transl");
    }

    public function getFocusId(): int
    {
        return $this->int("focus_id");
    }

    public function getFocusReturn(): int
    {
        return $this->int("focus_return");
    }

    public function getBackPage(): string
    {
        return $this->str("back_pg");
    }

    public function getSearchString(): string
    {
        return $this->str("srcstring");
    }

    public function getFrame(): string
    {
        return $this->str("frame");
    }

    public function getFromPage(): string
    {
        return $this->str("from_page");
    }

    public function getMobId(): int
    {
        return $this->int("mob_id");
    }

    public function getEmbedMode(): int
    {
        return $this->int("embed_mode");
    }

    public function getCmd(): string
    {
        if (!$this->isArray("cmd")) {
            return $this->str("cmd");
        }
        return "";
    }

    public function getPgId(): int
    {
        return $this->int("pg_id");
    }

    public function getPgType(): string
    {
        return $this->str("pg_type");
    }

    public function getNotificationSwitch(): int
    {
        return $this->int("ntf");
    }

    public function getType(): string
    {
        return $this->str("type");
    }

    public function getUrl(): string
    {
        return $this->str("url");
    }

    public function getRating(): int
    {
        return $this->int("rating");
    }

    public function getItems(): array
    {
        return $this->strArray("item");
    }

    public function getSelectedType(): string
    {
        return $this->str("sel_type");
    }

    public function getSelectedObjIds(): array
    {
        return $this->intArray("obj_id");
    }

    public function getQuestionPageId(): int
    {
        return $this->int("page_id");
    }

    public function getQuestionId(): int
    {
        return $this->int("id");
    }
}
