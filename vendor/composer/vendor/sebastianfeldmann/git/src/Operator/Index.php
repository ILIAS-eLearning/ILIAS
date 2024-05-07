<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian.feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianFeldmann\Git\Operator;

use RuntimeException;
use SebastianFeldmann\Git\Command\Add\AddFiles;
use SebastianFeldmann\Git\Command\DiffIndex\GetStagedFiles;
use SebastianFeldmann\Git\Command\DiffIndex\GetStagedFiles\FilterByStatus;
use SebastianFeldmann\Git\Command\RevParse\GetCommitHash;
use SebastianFeldmann\Git\Command\Rm\RemoveFiles;

/**
 * Index Operator
 *
 * @package SebastianFeldmann\Git
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/sebastianfeldmann/git
 * @since   Class available since Release 0.9.0
 */
class Index extends Base
{
    /**
     * List of changed files
     *
     * @var array<string,array>
     */
    private $files = [];

    /**
     * Changed files by file type
     *
     * @var array<string, array>
     */
    private $types = [];

    /**
     * Default diff filter used
     *
     * @var array<string>
     */
    private $defaultDiffFilter = ['A', 'C', 'M', 'R'];

    /**
     * Get the list of files that changed
     *
     * @param  array<string> $diffFilter List of status you want to get returned, choose from [A,C,D,M,R,T,U,X,B,*]
     * @return array<string>
     */
    public function getStagedFiles(array $diffFilter = []): array
    {
        $filter = empty($diffFilter) ? $this->defaultDiffFilter : $diffFilter;
        return $this->retrieveStagedFiles($filter);
    }

    /**
     * Where there files changed of a given type
     *
     * @param  string $suffix
     * @return bool
     */
    public function hasStagedFilesOfType(string $suffix): bool
    {
        return count($this->getStagedFilesOfType($suffix)) > 0;
    }

    /**
     * Return list of changed files of a given type
     *
     * @param  string        $suffix
     * @param  array<string> $diffFilter
     * @return array<string>
     */
    public function getStagedFilesOfType(string $suffix, array $diffFilter = []): array
    {
        $filter = empty($diffFilter) ? $this->defaultDiffFilter : $diffFilter;
        return $this->retrieveStagedFilesByType($suffix, $filter);
    }

    /**
     * Update the index using the current content found in the working tree
     *
     * @param  array<string> $files
     * @return bool
     */
    public function addFilesToIndex(array $files): bool
    {
        $cmd    = (new AddFiles($this->repo->getRoot()))->files($files);
        $result = $this->runner->run($cmd);

        return $result->isSuccessful();
    }

    /**
     * Update the index just where it already has an entry matching <pathspec>
     *
     * This removes as well as modifies index entries to match the working tree,
     * but adds no new files.
     *
     * @param  array<string> $files
     * @return bool
     */
    public function updateIndex(array $files): bool
    {
        $cmd    = (new AddFiles($this->repo->getRoot()))->files($files)->update();
        $result = $this->runner->run($cmd);

        return $result->isSuccessful();
    }

    /**
     * Update the index not only where the working tree has a file matching
     * <pathspec> but also where the index already has an entry.
     *
     * This adds, modifies, and removes index entries to match the working tree.
     *
     * If `$ignoreRemoval` is `true`, files removed in the working tree are
     * ignored and not removed from the index.
     *
     * @param  array<string> $files
     * @param  bool          $ignoreRemoval Ignore files that have been removed from the working tree
     * @return bool
     */
    public function updateIndexToMatchWorkingTree(array $files, bool $ignoreRemoval = false): bool
    {
        $all = !$ignoreRemoval;

        $cmd = (new AddFiles($this->repo->getRoot()))
            ->files($files)
            ->all($all)
            ->noAll($ignoreRemoval);

        $result = $this->runner->run($cmd);

        return $result->isSuccessful();
    }

    /**
     * Record only the fact that the path will be added later
     *
     * An entry for the path is placed in the index with no content.
     *
     * @param  array<string> $files
     * @return bool
     */
    public function recordIntentToAddFiles(array $files): bool
    {
        $cmd    = (new AddFiles($this->repo->getRoot()))->files($files)->intentToAdd();
        $result = $this->runner->run($cmd);

        return $result->isSuccessful();
    }

    /**
     * Remove files from the working tree and from the index
     *
     * @param  array<string> $files      The files to remove.
     * @param  bool          $recursive  Allow recursive removal when a leading directory name is given
     * @param  bool          $cachedOnly Unstage and remove paths only from the index.
     *                                   The working tree is untouched.
     * @return bool
     */
    public function removeFiles(
        array $files,
        bool $recursive = false,
        bool $cachedOnly = false
    ): bool {
        $cmd = (new RemoveFiles($this->repo->getRoot()))
            ->files($files)
            ->recursive($recursive)
            ->cached($cachedOnly);

        $result = $this->runner->run($cmd);

        return $result->isSuccessful();
    }

    /**
     * Resolve the list of files that changed
     *
     * @param  array<string> $diffFilter
     * @return array<string>
     */
    private function retrieveStagedFiles(array $diffFilter): iterable
    {
        if (!$this->isHeadValid()) {
            return [];
        }

        if ($this->isCached($diffFilter)) {
            return $this->retrieveFromCache($diffFilter);
        }

        $cmd       = new GetStagedFiles($this->repo->getRoot());
        $formatter = new FilterByStatus($diffFilter);
        $result    = $this->runner->run($cmd, $formatter);
        $files     = $result->getFormattedOutput();
        $this->cacheFiles($diffFilter, $files);

        return $files;
    }

    /**
     * Check if the staged files are cached
     *
     * @param  array<string> $diffStatus
     * @return bool
     */
    private function isCached(array $diffStatus): bool
    {
        return isset($this->files[implode($diffStatus)]);
    }

    /**
     * Cache staged file by requested status
     *
     * @param  array<string> $diffFilter
     * @param  array<string> $files
     * @return void
     */
    private function cacheFiles(array $diffFilter, array $files): void
    {
        $this->files[implode($diffFilter)] = $files;
    }

    /**
     * Retrieve files from cache
     *
     * @param  array<string> $diffFilter
     * @return array<string>
     */
    private function retrieveFromCache(array $diffFilter): array
    {
        return $this->files[implode($diffFilter)];
    }

    /**
     * Sort files by file suffix
     *
     * @param  string        $suffix
     * @param  array<string> $diffFilter
     * @return array<string>
     */
    private function retrieveStagedFilesByType(string $suffix, array $diffFilter): array
    {
        $suffix = strtolower($suffix);
        $key    = implode($diffFilter);

        if (!isset($this->types[$key])) {
            $this->types[$key] = [];
            foreach ($this->retrieveStagedFiles($diffFilter) as $file) {
                $ext                       = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $this->types[$key][$ext][] = $file;
            }
        }
        return isset($this->types[$key][$suffix]) ? $this->types[$key][$suffix] : [];
    }

    /**
     * Check head validity
     *
     * @return bool
     */
    private function isHeadValid(): bool
    {
        try {
            $cmd    = new GetCommitHash($this->repo->getRoot());
            $result = $this->runner->run($cmd);
            return $result->isSuccessful();
        } catch (RuntimeException $e) {
            // if we do not have a permission error the current head is just invalid
            if ($e->getCode() !== 128) {
                return false;
            }
            throw $e;
        }
    }
}
