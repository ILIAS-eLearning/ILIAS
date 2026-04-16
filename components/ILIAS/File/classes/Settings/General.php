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

namespace ILIAS\components\File\Settings;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class General
{
    public const MODULE_NAME = 'file_access';
    public const F_BG_LIMIT = 'bg_limit';
    public const F_INLINE_FILE_EXTENSIONS = 'inline_file_extensions';
    public const F_SHOW_AMOUNT_OF_DOWNLOADS = 'show_amount_of_downloads';
    public const F_DOWNLOAD_ASCII_FILENAME = 'download_ascii_filename';
    /**
     * @var string
     */
    private const SEPARATOR = ' ';

    private array $default_inline_extensions = [
        'gif',
        'jpg',
        'jpeg',
        'mp3',
        'pdf',
        'png',
    ];

    private array $setting = [];

    public function __construct(private \ilDBInterface $db)
    {
        $this->read();
    }

    public function read(): void
    {
        try {
            $res = $this->db->queryF(
                "SELECT * FROM settings WHERE module = %s",
                ['text'],
                [self::MODULE_NAME]
            );

            while ($row = $this->db->fetchAssoc($res)) {
                $this->setting[$row["keyword"]] = $row["value"];
            }
        } catch (\Throwable) {

        }
    }

    public function get(
        string $keyword,
        ?string $default_value = null
    ): ?string {
        return $this->setting[$keyword] ?? $default_value;
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

    public function setInlineFileExtensions(array $extensions): void
    {
        $extensions = array_map(
            fn(string $extension): string => strtolower(trim($extension, " \t\n\r\0\x0B,")),
            $extensions
        );

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

    public function getDownloadLimitinMB(): int
    {
        return $this->strToInt($this->get(self::F_BG_LIMIT, '200'));
    }

    public function setDownloadLimitInMB(int $limit): void
    {
        $this->set(self::F_BG_LIMIT, $this->intToStr($limit));
    }

    // HELPERS

    private function strToBool(?string $value): bool
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

    private function strToInt(?string $str): int
    {
        return (int) $str;
    }

    private function arrayToStr(array $array): string
    {
        return implode(self::SEPARATOR, $array);
    }

    private function strToArray(?string $str): array
    {
        return explode(self::SEPARATOR, (string) $str);
    }
}
