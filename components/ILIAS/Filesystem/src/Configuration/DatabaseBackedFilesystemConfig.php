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

namespace ILIAS\Filesystem\Configuration;

use ILIAS\Database\PDO\External;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DatabaseBackedFilesystemConfig implements FilesystemConfig
{
    public const string MODULE_NAME = LegacyFilesystemConfigProxy::MODULE_NAME;
    public const string F_BG_LIMIT = LegacyFilesystemConfigProxy::F_BG_LIMIT;
    public const string F_INLINE_FILE_EXTENSIONS = LegacyFilesystemConfigProxy::F_INLINE_FILE_EXTENSIONS;
    public const string F_SHOW_AMOUNT_OF_DOWNLOADS = LegacyFilesystemConfigProxy::F_SHOW_AMOUNT_OF_DOWNLOADS;
    public const string F_DOWNLOAD_ASCII_FILENAME = LegacyFilesystemConfigProxy::F_DOWNLOAD_ASCII_FILENAME;
    public const string F_BYPASS = LegacyFilesystemConfigProxy::F_BYPASS;

    private ?int $file_admin_ref_id = null;
    private ?bool $bypass_allowed = null;

    private array $resolved_values = [];
    private array $white_list_negative = [];
    private array $white_list_positive = [];
    private ?array $white_list_overall = null;
    private array $black_list_prohibited = [];
    private ?array $black_list_overall = null;
    private array $white_list_default = [];

    public function __construct(private readonly External $db)
    {
        $this->white_list_default = include __DIR__ . "/../../../FileServices/defaults/default_whitelist.php";
    }

    private function determineFileAdminRefId(): int
    {
        if ($this->file_admin_ref_id !== null) {
            return $this->file_admin_ref_id;
        }

        try {
            $r = $this->db->query(
                "SELECT ref_id FROM object_reference JOIN object_data ON object_reference.obj_id = object_data.obj_id WHERE object_data.type = 'facs';"
            );
            $r = $this->db->fetchObject($r);
            return $this->file_admin_ref_id = (int) ($r->ref_id ?? 0);
        } catch (\Throwable) {
            return $this->file_admin_ref_id = 0;
        }
    }

    public function isByPassAllowedForCurrentUser(): bool
    {
        if ($this->bypass_allowed !== null) {
            return $this->bypass_allowed;
        }
        global $DIC;
        return $this->bypass_allowed = ($DIC->isDependencyAvailable('rbac')
            && isset($DIC['rbacsystem'])
            && $DIC->rbac()->system()->checkAccess(
                'upload_blacklisted_files',
                $this->determineFileAdminRefId()
            ));
    }

    protected function fromSettingsTable(string $module, string $key, mixed $default = null): mixed
    {
        if (isset($this->resolved_values[$module][$key])) {
            return $this->resolved_values[$module][$key];
        }

        try {
            $res = $this->db->queryF(
                "SELECT * FROM settings WHERE module = %s AND keyword = %s",
                ['text', 'text'],
                [$module, $key]
            );
            return $this->resolved_values[$module][$key] = ($this->db->fetchAssoc($res)['value'] ?? $default);
        } catch (\Throwable $t) {
            throw new \LogicException('Cannot read configuration during bootstrap. ' . $t->getMessage(), $t->getCode(), $t);
        }
    }

    public function isASCIIConvertionEnabled(): bool
    {
        return $this->fromSettingsTable(
            self::MODULE_NAME,
            self::F_DOWNLOAD_ASCII_FILENAME,
            false
        );
    }

    private function getCleaner(): \Closure
    {
        return fn(string $suffix): string => trim(strtolower($suffix));
    }

    private function read():void {
        $this->readBlackList();
        $this->readWhiteList();
    }

    private function readWhiteList(): void
    {
        $cleaner = $this->getCleaner();

        $this->white_list_negative = array_map(
            $cleaner,
            explode(",", (string) $this->fromSettingsTable('common', "suffix_repl_additional", ''))
        );

        $this->white_list_positive = array_map(
            $cleaner,
            explode(",", (string) $this->fromSettingsTable('common', "suffix_custom_white_list", ''))
        );

        $this->white_list_overall = array_merge($this->white_list_default, $this->white_list_positive);
        $this->white_list_overall = array_diff($this->white_list_overall, $this->white_list_negative);
        $this->white_list_overall = array_diff($this->white_list_overall, $this->black_list_overall);
        $this->white_list_overall[] = '';
        $this->white_list_overall = array_unique($this->white_list_overall);
        $this->white_list_overall = array_diff($this->white_list_overall, $this->black_list_prohibited);
    }

    public function getWhiteListedSuffixes(): array
    {
        if ($this->white_list_overall !== null) {
            return $this->white_list_overall;
        }
        $this->read();
        return $this->white_list_overall;
    }

    public function getBlackListedSuffixes(): array
    {
        if ($this->black_list_overall !== null) {
            return $this->black_list_overall;
        }
        $this->read();
        return $this->black_list_overall;
    }

    private function readBlackList(): void
    {
        $cleaner = $this->getCleaner();

        $this->black_list_prohibited = array_map(
            $cleaner,
            explode(",", (string) $this->fromSettingsTable('common', "suffix_custom_expl_black", ''))
        );

        $this->black_list_prohibited = array_filter($this->black_list_prohibited, fn($item): bool => $item !== '');
        $this->black_list_overall = $this->black_list_prohibited;
    }

    public function getDefaultWhitelist(): array
    {
        return $this->white_list_default;
    }

    public function getWhiteListNegative(): array
    {
        if (isset($this->white_list_negative)) {
            return $this->white_list_negative;
        }
        $this->read();
        return $this->white_list_negative;
    }

    public function getWhiteListPositive(): array
    {
        if (isset($this->white_list_positive)) {
            return $this->white_list_positive;
        }
        $this->read();
        return $this->white_list_positive;
    }

    public function getProhibited(): array
    {
        if (isset($this->black_list_prohibited)) {
            return $this->black_list_prohibited;
        }
        $this->read();
        return $this->black_list_prohibited;
    }
}
