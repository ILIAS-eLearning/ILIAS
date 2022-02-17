<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Helper;

use Closure;
use ReflectionFunction;

/**
 * Class BasicAccessCheckClosures
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicAccessCheckClosures
{

    /**
     * @var self
     */
    protected static $instance;
    private $dic;

    /**
     * BasicAccessCheckClosures constructor.
     * @param $dic
     */
    protected function __construct()
    {
        global $DIC;
        $this->dic = $DIC;
    }

    /**
     * @return self
     */
    public static function getInstance() : self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isRepositoryReadable(?Closure $additional = null) : Closure
    {
        static $repo_read;
        if (!isset($repo_read)) {
            $is_user_logged_in = $this->isUserLoggedIn()();
            if (!$is_user_logged_in) {
                $repo_read = (bool) $this->dic->settings()->get('pub_section') && $this->dic->access()->checkAccess('read', '', ROOT_FOLDER_ID);
            } else {
                $repo_read = (bool) $this->dic->access()->checkAccess('read', '', ROOT_FOLDER_ID);
            }
        }

        return $this->getClosureWithOptinalClosure(static function () use ($repo_read) : bool {
            return $repo_read;
        }, $additional);
    }

    public function isPublicSectionActive(?Closure $additional = null) : Closure
    {
        static $public_section_active;
        if (!isset($public_section_active)) {
            $public_section_active = (bool) $this->dic->settings()->get('pub_section') && $this->dic->access()->checkAccess('visible',
                    '', ROOT_FOLDER_ID);
        }

        return $this->getClosureWithOptinalClosure(function () use ($public_section_active) : bool {
            return $public_section_active;
        }, $additional);
    }

    public function isRepositoryVisible(?Closure $additional = null) : Closure
    {
        static $repo_visible;
        if (!isset($repo_visible)) {
            $is_user_logged_in = $this->isUserLoggedIn()();
            if (!$is_user_logged_in) {
                $repo_visible = (bool) $this->dic->settings()->get('pub_section') && $this->dic->access()->checkAccess('visible', '', ROOT_FOLDER_ID);
            } else {
                $repo_visible = (bool) $this->dic->access()->checkAccess('visible', '', ROOT_FOLDER_ID);
            }
        }

        return $this->getClosureWithOptinalClosure(static function () use ($repo_visible) : bool {
            return $repo_visible;
        }, $additional);
    }

    public function isUserLoggedIn(?Closure $additional = null) : Closure
    {
        static $is_anonymous;
        if (!isset($is_anonymous)) {
            $is_anonymous = (bool) $this->dic->user()->isAnonymous() || ($this->dic->user()->getId() == 0);
        }

        return $this->getClosureWithOptinalClosure(static function () use ($is_anonymous) : bool {
            return !$is_anonymous;
        }, $additional);
    }

    public function hasAdministrationAccess(?Closure $additional = null) : Closure
    {
        static $has_admin_access;
        if (!isset($has_admin_access)) {
            $has_admin_access = (bool) ($this->dic->rbac()->system()->checkAccess('visible', SYSTEM_FOLDER_ID));
        }
        return $this->getClosureWithOptinalClosure(static function () use ($has_admin_access) : bool {
            return $has_admin_access;
        }, $additional);
    }


    //
    // Internal
    //

    private function checkClosureForBoolReturnValue(Closure $c) : bool
    {
        try {
            $r = new ReflectionFunction($c);
        } catch (\Throwable $e) {
            return false;
        }

        if(!$r->hasReturnType() || !$r->getReturnType()->isBuiltin()){
            throw new \InvalidArgumentException('the additional Closure MUST return a bool dy declaration');
        }
        return true;
    }

    private function getClosureWithOptinalClosure(Closure $closure, ?Closure $additional = null) : Closure
    {
        if ($additional instanceof Closure && $this->checkClosureForBoolReturnValue($additional)) {
            return static function () use ($closure, $additional) : bool {
                return $additional() && $closure();
            };
        }

        return $closure;
    }
}
