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

use ILIAS\Chatroom\CleanUpBans;

class ilChatroomAppEventListener implements ilAppEventListener
{
    private static ?ilChatroomAppEventListener $instance = null;

    private readonly CleanUpBans $clean_up;

    /**
     * @param array<string, mixed>  $a_parameter
     */
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        global $DIC;
        self::$instance ??= new self($DIC->database());
        self::$instance->handle($component, $event, $parameter);
    }

    private function __construct(ilDBInterface $database)
    {
        $this->clean_up = new CleanUpBans($this->dbCachePrepare($database));
    }

    private function handle(string $component, string $event, array $parameter): void
    {
        if ($event === 'deleteUser') {
            $this->clean_up->removeEntriesOfDeletedUser($parameter['usr_id']);
        }
    }

    /**
     * @return Closure(string, array): Closure(array): void
     */
    private function dbCachePrepare(ilDBInterface $db): Closure
    {
        return $this->cache(function (...$args) use ($db): Closure {
            $statement = $db->prepare(...$args);
            return fn(array $values = []) => $db->execute($statement, $values);
        });
    }

    /**
     * @template A
     * @template B
     *
     * @param Closure(...A): B
     * @return Closure(...A): B
     */
    private function cache(Closure $proc): Closure
    {
        $cache = [];
        return function (...$args) use (&$cache, $proc) {
            $key = json_encode($args);
            if (!isset($cache[$key])) {
                $cache[$key] = $proc(...$args);
            }
            return $cache[$key];
        };
    }
}
