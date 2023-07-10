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
 ********************************************************************
 */

use ILIAS\UI\Component\Input\Container\Filter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Request adapter for filter
 *
 * @author killing@leifos.de
 * @ingroup ServicesUI
 */
class ilUIFilterRequestAdapter
{
    public const CMD_PARAMETER = "cmdFilter";
    public const RENDER_INPUT_BASE = "__filter_status_";

    protected ServerRequestInterface $request;
    protected array $params;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->params = $this->request->getQueryParams();
    }

    public function getFilterCmd(): string
    {
        if (isset($this->params[self::CMD_PARAMETER])) {
            return (string) $this->params[self::CMD_PARAMETER];
        }

        return "";
    }

    /**
     * Has an input field been rendered in current request?
     */
    public function isInputRendered(string $input_id): bool
    {
        if (isset($this->params[self::RENDER_INPUT_BASE . $input_id]) &&
            $this->params[self::RENDER_INPUT_BASE . $input_id] === "1") {
            return true;
        }

        return false;
    }

    public function getFilterWithRequest(Filter\Standard $filter): Filter\Standard
    {
        return $filter->withRequest($this->request);
    }

    /**
     * Get action for filter command
     */
    public function getAction(string $base_action, string $filter_cmd, bool $non_asynch = false): string
    {
        if ($non_asynch) {
            $base_action = str_replace("cmdMode=asynch", "", $base_action);
        }

        return $base_action . "&" . self::CMD_PARAMETER . "=" . $filter_cmd;
    }
}
