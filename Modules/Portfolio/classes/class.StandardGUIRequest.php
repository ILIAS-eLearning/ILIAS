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

namespace ILIAS\Portfolio;

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

    public function getPortfolioId(): int
    {
        $prt_id = $this->int("prt_id");
        if ($prt_id === 0) {
            $prt_id = $this->int("prtf");
        }
        return $prt_id;
    }

    public function getPortfolioIds(): array
    {
        $ids = $this->intArray("prtfs");
        if ((count($ids) === 0) && $this->int("prtf") > 0) {
            $ids = [$this->int("prtf")];
        }
        return $ids;
    }

    public function getBaseClass(): string
    {
        return $this->str("baseClass");
    }

    public function getNewType(): string
    {
        return $this->str("new_type");
    }

    public function getCopyFormProcess(): bool
    {
        return (bool) $this->int("cpfl");
    }

    public function getExcBackRefId(): int
    {
        return $this->int("exc_back_ref_id");
    }

    public function getExcAssId(): int
    {
        $ass_id = $this->int("ass");
        if ($ass_id === 0) {
            $ass_id = $this->int("ass_id");
        }
        return $ass_id;
    }

    public function getExcFile(): string
    {
        return trim($this->str("file"));
    }

    public function getBackUrl(): string
    {
        return trim($this->str("back_url"));
    }

    public function getPortfolioPageId(): int
    {
        return $this->int("ppage");
    }

    public function getUserPage(): int
    {
        return $this->int("user_page");
    }

    /** @return int[] */
    public function getPortfolioPageIds(): array
    {
        $pages = $this->intArray("prtf_pages");
        if ((count($pages) === 0) && $this->int("prtf_page") > 0) {
            $pages = [$this->int("prtf_page")];
        }
        return $pages;
    }

    public function getConsultationHourUserId(): int
    {
        return $this->int("chuid");
    }

    public function getCalendarSeed(): string
    {
        return $this->str("seed");
    }

    public function getVerificationId(): int
    {
        return $this->int("dlid");
    }

    /** @return string[] */
    public function getRoleTemplateIds(): array
    {
        return $this->strArray("role_template_ids");
    }

    /** @return string[] */
    public function getObjIds(): array
    {
        return $this->strArray("obj_id");
    }

    /** @return int[] */
    public function getOrder(): array
    {
        return $this->intArray("order");
    }

    /** @return string[] */
    public function getTitles(): array
    {
        return $this->strArray("title");
    }

    public function getStyleId(): int
    {
        return $this->int("style_id");
    }

    public function getPageType(): string
    {
        return $this->str("ptype");
    }

    public function getPageTitle(): string
    {
        return $this->str("fpage");
    }

    public function getBlogTitle(): string
    {
        return $this->str("blog");
    }

    public function getPortfolioTitle(): string
    {
        return trim($this->str("pt"));
    }

    public function getTemplateId(): int
    {
        return $this->int("tmpl");
    }

    /** @return string[] */
    public function getOnline(): array
    {
        return $this->strArray("online");
    }

    public function getCourseSorting(): string
    {
        return $this->str("srt");
    }

    public function getPrintSelectedType(): string
    {
        return $this->str("sel_type");
    }

    public function getPortfolioTemplateId(): int
    {
        return $this->int("prtt_pre");
    }

    public function getPortfolioTemplate(): int
    {
        return $this->int("prtt");
    }

    public function getExerciseRefId(): int
    {
        return $this->int("exc_id");
    }
}
