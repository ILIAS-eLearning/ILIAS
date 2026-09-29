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

use ILIAS\Database\Connection;
use ILIAS\FileServices\Policy\UploadRestrictionBypass;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class DatabaseBackedFilesystemConfig implements FilesystemConfig
{
    public const string MODULE_NAME = 'file_access';
    public const string F_BG_LIMIT = 'bg_limit';
    public const string F_INLINE_FILE_EXTENSIONS = 'inline_file_extensions';
    public const string F_SHOW_AMOUNT_OF_DOWNLOADS = 'show_amount_of_downloads';
    public const string F_DOWNLOAD_ASCII_FILENAME = 'download_ascii_filename';
    public const string F_BYPASS = 'bypass';

    private array $resolved_values = [];
    private ?array $white_list_negative = null;
    private ?array $white_list_positive = null;
    private ?array $white_list_overall = null;
    private ?array $black_list_prohibited = null;
    private ?array $black_list_overall = null;
    private ?array $white_list_default = null;

    public function __construct(
        private readonly Connection $db,
        private readonly UploadRestrictionBypass $bypass,
    ) {
    }

    /**
     * Lazily includes the default whitelist on first access. Done outside the
     * constructor so building this config (e.g. during bootstrap) does not
     * eagerly hit the filesystem; the include runs at most once.
     */
    private function defaultWhitelist(): array
    {
        return $this->white_list_default ??= include __DIR__ . "/../../../FileServices/defaults/default_whitelist.php";
    }

    public function isByPassAllowedForCurrentUser(): bool
    {
        return $this->bypass->isGrantedToCurrentUser();
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

    private function read(): void
    {
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

        $this->white_list_overall = array_merge($this->defaultWhitelist(), $this->white_list_positive);
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
        return $this->defaultWhitelist();
    }

    public function getWhiteListNegative(): array
    {
        if ($this->white_list_negative !== null) {
            return $this->white_list_negative;
        }
        $this->read();
        return $this->white_list_negative;
    }

    public function getWhiteListPositive(): array
    {
        if ($this->white_list_positive !== null) {
            return $this->white_list_positive;
        }
        $this->read();
        return $this->white_list_positive;
    }

    public function getProhibited(): array
    {
        if ($this->black_list_prohibited !== null) {
            return $this->black_list_prohibited;
        }
        $this->read();
        return $this->black_list_prohibited;
    }
}
