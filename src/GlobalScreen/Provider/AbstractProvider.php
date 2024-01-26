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
namespace ILIAS\GlobalScreen\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Services;

/**
 * Class AbstractProvider
 * @package ILIAS\GlobalScreen\Provider
 */
abstract class AbstractProvider implements Provider
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;
    /**
     * @var string
     */
    private $provider_name_cache = "";

    /**
     * @inheritDoc
     */
    public function __construct(Container $dic)
    {
        $this->dic = $dic;
    }

    /**
     * @return Services
     */
    protected function globalScreen() : Services
    {
        return $this->dic->globalScreen();
    }

    /**
     * @inheritDoc
     */
    final public function getFullyQualifiedClassName() : string
    {
        return self::class;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getProviderNameForPresentation() : string
    {
        if ($this->provider_name_cache !== "" && is_string($this->provider_name_cache)) {
            return $this->provider_name_cache;
        }
        $reflector = new \ReflectionClass($this);

        $re = "/.*[\\\|\\/](?P<provider>(Services|Modules)[\\\|\\/].*)[\\\|\\/]classes/m";

        preg_match($re, str_replace("\\", "/", $reflector->getFileName()), $matches);

        $this->provider_name_cache = isset($matches[1]) ? is_string($matches[1]) ? $matches[1] : self::class : self::class;

        return $this->provider_name_cache;
    }
}
