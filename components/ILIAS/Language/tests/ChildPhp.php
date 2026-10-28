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


/**
 * Starts child PHP processes with Xdebug off (ini and XDEBUG_MODE), so no debugger notices reach the stderr tests assert empty.
 */
final class ChildPhp
{
    /** @param list<string> $arguments everything after the PHP binary
     * @return list<string>
     */
    public static function command(array $arguments): array
    {
        return [PHP_BINARY, '-d', 'xdebug.mode=off', ...$arguments];
    }

    /** @return array<string, string> */
    public static function environment(): array
    {
        return [...getenv(), 'XDEBUG_MODE' => 'off'];
    }

    /**
     * @param list<string> $arguments see command()
     * @return array{resource, array<int, resource>}|null process and pipes (1 = stdout, 2 = stderr)
     */
    public static function open(array $arguments, ?string $working_directory = null): ?array
    {
        $process = proc_open(
            self::command($arguments),
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $working_directory,
            self::environment()
        );

        return is_resource($process) ? [$process, $pipes] : null;
    }

    /**
     * @param list<string> $arguments see command()
     * @return array{int, string, string}|null exit code, stdout, stderr; null if the process did not start
     */
    public static function run(array $arguments, ?string $working_directory = null): ?array
    {
        $started = self::open($arguments, $working_directory);
        if ($started === null) {
            return null;
        }
        [$process, $pipes] = $started;
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $stdout, $stderr];
    }
}
