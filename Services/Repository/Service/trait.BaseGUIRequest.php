<?php declare(strict_types = 1);

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

namespace ILIAS\Repository;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Base gui request wrapper. This class processes all
 * request parameters which are not handled by form classes already.
 * POST overwrites GET with the same name.
 * POST/GET parameters may be passed to the class for testing purposes.
 * @author Alexander Killing <killing@leifos.de>
 */
trait BaseGUIRequest
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;
    protected ?array $passed_query_params;
    protected ?array $passed_post_data;

    /**
     * Query params and post data parameters are used for testing. If none of these is
     * provided the usual http service wrapper is used to determine the request data.
     * @param HTTP\Services    $http
     * @param Refinery\Factory $refinery
     * @param array|null       $passed_query_params
     * @param array|null       $passed_post_data
     */
    protected function initRequest(
        HTTP\Services $http,
        Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
        $this->passed_post_data = $passed_post_data;
        $this->passed_query_params = $passed_query_params;
    }

    // get integer parameter kindly
    protected function int($key) : int
    {
        $t = $this->refinery->kindlyTo()->int();
        return (int) ($this->get($key, $t) ?? 0);
    }

    // get integer array kindly
    protected function intArray($key) : array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to int
                return array_column(
                    array_map(
                        function ($k, $v) {
                            return [$k, (int) $v];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    // get string parameter kindly
    protected function str($key) : string
    {
        $t = $this->refinery->kindlyTo()->string();
        return \ilUtil::stripSlashes((string) ($this->get($key, $t) ?? ""));
    }

    // get string array kindly
    protected function strArray($key) : array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to string
                return array_column(
                    array_map(
                        function ($k, $v) {
                            return [$k, \ilUtil::stripSlashes((string) $v)];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    // get string array kindly
    protected function arrayArray($key) : array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to string
                return array_column(
                    array_map(
                        function ($k, $v) {
                            return [$k, (array) $v];
                        },
                        array_keys($arr),
                        $arr
                    ),
                    1,
                    0
                );
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    /**
     * Check if parameter is an array
     */
    protected function isArray(string $key) : bool
    {
        if ($this->passed_query_params === null && $this->passed_post_data === null) {
            $no_transform = $this->refinery->custom()->transformation(function ($v) {
                return $v;
            });
            $w = $this->http->wrapper();
            if ($w->post()->has($key)) {
                return is_array($w->post()->retrieve($key, $no_transform));
            }
            if ($w->query()->has($key)) {
                return is_array($w->query()->retrieve($key, $no_transform));
            }
        }
        if (isset($this->passed_post_data[$key])) {
            return is_array($this->passed_post_data[$key]);
        }
        if (isset($this->passed_query_params[$key])) {
            return is_array($this->passed_query_params[$key]);
        }
        return false;
    }

    /**
     * @return mixed|null
     */
    protected function raw($key)
    {
        $no_transform = $this->refinery->custom()->transformation(function ($v) {
            return $v;
        });
        return $this->get($key, $no_transform);
    }



    /**
     * Get passed parameter, if not data passed, get key from http request
     * @param string                  $key
     * @param Refinery\Transformation $t
     * @return mixed|null
     */
    protected function get(string $key, Refinery\Transformation $t)
    {
        if ($this->passed_query_params === null && $this->passed_post_data === null) {
            $w = $this->http->wrapper();
            if ($w->post()->has($key)) {
                return $w->post()->retrieve($key, $t);
            }
            if ($w->query()->has($key)) {
                return $w->query()->retrieve($key, $t);
            }
        }
        if (isset($this->passed_post_data[$key])) {
            return $t->transform($this->passed_post_data[$key]);
        }
        if (isset($this->passed_query_params[$key])) {
            return $t->transform($this->passed_query_params[$key]);
        }
        return null;
    }
}
