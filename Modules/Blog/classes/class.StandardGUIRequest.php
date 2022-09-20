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

namespace ILIAS\Blog;

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

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getGotoPage(): int
    {
        return $this->int("gtp");
    }

    public function getEditing(): string
    {
        return $this->str("edt");
    }

    public function getBlogPage(): int
    {
        return $this->int("blpg");
    }

    public function getOldNr(): int
    {
        return $this->int("old_nr");
    }

    public function getPPage(): int
    {
        return $this->int("ppage");
    }

    public function getUserPage(): int
    {
        return $this->int("user_page");
    }

    public function getNewType(): string
    {
        return $this->str("new_type");
    }

    public function getPreviewMode(): string
    {
        return $this->str("prvm");
    }

    public function getNotification(): int
    {
        return $this->int("ntf");
    }

    public function getApId(): int
    {
        return $this->int("apid");
    }

    public function getMonth(): string
    {
        return $this->str("bmn");
    }

    public function getKeyword(): string
    {
        return $this->str("kwd");
    }

    public function getAuthor(): int
    {
        return $this->int("ath");
    }

    public function getPrtId(): int
    {
        return $this->int("prt_id");
    }

    public function getAssId(): int
    {
        return $this->int("ass");
    }

    public function getAssFile(): string
    {
        return trim($this->str("file"));
    }

    public function getFetchAll(): bool
    {
        return (bool) $this->int("fetchall");
    }

    public function getTerm(): string
    {
        return trim($this->str("term"));
    }

    public function getTitle(): string
    {
        return trim($this->str("title"));
    }

    public function getFormat(): string
    {
        return trim($this->str("format"));
    }

    public function getUserLogin(): string
    {
        return trim($this->str("user_login"));
    }

    public function getUserType(): string
    {
        return trim($this->str("user_type"));
    }

    /** @return int[] */
    public function getIds(): array
    {
        return $this->intArray("id");
    }

    public function getStyleId(): int
    {
        return $this->int("style_id");
    }

    /** @return int[] */
    public function getObjIds(): array
    {
        return $this->intArray("obj_id");
    }
}
