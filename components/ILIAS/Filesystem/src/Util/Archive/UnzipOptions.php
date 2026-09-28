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

namespace ILIAS\Filesystem\Util\Archive;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class UnzipOptions extends Options
{
    /**
     * @description Disables a numeric limit (ratio, entry-count or size). A value of 0 means "no limit".
     */
    public const UNLIMITED = 0;

    /**
     * @description Reject archives whose overall uncompressed/compressed ratio exceeds this value.
     * A deflate decompression bomb reaches ~1000:1, while legitimate archives (already compressed
     * media, office documents, ...) stay well below 100:1.
     */
    public const DEFAULT_MAX_COMPRESSION_RATIO = 100;

    /**
     * @description The ratio check is only applied once the total uncompressed size exceeds this
     * floor, so small but highly compressible archives are never rejected.
     */
    public const DEFAULT_RATIO_CHECK_MIN_UNCOMPRESSED_SIZE = 33554432; // 32 MiB

    /**
     * @description Reject archives with more than this many entries (entry-count bomb).
     */
    public const DEFAULT_MAX_AMOUNT_OF_ENTRIES = 100000;

    /**
     * @description Reject archives whose total uncompressed size exceeds this value. Without an
     * absolute ceiling the ratio alone allows DEFAULT_MAX_COMPRESSION_RATIO times the upload limit
     * to be written, which grows with post_max_size. 4 GiB is the point where an archive requires
     * Zip64; legitimate ILIAS imports stay well below it. Installations that really do import
     * larger archives can raise or disable the limit per call.
     */
    public const DEFAULT_MAX_UNCOMPRESSED_SIZE = 4294967296; // 4 GiB

    protected ?string $zip_output_path = null;
    private bool $flat = false;
    private bool $overwrite = false;
    private int $max_compression_ratio = self::DEFAULT_MAX_COMPRESSION_RATIO;
    private int $ratio_check_min_uncompressed_size = self::DEFAULT_RATIO_CHECK_MIN_UNCOMPRESSED_SIZE;
    private int $max_amount_of_entries = self::DEFAULT_MAX_AMOUNT_OF_ENTRIES;
    private int $max_uncompressed_size = self::DEFAULT_MAX_UNCOMPRESSED_SIZE;

    public function getZipOutputPath(): ?string
    {
        return $this->zip_output_path;
    }

    public function withZipOutputPath(string $zip_output_path): self
    {
        $clone = clone $this;
        $clone->zip_output_path = $zip_output_path;
        return $clone;
    }

    public function isOverwrite(): bool
    {
        return $this->overwrite;
    }

    public function withOverwrite(bool $overwrite): self
    {
        $clone = clone $this;
        $clone->overwrite = $overwrite;
        return $clone;
    }

    public function getMaxCompressionRatio(): int
    {
        return $this->max_compression_ratio;
    }

    public function withMaxCompressionRatio(int $max_compression_ratio): self
    {
        $clone = clone $this;
        $clone->max_compression_ratio = max(self::UNLIMITED, $max_compression_ratio);
        return $clone;
    }

    public function getRatioCheckMinUncompressedSize(): int
    {
        return $this->ratio_check_min_uncompressed_size;
    }

    public function withRatioCheckMinUncompressedSize(int $ratio_check_min_uncompressed_size): self
    {
        $clone = clone $this;
        $clone->ratio_check_min_uncompressed_size = max(0, $ratio_check_min_uncompressed_size);
        return $clone;
    }

    public function getMaxAmountOfEntries(): int
    {
        return $this->max_amount_of_entries;
    }

    public function withMaxAmountOfEntries(int $max_amount_of_entries): self
    {
        $clone = clone $this;
        $clone->max_amount_of_entries = max(self::UNLIMITED, $max_amount_of_entries);
        return $clone;
    }

    public function getMaxUncompressedSize(): int
    {
        return $this->max_uncompressed_size;
    }

    public function withMaxUncompressedSize(int $max_uncompressed_size): self
    {
        $clone = clone $this;
        $clone->max_uncompressed_size = max(self::UNLIMITED, $max_uncompressed_size);
        return $clone;
    }

}
