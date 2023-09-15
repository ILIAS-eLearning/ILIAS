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

use ILIAS\KioskMode\URLBuilder;

class LSUrlBuilder implements URLBuilder
{
    public const PARAM_LSO_COMMAND = 'lsocmd';
    public const PARAM_LSO_PARAMETER = 'lsov';

    protected ILIAS\Data\URI $base_url;

    public function __construct(ILIAS\Data\URI $base_url)
    {
        $this->base_url = $base_url;
    }

    public function getURL(string $command, int $param = null): ILIAS\Data\URI
    {
        $query = $this->base_url->getQuery();
        if (!$query) {
            $params = [];
        } else {
            parse_str($this->base_url->getQuery(), $params);
        }

        $params[self::PARAM_LSO_COMMAND] = $command;
        if (is_null($param)) {
            unset($params[self::PARAM_LSO_PARAMETER]);
        } else {
            $params[self::PARAM_LSO_PARAMETER] = $param;
        }
        return $this->base_url->withQuery(http_build_query($params));
    }

    public function getHref(string $command, int $param = null): string
    {
        $url = $this->getURL($command, $param);
        return $url->getBaseURI() . '?' . $url->getQuery();
    }
}
