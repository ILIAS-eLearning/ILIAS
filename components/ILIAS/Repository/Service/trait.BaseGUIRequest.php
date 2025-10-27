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

declare(strict_types=1);

namespace ILIAS\Repository;

use ILIAS\HTTP;
use ILIAS\Refinery;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\ServerRequestInterface;

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
    ): void {
        $this->http = $http;
        $this->refinery = $refinery;
        $this->passed_post_data = $passed_post_data;
        $this->passed_query_params = $passed_query_params;
    }

    private function retrieveArray(string $key, int $depth, Transformation $transformation): array
    {
        $chain = $this->refinery->kindlyTo()->dictOf($transformation);
        for ($i = 1; $i < $depth; $i++) {
            $chain = $this->refinery->kindlyTo()->dictOf($chain);
        }

        return $this->get(
            $key,
            $this->refinery->byTrying([
                $chain,
                $this->refinery->always([])
            ])
        ) ?? [];
    }

    protected function strip(string $input): string
    {
        // see https://www.ilias.de/mantis/view.php?id=19727
        $str = \ilUtil::stripSlashes($input);
        if ($str !== $input) {
            $str = \ilUtil::stripSlashes(str_replace("<", "< ", $input));
        }
        return $str;
    }

    protected function str(string $key): string
    {
        return $this->string($key);
    }

    protected function arrayArray(string $key): array
    {
        if (!$this->isArray($key)) {
            return [];
        }
        $t = $this->refinery->custom()->transformation(
            function ($arr) {
                // keep keys(!), transform all values to string
                return array_column(
                    array_map(
                        static function ($k, $v): array {
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
     * @param string                  $key
     * @param Refinery\Transformation $t
     * @return mixed|null
     */
    protected function get(string $key, Refinery\Transformation $t): mixed
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
     * @return mixed|null
     */
    public function raw(string $key): mixed
    {
        return $this->get($key, $this->refinery->identity());
    }

    public function int(string $key): int
    {
        try {
            return $this->get($key, $this->refinery->kindlyTo()->int()) ?? 0;
        } catch (ConstraintViolationException) {
            return 0;
        }
    }

    public function float(string $key): float
    {
        try {
            return $this->get($key, $this->refinery->kindlyTo()->float()) ?? 0.0;
        } catch (ConstraintViolationException) {
            return 0.0;
        }
    }

    public function string(string $key): string
    {
        return $this->get($key, $this->refinery->kindlyTo()->string()) ?? '';
    }

    public function bool(string $key): ?bool
    {
        return $this->get($key, $this->refinery->kindlyTo()->bool());
    }

    public function strArray(string $key, int $depth = 1): array
    {
        return $this->retrieveArray($key, $depth, $this->refinery->kindlyTo()->string());
    }

    public function floatArray(string $key, int $depth = 1): array
    {
        return $this->retrieveArray($key, $depth, $this->refinery->kindlyTo()->float());
    }

    public function intArray(string $key, int $depth = 1): array
    {
        return $this->retrieveArray($key, $depth, $this->refinery->kindlyTo()->int());
    }

    public function rawArray(string $key, int $depth = 1): array
    {
        return $this->retrieveArray($key, $depth, $this->refinery->identity());
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->http->request();
    }

    public function isset(string $key): bool
    {
        return $this->raw($key) !== null;
    }

    public function hasRefId(): bool
    {
        return $this->raw('ref_id') !== null;
    }

    public function getRefId(): int
    {
        return $this->int('ref_id');
    }

    public function hasQuestionId(): bool
    {
        return $this->raw('q_id') !== null;
    }

    public function getQuestionId(): int
    {
        return $this->int('q_id');
    }

    public function getIds(): array
    {
        return $this->strArray('id');
    }

    public function getParsedBody(): object|array|null
    {
        return $this->http->request()->getParsedBody();
    }

    public function getPostKeys(): array
    {
        return $this->http->wrapper()->post()->keys();
    }

    public function getMultiSelectionIds(string $key): array|string
    {
        $query = $this->http->wrapper()->query();

        if (!$query->has($key)) {
            return [];
        }

        return $query->retrieve(
            $key,
            $this->refinery->custom()->transformation(
                static fn(array|string $value): array|string => $value === 'ALL_OBJECTS' || $value[0] === 'ALL_OBJECTS'
                    ? 'ALL_OBJECTS'
                    : array_map('intval', $value)
            )
        );
    }
}
