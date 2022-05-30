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

use ILIAS\HTTP;
use ILIAS\Refinery;

class ilBadgeGUIRequest
{
    protected HTTP\Services $http;
    protected Refinery\Factory $refinery;

    public function __construct(
        HTTP\Services $http,
        Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    protected function initRequest(
        HTTP\Services $http,
        Refinery\Factory $refinery
    ) : void {
        $this->http = $http;
        $this->refinery = $refinery;
    }

    // get string parameter kindly
    protected function str(string $key) : string
    {
        $t = $this->refinery->kindlyTo()->string();
        return \ilUtil::stripSlashes((string) ($this->get($key, $t) ?? ""));
    }

    // get integer parameter kindly
    protected function int(string $key) : int
    {
        $t = $this->refinery->kindlyTo()->int();
        return (int) ($this->get($key, $t) ?? 0);
    }

    // get integer array kindly
    protected function intArray(string $key) : array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            static function (array $arr) : array {
                // keep keys(!), transform all values to int
                return array_column(
                    array_map(
                        static function ($k, $v) : array {
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
                            if (is_array($v)) {
                                $v = "";
                            }
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

    /**
     * Check if parameter is an array
     */
    protected function isArray(string $key) : bool
    {
        $no_transform = $this->refinery->identity();
        $w = $this->http->wrapper();
        if ($w->post()->has($key)) {
            return is_array($w->post()->retrieve($key, $no_transform));
        }
        if ($w->query()->has($key)) {
            return is_array($w->query()->retrieve($key, $no_transform));
        }
        return false;
    }

    /**
     * @return mixed|null
     */
    protected function get(string $key, Refinery\Transformation $t)
    {
        $w = $this->http->wrapper();
        if ($w->post()->has($key)) {
            return $w->post()->retrieve($key, $t);
        }
        if ($w->query()->has($key)) {
            return $w->query()->retrieve($key, $t);
        }
        return null;
    }

    /** @return int [] */
    public function getBadgeIds() : array
    {
        $badge_ids = $this->intArray("badge_id");
        if (count($badge_ids) === 0 && $this->int("badge_id") > 0) {
            $badge_ids = [$this->int("badge_id")];
        }
        return $badge_ids;
    }

    public function getBadgeId() : int
    {
        return $this->int("bid");
    }

    public function getId() : int
    {
        return $this->int("id");
    }

    /** @return string[] */
    public function getIds() : array
    {
        return $this->strArray("id");
    }

    public function getType() : string
    {
        return $this->str("type");
    }

    public function getTgt() : string
    {
        return $this->str("tgt");
    }

    public function getTemplateId() : int
    {
        return $this->int("tid");
    }

    public function getParentId() : int
    {
        return $this->int("pid");
    }
}
