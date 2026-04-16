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

namespace ILIAS\Environment\Configuration\Server;

use ILIAS\Data\DataSize;

/**
 * Read access to PHP runtime and server configuration.
 *
 * Covers: PHP runtime identity, error handling, memory/upload limits, execution constraints, OS.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface ServerConfiguration
{
    // — PHP runtime —

    public function getPhpVersion(): string;

    public function getPhpSapi(): string;

    public function isFpm(): bool;

    /**
     * Returns the escaped path to the PHP CLI binary.
     */
    public function getPhpBinary(): string;

    // — Error handling —

    public function getErrorReportingLevel(): int;

    public function isErrorLoggingEnabled(): bool;

    public function isErrorDisplayEnabled(): bool;

    // — Memory & upload limits —

    public function getMemoryLimit(): DataSize;

    /**
     * Effective upload size limit: min(post_max_size, upload_max_filesize).
     */
    public function getUploadSizeLimit(): DataSize;

    public function getPostMaxSize(): DataSize;

    public function getUploadMaxFilesize(): DataSize;

    // — Execution constraints —

    /**
     * Returns max_execution_time in seconds. 0 means no limit.
     */
    public function getMaxExecutionTime(): int;

    public function getMaxInputVars(): int;

    // — Operating system —

    public function getOperatingSystem(): string;

    public function getOperatingSystemFamily(): string;
}
