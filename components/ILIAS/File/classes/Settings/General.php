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

namespace ILIAS\Modules\File\Settings;

use ILIAS\Administration\Setting;
use ILIAS\ResourceStorage\Flavour\Engine\GDEngine;
use ILIAS\ResourceStorage\Flavour\Engine\ImagickEngine;
use ILIAS\UI\Component\Input\Field\Group;
use ilSetting;
use ILIAS\UI\Component\Input\Field\Section;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class General extends ilSetting implements Setting
{
    public const MODULE_NAME = 'file_access';
    public const F_DOWNLOAD_WITH_UPLOADED_FILENAME = 'download_with_uploaded_filename';
    public const F_BG_LIMIT = 'bg_limit';
    public const F_INLINE_FILE_EXTENSIONS = 'inline_file_extensions';
    public const F_SHOW_AMOUNT_OF_DOWNLOADS = 'show_amount_of_downloads';
    public const F_DOWNLOAD_ASCII_FILENAME = 'download_ascii_filename';
    private const SEPARATOR = ' ';

    private array $default_inline_extensions = [
        'gif',
        'jpg',
        'jpeg',
        'mp3',
        'pdf',
        'png',
    ];


    public function __construct()
    {
        parent::__construct(self::MODULE_NAME, false);
    }

    public function isDownloadWithAsciiFileName(): bool
    {
        return $this->strToBool($this->get(self::F_DOWNLOAD_ASCII_FILENAME, '1'));
    }

    public function setDownloadWithAsciiFileName(bool $value): void
    {
        $this->set(self::F_DOWNLOAD_ASCII_FILENAME, $this->boolToStr($value));
    }

    public function isShowAmountOfDownloads(): bool
    {
        return $this->strToBool($this->get(self::F_SHOW_AMOUNT_OF_DOWNLOADS, '1'));
    }

    public function setShowAmountOfDownloads(bool $value): void
    {
        $this->set(self::F_SHOW_AMOUNT_OF_DOWNLOADS, $this->boolToStr($value));
    }

    public function setInlineFileExtensions(array $extensions)
    {
        $extensions = array_map(function (string $extension): string {
            return strtolower(trim($extension, " \t\n\r\0\x0B,"));
        }, $extensions);

        $this->set(self::F_INLINE_FILE_EXTENSIONS, $this->arrayToStr($extensions));
    }

    public function getInlineFileExtensions(): array
    {
        return $this->strToArray(
            $this->get(
                self::F_INLINE_FILE_EXTENSIONS,
                $this->arrayToStr($this->default_inline_extensions)
            )
        );
    }

    public function isDownloadWithUploadedFileName(): bool
    {
        return $this->strToBool($this->get(self::F_DOWNLOAD_WITH_UPLOADED_FILENAME, '0'));
    }

    public function setDownloadWithUploadedFileName(bool $value): void
    {
        $this->set(self::F_DOWNLOAD_WITH_UPLOADED_FILENAME, $this->boolToStr($value));
    }

    public function getDownloadLimitinMB(): int
    {
        return $this->strToInt($this->get(self::F_BG_LIMIT, '200'));
    }

    public function setDownloadLimitInMB(int $limit): void
    {
        $this->set(self::F_BG_LIMIT, $this->intToStr($limit));
    }

    // HELPERS

    private function strToBool(string $value): bool
    {
        return $value === '1';
    }

    private function boolToStr(bool $value): string
    {
        return $value ? '1' : '0';
    }

    private function intToStr(int $int): string
    {
        return (string) $int;
    }

    private function strToInt(string $str): int
    {
        return (int) $str;
    }

    private function arrayToStr(array $array): string
    {
        return implode(self::SEPARATOR, $array);
    }

    private function strToArray(string $str): array
    {
        return explode(self::SEPARATOR, $str);
    }
}
