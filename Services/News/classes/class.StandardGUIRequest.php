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

namespace ILIAS\News;

use ILIAS\Repository;

class StandardGUIRequest
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

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getNewsId() : int
    {
        $id = $this->int("news_id");
        if ($id === 0) {
            $id = $this->int("id");
        }
        return $id;
    }

    public function getNewsRefId() : int
    {
        return $this->int("news_ref_id");
    }

    public function getNewsContext() : string
    {
        return $this->str("news_context");
    }

    public function getDeleteMedia() : int
    {
        return $this->int("media_delete");
    }

    public function getRenderedNews() : array
    {
        return $this->intArray("rendered_news");
    }

    public function getNewsAction() : string
    {
        return $this->str("news_action");
    }

    public function getId() : int
    {
        return $this->int("id");
    }

    public function getCmd() : string
    {
        return $this->str("cmd");
    }

    public function getDashboardPeriod() : string
    {
        return $this->str("news_pd_periods");
    }

    public function getNewsPer() : string
    {
        return $this->str("news_per");
    }

    public function getNewsIds() : array
    {
        return $this->intArray("news_id");
    }
}
