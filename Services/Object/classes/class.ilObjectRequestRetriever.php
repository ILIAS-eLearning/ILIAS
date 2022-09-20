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

use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Transformation;

/**
 * Base class for all sub item list gui's
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjectRequestRetriever
{
    protected WrapperFactory $wrapper;
    protected Factory $refinery;

    public function __construct(WrapperFactory $wrapper, Factory $refinery)
    {
        $this->wrapper = $wrapper;
        $this->refinery = $refinery;
    }

    /**
     * @return mixed
     */
    private function getFromRequest(string $key, Transformation $t)
    {
        if ($this->wrapper->query()->has($key)) {
            return $this->wrapper->query()->retrieve($key, $t);
        }
        if ($this->wrapper->post()->has($key)) {
            return $this->wrapper->post()->retrieve($key, $t);
        }
        return null;
    }

    public function has(string $key): bool
    {
        return $this->wrapper->query()->has($key)
            || $this->wrapper->post()->has($key);
    }

    public function getMaybeInt(string $key, ?int $fallback = null): ?int
    {
        return $this->getFromRequest($key, $this->refinery->kindlyTo()->int()) ?? $fallback;
    }

    public function getMaybeString(string $key, ?string $fallback = null): ?string
    {
        return $this->getFromRequest($key, $this->refinery->kindlyTo()->string()) ?? $fallback;
    }

    public function getBool(string $key): bool
    {
        return $this->getFromRequest($key, $this->refinery->kindlyTo()->bool()) ?? false;
    }
}
