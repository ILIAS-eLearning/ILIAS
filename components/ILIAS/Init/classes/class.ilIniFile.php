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

use ILIAS\Environment\Configuration\Instance\ConfigurationReadRepository;
use ILIAS\Environment\Configuration\Instance\ConfigurationWriteRepository;
use ILIAS\Environment\Configuration\Instance\IniFileConfigurationRepository;

/**
 * INI file parser — compatibility wrapper.
 *
 * All file I/O is now delegated to
 * {@see \ILIAS\Environment\Configuration\Instance\IniFileConfigurationRepository}.
 * Use the typed interfaces {@see \ILIAS\Environment\Configuration\Instance\IliasIni}
 * and {@see \ILIAS\Environment\Configuration\Instance\ClientIni} for new code.
 *
 * @deprecated Use ILIAS\Environment\Configuration\Instance\IliasIni or ClientIni instead.
 */
class ilIniFile
{
    /** @deprecated public properties are kept for backward compatibility only */
    public string $INI_FILE_NAME = '';
    public string $ERROR = '';
    /** @var array<string, array<string, string>> */
    public array $GROUPS = [];
    public string $CURRENT_GROUP = '';

    private ConfigurationReadRepository $repository;

    /**
     * @param string                          $a_ini_file_name  Path to the ini file (legacy usage).
     * @param ConfigurationReadRepository|null $repository       Pre-built repository (bootstrap usage).
     *                                                           When provided, $a_ini_file_name is ignored for I/O.
     */
    public function __construct(
        string $a_ini_file_name = '',
        ?ConfigurationReadRepository $repository = null
    ) {
        $this->INI_FILE_NAME = $a_ini_file_name;

        if ($repository !== null) {
            $this->repository = $repository;
        } else {
            try {
                $this->repository = new IniFileConfigurationRepository($a_ini_file_name);
            } catch (\RuntimeException $e) {
                $this->ERROR = $e->getMessage();
                $this->repository = new IniFileConfigurationRepository(''); // empty fallback
                return;
            }
        }

        $this->syncGroups();
    }

    // — Legacy read API —

    /**
     * @deprecated Kept for backward compatibility. File is read in the constructor.
     */
    public function read(): bool
    {
        if ($this->ERROR !== '') {
            return false;
        }
        if ($this->INI_FILE_NAME !== '' && !file_exists($this->INI_FILE_NAME)) {
            $this->ERROR = 'file_does_not_exist';
            return false;
        }
        return !empty($this->GROUPS);
    }

    /**
     * @deprecated Kept for backward compatibility. Parsing happens in the constructor.
     */
    public function parse(): bool
    {
        return $this->read();
    }

    public function getGroupCount(): int
    {
        return count($this->GROUPS);
    }

    public function readGroups(): array
    {
        return $this->repository->getSections();
    }

    public function groupExists(string $a_group_name): bool
    {
        return $this->repository->hasSection($a_group_name);
    }

    public function readGroup(string $a_group_name): array
    {
        if (!$this->repository->hasSection($a_group_name)) {
            $this->ERROR = "Group '$a_group_name' does not exist";
            return [];
        }
        return $this->repository->getSection($a_group_name);
    }

    public function variableExists(string $a_group, string $a_var_name): bool
    {
        return $this->repository->has($a_group, $a_var_name);
    }

    public function readVariable(string $a_group, string $a_var_name): string
    {
        return $this->repository->has($a_group, $a_var_name)
            ? $this->repository->get($a_group, $a_var_name)
            : '';
    }

    // — Legacy write API —

    public function write(): bool
    {
        if (!$this->repository instanceof ConfigurationWriteRepository) {
            $this->ERROR = 'Repository does not support writing.';
            return false;
        }
        try {
            $this->repository->persist();
            return true;
        } catch (\RuntimeException $e) {
            $this->ERROR = $e->getMessage();
            return false;
        }
    }

    public function addGroup(string $a_group_name): bool
    {
        if (!$this->repository instanceof ConfigurationWriteRepository) {
            $this->ERROR = 'Repository does not support writing.';
            return false;
        }
        if ($this->repository->hasSection($a_group_name)) {
            $this->ERROR = "Group '$a_group_name' exists";
            return false;
        }
        $this->repository->addSection($a_group_name);
        $this->GROUPS[$a_group_name] = [];
        return true;
    }

    public function removeGroup(string $a_group_name): bool
    {
        if (!$this->repository instanceof ConfigurationWriteRepository) {
            $this->ERROR = 'Repository does not support writing.';
            return false;
        }
        if (!$this->repository->hasSection($a_group_name)) {
            $this->ERROR = "Group '$a_group_name' does not exist";
            return false;
        }
        $this->repository->removeSection($a_group_name);
        unset($this->GROUPS[$a_group_name]);
        return true;
    }

    public function setVariable(string $a_group_name, string $a_var_name, string $a_var_value): bool
    {
        if (!$this->repository instanceof ConfigurationWriteRepository) {
            $this->ERROR = 'Repository does not support writing.';
            return false;
        }
        if (!$this->repository->hasSection($a_group_name)) {
            $this->ERROR = "Group '$a_group_name' does not exist";
            return false;
        }
        $this->repository->set($a_group_name, $a_var_name, $a_var_value);
        $this->GROUPS[$a_group_name][$a_var_name] = $a_var_value;
        return true;
    }

    // — Error handling —

    public function error(string $a_errmsg): bool
    {
        $this->ERROR = $a_errmsg;
        return true;
    }

    public function getError(): string
    {
        return $this->ERROR;
    }

    // — Kept for very old code that calls show() —

    public function show(): string
    {
        $content = '';
        foreach ($this->GROUPS as $group_name => $vars) {
            $content .= ($content === '' ? '' : "\n") . "[$group_name]\n";
            foreach ($vars as $key => $value) {
                $content .= "$key = $value\n";
            }
        }
        return $content;
    }

    // — Internal —

    private function syncGroups(): void
    {
        $this->GROUPS = [];
        foreach ($this->repository->getSections() as $section) {
            $this->GROUPS[$section] = $this->repository->getSection($section);
        }
        $keys = array_keys($this->GROUPS);
        if ($keys !== []) {
            $this->CURRENT_GROUP = end($keys);
        }
    }
}
