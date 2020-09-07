<?php

declare(strict_types=1);

use ILIAS\KioskMode\URLBuilder;

/**
 * Class LSUrlBuilder
 */
class LSUrlBuilder implements URLBuilder
{
    const PARAM_LSO_COMMAND = 'lsocmd';
    const PARAM_LSO_PARAMETER = 'lsov';

    public function __construct(ILIAS\Data\URI $base_url)
    {
        $this->base_url = $base_url;
    }

    public function getURL(string $command, int $param = null) : ILIAS\Data\URI
    {
        $query = $this->base_url->query();
        if (!$query) {
            $params = [];
        } else {
            parse_str($this->base_url->query(), $params);
        }

        $params[self::PARAM_LSO_COMMAND] = $command;
        if (is_null($param)) {
            unset($params[self::PARAM_LSO_PARAMETER]);
        } else {
            $params[self::PARAM_LSO_PARAMETER] = $param;
        }
        $url = $this->base_url->withQuery(http_build_query($params));
        return $url;
    }

    public function getHref(string $command, int $param = null) : string
    {
        $url = $this->getURL($command, $param);
        return $url->baseURI() . '?' . $url->query();
    }
}
