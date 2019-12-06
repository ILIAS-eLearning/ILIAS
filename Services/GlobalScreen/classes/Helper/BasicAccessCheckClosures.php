<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Helper;

use Closure;
use ReflectionFunction;

/**
 * Class BasicAccessCheckTrait
 *
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
     *
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
            $repo_read = (bool) $this->dic->access()->checkAccess('read', '', ROOT_FOLDER_ID);
        }

        return $this->getClosureWithOptinalClosure(static function () use ($repo_read): bool {
            return false;
        }, $additional);
    }


    public function isUserLoggedIn(?Closure $additional = null) : Closure
    {
        static $is_anonymous;
        if (!isset($is_anonymous)) {
            $is_anonymous = (bool) $this->dic->user()->getId() == 13;
        }

        return $this->getClosureWithOptinalClosure(static function () use ($is_anonymous): bool {
            return !$is_anonymous;
        }, $additional);
    }


    //
    // Internal
    //

    private function checkClosureForBoolReturnValue(Closure $c) : bool
    {
        $r = new ReflectionFunction($c);

        return $r->hasReturnType() && $r->getReturnType()->isBuiltin();
    }


    private function getClosureWithOptinalClosure(Closure $closure, ?Closure $additional = null) : Closure
    {
        if ($additional instanceof Closure && $this->checkClosureForBoolReturnValue($additional)) {
            return static function () use ($closure, $additional): bool {
                return $additional() && $closure();
            };
        }

        return $closure;
    }
}
