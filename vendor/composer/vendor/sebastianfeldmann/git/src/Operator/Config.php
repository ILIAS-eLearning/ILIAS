<?php

/**
 * This file is part of SebastianFeldmann\Git.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianFeldmann\Git\Operator;

use RuntimeException;
use SebastianFeldmann\Cli\Command\Runner\Result;
use SebastianFeldmann\Git\Command\Config\Get;
use SebastianFeldmann\Git\Command\Config\ListSettings;

/**
 * Class Config
 *
 * @package SebastianFeldmann\Git
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/git
 * @since   Class available since Release 1.0.2
 */
class Config extends Base
{
    /**
     * Does git have a configuration key.
     *
     * @param  string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        try {
            $result = $this->configCommand($name);
        } catch (RuntimeException $exception) {
            return false;
        }

        return $result->isSuccessful();
    }

    /**
     * Get a configuration key value.
     *
     * @param  string $name
     * @return string
     */
    public function get(string $name): string
    {
        $result = $this->configCommand($name);

        return $result->getBufferedOutput()[0];
    }

    /**
     * Get config values without throwing exceptions.
     *
     * You can provide a default value to return.
     * By default the return value on unset config values is the empty string.
     *
     * @param  string $name    Name of the config value to retrieve
     * @param  string $default Value to return if config value is not set, empty string by default
     * @return string
     */
    public function getSafely(string $name, string $default = '')
    {
        return $this->has($name) ? $this->get($name) : $default;
    }

    /**
     * Return a map of all configuration settings.
     *
     * For example: ['color.branch' => 'auto', 'color.diff' => 'auto]
     *
     * @return array<string, string>
     */
    public function getSettings(): array
    {
        $cmd = new ListSettings($this->repo->getRoot());
        $res = $this->runner->run($cmd, new ListSettings\MapSettings());

        return $res->getFormattedOutput();
    }

    /**
     * Run the get config command.
     *
     * @param  string $name
     * @return \SebastianFeldmann\Cli\Command\Runner\Result
     */
    private function configCommand(string $name): Result
    {
        $cmd = (new Get($this->repo->getRoot()));
        $cmd->name($name);

        return $this->runner->run($cmd);
    }
}
