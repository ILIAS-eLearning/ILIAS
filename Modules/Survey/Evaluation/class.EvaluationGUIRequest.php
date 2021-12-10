<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Survey\Evaluation;

/**
 * Exercise gui request wrapper. This class processes all
 * request parameters which are not handled by form classes already.
 * POST overwrites GET with the same name.
 * POST/GET parameters may be passed to the class for testing purposes.
 * @author Alexander Killing <killing@leifos.de>
 */
class EvaluationGUIRequest
{
    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    /**
     * @var array
     */
    protected $params;

    /**
     * @param \ILIAS\DI\HTTPServices    $http
     */
    public function __construct(
        \ILIAS\DI\HTTPServices $http
    ) {
        $this->http = $http;
        $this->params = array_merge(
            $http->request()->getQueryParams(),
            $http->request()->getParsedBody()
        );
    }

    public function getShowTable() : bool
    {
        return !isset($this->params["vw"]) || is_int(strpos($this->params["vw"], "t"));
    }

    public function getShowChart() : bool
    {
        return !isset($this->params["vw"]) || is_int(strpos($this->params["vw"], "c"));
    }

    public function getShowAbsolute() : bool
    {
        return !isset($this->params["cp"]) || is_int(strpos($this->params["cp"], "a"));
    }

    public function getShowPercentage() : bool
    {
        return !isset($this->params["cp"]) || is_int(strpos($this->params["cp"], "p"));
    }

    public function getAppraiseeId() : int
    {
        return (int) ($this->params["appr_id"] ?? 0);
    }

    public function getRaterId() : string
    {
        return (string) ($this->params["rater_id"] ?? 0);
    }
}
