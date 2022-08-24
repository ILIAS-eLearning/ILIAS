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
 * Class BasicAccessCheckClosures
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicAccessCheckClosures
{
    private Container $dic;
    private array $access_cache = [];

    /**
     * BasicAccessCheckClosuresSingleton constructor.
     */
    public function __construct(?Container $dic = null)
    {
        global $DIC;
        $this->dic = $dic ?? $DIC;
    }

    public function isRepositoryReadable(?Closure $additional = null): Closure
    {
        if (!isset($this->access_cache['repo_read'])) {
            $is_user_logged_in = $this->isUserLoggedIn()();
            if (!$is_user_logged_in) {
                $this->access_cache['repo_read'] = $this->dic->settings()->get('pub_section') && $this->dic->access(
                    )->checkAccess(
                        'read',
                        '',
                        \ROOT_FOLDER_ID
                    );
            } else {
                $this->access_cache['repo_read'] = $this->dic->access()->checkAccess(
                    'read',
                    '',
                    \ROOT_FOLDER_ID
                );
            }
        }

        return $this->getClosureWithOptinalClosure(function (): bool {
            return $this->access_cache['repo_read'];
        }, $additional);
    }

    public function isRepositoryVisible(?Closure $additional = null): Closure
    {
        if (!isset($this->access_cache['repo_visible'])) {
            $is_user_logged_in = $this->isUserLoggedIn()();
            if (!$is_user_logged_in) {
                $this->access_cache['repo_visible'] = $this->dic->settings()->get('pub_section') && $this->dic->access(
                    )->checkAccess(
                        'visible',
                        '',
                        \ROOT_FOLDER_ID
                    );
            } else {
                $this->access_cache['repo_visible'] = $this->dic->access()->checkAccess(
                    'visible',
                    '',
                    \ROOT_FOLDER_ID
                );
            }
        }

        return $this->getClosureWithOptinalClosure(function (): bool {
            return $this->access_cache['repo_visible'];
        }, $additional);
    }

    public function isUserLoggedIn(?Closure $additional = null): Closure
    {
        if (!isset($this->access_cache['is_anonymous'])) {
            $this->access_cache['is_anonymous'] = ($this->dic->user()->isAnonymous() || $this->dic->user()->getId(
                ) === 0);
        }

        return $this->getClosureWithOptinalClosure(function (): bool {
            return !$this->access_cache['is_anonymous'];
        }, $additional);
    }

    public function hasAdministrationAccess(?Closure $additional = null): Closure
    {
        if (!isset($this->access_cache['has_admin_access'])) {
            $this->access_cache['has_admin_access'] = ($this->dic->rbac()->system()->checkAccess(
                'visible',
                \SYSTEM_FOLDER_ID
            ));
        }
        return $this->getClosureWithOptinalClosure(function (): bool {
            return $this->access_cache['has_admin_access'];
        }, $additional);
    }


    //
    // Internal
    //

    private function checkClosureForBoolReturnValue(Closure $c): bool
    {
        try {
            $r = new ReflectionFunction($c);
        } catch (Throwable $e) {
            return false;
        }

        if (!$r->hasReturnType() || !$r->getReturnType()->isBuiltin()) {
            throw new InvalidArgumentException('the additional Closure MUST return a bool dy declaration');
        }
        return true;
    }

    private function getClosureWithOptinalClosure(Closure $closure, ?Closure $additional = null): Closure
    {
        if ($additional instanceof Closure && $this->checkClosureForBoolReturnValue($additional)) {
            return static function () use ($closure, $additional): bool {
                return $additional() && $closure();
            };
        }

        return $closure;
    }
}
