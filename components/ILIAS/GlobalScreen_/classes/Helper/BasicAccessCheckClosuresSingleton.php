<?php

declare(strict_types=1);

namespace ILIAS\GlobalScreen\Helper;

use Closure;
use ReflectionFunction;
use Throwable;
use InvalidArgumentException;
use ILIAS\DI\Container;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class BasicAccessCheckClosuresSingleton
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicAccessCheckClosuresSingleton
{
    protected static ?BasicAccessCheckClosures $instance = null;

    /**
     * BasicAccessCheckClosuresSingleton constructor.
     */
    private function __construct()
    {
    }

    public static function getInstance(): BasicAccessCheckClosures
    {
        if (!self::$instance instanceof BasicAccessCheckClosures) {
            self::$instance = new BasicAccessCheckClosures();
        }

        return self::$instance;
    }
}
