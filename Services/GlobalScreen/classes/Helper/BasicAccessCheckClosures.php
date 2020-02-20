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

        return $r->hasReturnType() && $r->getReturnType()->isBuiltin();
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
