<?php

/**
 * This file is part of SebastianFeldmann\Git.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianFeldmann\Git;

use RuntimeException;
use SebastianFeldmann\Cli\Command\Runner;

/**
 * Class Repository
 *
 * @package SebastianFeldmann\Git
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/git
 * @since   Class available since Release 0.9.0
 */
class Repository
{
    /**
     * Path to git repository root
     *
     * @var string
     */
    private $root;

    /**
     * Path to .git directory
     *
     * @var string
     */
    private $dotGitDir;

    /**
     * Commit message.
     *
     * @var \SebastianFeldmann\Git\CommitMessage
     */
    private $commitMsg;

    /**
     * Executes cli commands
     *
     * @var \SebastianFeldmann\Cli\Command\Runner
     */
    private $runner;

    /**
     * Map of operators
     *
     * @var array<string, object>
     */
    private $operator = [];

    /**
     * Repository constructor
     *
     * @param string                                $root
     * @param \SebastianFeldmann\Cli\Command\Runner $runner
     */
    public function __construct(string $root = '', Runner $runner = null)
    {
        $path            = empty($root) ? getcwd() : $root;
        $this->root      = $path;
        $this->dotGitDir = $this->root . '/.git';
        $this->runner    = null == $runner ? new Runner\Simple() : $runner;

        if (self::isGitSubmodule($this->dotGitDir)) {
            // For submodules hooks are stored in the parents .git/modules directory
            $dotGitContents = file_get_contents($root . '/.git');
            if (preg_match('/^gitdir:\s*(.+)$/m', $dotGitContents, $matches)) {
                $this->dotGitDir = $root . '/' . $matches[1];
            }
        }
    }

    /**
     * Root path getter
     *
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * Returns the path to the hooks directory
     *
     * @return string
     */
    public function getHooksDir(): string
    {
        return $this->dotGitDir . DIRECTORY_SEPARATOR . 'hooks';
    }

    /**
     * Check for a hook file.
     *
     * @param  string $hook
     * @return bool
     */
    public function hookExists($hook): bool
    {
        return file_exists($this->getHooksDir() . DIRECTORY_SEPARATOR . $hook);
    }

    /**
     * CommitMessage setter.
     *
     * @param  \SebastianFeldmann\Git\CommitMessage $commitMsg
     * @return void
     */
    public function setCommitMsg(CommitMessage $commitMsg): void
    {
        $this->commitMsg = $commitMsg;
    }

    /**
     * CommitMessage getter.
     *
     * @return \SebastianFeldmann\Git\CommitMessage
     */
    public function getCommitMsg(): CommitMessage
    {
        if (null === $this->commitMsg) {
            throw new RuntimeException('No commit message available');
        }
        return $this->commitMsg;
    }

    /**
     * Is there a merge in progress
     *
     * Will return true as soon as there are any MERGE_* files present in your .git directory.
     * This is not only the case while merging but can also happen if you use `cherry-pick`
     * without letting git instantly commit the picked changes.
     *
     * @return bool
     */
    public function isMerging(): bool
    {
        foreach (['MERGE_MSG', 'MERGE_HEAD', 'MERGE_MODE'] as $fileName) {
            if (file_exists($this->dotGitDir . DIRECTORY_SEPARATOR . $fileName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get index operator.
     *
     * @return \SebastianFeldmann\Git\Operator\Index
     */
    public function getIndexOperator(): Operator\Index
    {
        return $this->getOperator('Index');
    }

    /**
     * Get info operator.
     *
     * @return \SebastianFeldmann\Git\Operator\Info
     */
    public function getInfoOperator(): Operator\Info
    {
        return $this->getOperator('Info');
    }

    /**
     * Get log operator.
     *
     * @return \SebastianFeldmann\Git\Operator\Log
     */
    public function getLogOperator(): Operator\Log
    {
        return $this->getOperator('Log');
    }

    /**
     * Get config operator.
     *
     * @return \SebastianFeldmann\Git\Operator\Config
     */
    public function getConfigOperator(): Operator\Config
    {
        return $this->getOperator('Config');
    }

    /**
     * Get diff operator
     *
     * Responsible for inspection and comparison commands
     *
     * @return \SebastianFeldmann\Git\Operator\Diff
     */
    public function getDiffOperator(): Operator\Diff
    {
        return $this->getOperator('Diff');
    }

    /**
     * Get status operator.
     *
     * @return \SebastianFeldmann\Git\Operator\Status
     */
    public function getStatusOperator(): Operator\Status
    {
        return $this->getOperator('Status');
    }

    /**
     * Return requested operator.
     *
     * @param  string $name
     * @return mixed
     */
    private function getOperator(string $name)
    {
        if (!isset($this->operator[$name])) {
            $class                 = '\\SebastianFeldmann\\Git\\Operator\\' . $name;
            $this->operator[$name] = new $class($this->runner, $this);
        }
        return $this->operator[$name];
    }

    /**
     * Creates a Repository but makes sure the repository exists
     *
     * @param  string                                     $root
     * @param  \SebastianFeldmann\Cli\Command\Runner|null $runner
     * @return \SebastianFeldmann\Git\Repository
     */
    public static function createVerified(string $root, Runner $runner = null): Repository
    {
        if (!self::isGitRepository($root)) {
            throw new RuntimeException(sprintf('Invalid git repository: %s', $root));
        }
        return new Repository($root, $runner);
    }

    /**
     * @param  string $root
     * @return bool
     */
    public static function isGitRepository(string $root): bool
    {
        return is_dir($root . '/.git') || is_file($root . '/.git');
    }

    /**
     * @param  string $root
     * @return bool
     */
    public static function isGitSubmodule(string $root): bool
    {
        return !is_dir($root) && is_file($root);
    }
}
