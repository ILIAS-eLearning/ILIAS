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

namespace ILIAS\Skill\Service;

use ILIAS\HTTP;
use ILIAS\Refinery;

/**
 * Skill gui global request wrapper.
 * POST overwrites GET with the same name.
 * POST/GET parameters may be passed to the class for testing purposes.
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillGUIRequest
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;
    protected ?array $passed_query_params = null;
    protected ?array $passed_post_data = null;

    /**
     * Query params and post data parameters are used for testing. If none of these is
     * provided the usual http service wrapper is used to determine the request data.
     */
    public function __construct(
        HTTP\Services $http,
        Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
        $this->passed_query_params = $passed_query_params;
        $this->passed_post_data = $passed_post_data;
    }

    /**
     * get integer parameter kindly
     */
    protected function int(string $key): int
    {
        $t = $this->refinery->kindlyTo()->int();
        return (int) ($this->get($key, $t) ?? 0);
    }

    /**
     * get integer array kindly
     * @return int[]|array<int|string, int>
     */
    protected function intArray(string $key): array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            static function (array $arr): array {
                // keep keys(!), transform all values to int
                return array_map('intval', $arr);
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    /**
     * get string parameter kindly
     */
    protected function str(string $key): string
    {
        $t = $this->refinery->kindlyTo()->string();
        return \ilUtil::stripSlashes((string) ($this->get($key, $t) ?? ""));
    }

    /**
     * get string array kindly
     * @return string[]|array<int|string, string>
     */
    protected function strArray(string $key): array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            static function (array $arr): array {
                // keep keys(!), transform all values to string
                return array_map(
                    static function ($v): string {
                        return \ilUtil::stripSlashes((string) $v);
                    },
                    $arr
                );
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    /**
     * get bool parameter kindly
     */
    protected function bool(string $key): bool
    {
        $t = $this->refinery->kindlyTo()->bool();
        return (bool) ($this->get($key, $t) ?? false);
    }

    /**
     * get bool array kindly
     * @return bool[]|array<int|string, bool>
     */
    protected function boolArray(string $key): array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            static function (array $arr): array {
                // keep keys(!), transform all values to bool
                return array_map('boolval', $arr);
            }
        );
        return (array) ($this->get($key, $t) ?? []);
    }

    /**
     * Check if parameter is an array
     */
    protected function isArray(string $key): bool
    {
        if ($this->passed_query_params === null && $this->passed_post_data === null) {
            $no_transform = $this->refinery->identity();
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
     * Get passed parameter, if not data passed, get key from http request
     *
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

    /**
     * @return int[]
     */
    protected function getIds(): array
    {
        return $this->intArray("id");
    }
}
