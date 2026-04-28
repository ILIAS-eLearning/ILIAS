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

namespace ILIAS\Environment\Configuration\Instance;

/**
 * Typed read access to ilias.ini.php.
 *
 * Sections: server, clients, tools, log, https
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * @deprecated Try to avoid consuming this directly. Use or introduce more specific Interfaces, e.g.
 * \ILIAS\Environment\Configuration\Instance\Directories and move to other components.
 * This will help to reactor configuration in the future.
 */
interface IliasIni
{
    // [server]
    public function getHttpPath(): string;
    public function getAbsolutePath(): string;
    public function getTimezone(): string;

    // [clients]
    public function getClientsPath(): string;
    public function getClientIniFile(): string;
    public function getDataDirectory(): string;
    public function getDefaultClientId(): string;

    // [log]
    public function getLogPath(): string;
    public function getLogFile(): string;
    public function isLogEnabled(): bool;
    public function getDefaultLogLevel(): string;
    public function getLogErrorPath(): string;

    // [tools]
    public function getConvertPath(): string;
    public function getZipPath(): string;
    public function getUnzipPath(): string;
    public function getJavaPath(): string;
    public function getFfmpegPath(): string;
    public function getGhostscriptPath(): string;
    public function getFopPath(): string;
    public function getLatexPath(): string;

    // [https]
    public function isAutoHttpsDetectEnabled(): bool;
    public function getAutoHttpsDetectHeaderName(): string;
    public function getAutoHttpsDetectHeaderValue(): string;
}
