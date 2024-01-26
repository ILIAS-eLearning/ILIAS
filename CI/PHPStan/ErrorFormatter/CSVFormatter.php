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

namespace ILIAS\CI\PHPStan\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\Output;

class CSVFormatter implements ErrorFormatter
{
    private const COMPONENT_REGEX = '/.*(Modules|Services|src)\/(.*?)\/.*/m';
    private const H_COMPONENT = 'Component';
    private const H_CLASS = 'Filename';
    private const H_LINE = 'Line';
    private const H_MESSAGE = 'Used Implementation';
    private const UNKNOWN = 'Unknown';
    private const H_RULE = 'Rule';
    private const H_VERSION = 'Version';

    private array $csv_headers = [
        self::H_COMPONENT,
        self::H_CLASS,
        self::H_VERSION,
        self::H_LINE,
        self::H_RULE,
        self::H_MESSAGE
    ];


    public function formatErrors(AnalysisResult $analysisResult, Output $output): int
    {
        $getcwd = getcwd();
        $output->writeLineFormatted(implode(';', $this->csv_headers));

        foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
            $filename = str_replace($getcwd, '', $fileSpecificError->getFile());
            if (preg_match(self::COMPONENT_REGEX, $filename, $matches)) {
                $component = $matches[1] . '/' . $matches[2];
            } else {
                $component = self::UNKNOWN;
            }

            $result = [
                self::H_COMPONENT => $component,
                self::H_CLASS => basename($fileSpecificError->getFile()),
                self::H_VERSION => $fileSpecificError->getMetadata()['version'] ?? self::UNKNOWN,
                self::H_LINE => $fileSpecificError->getLine(),
                self::H_RULE => $fileSpecificError->getMetadata()['rule'] ?? self::UNKNOWN,
                self::H_MESSAGE => $fileSpecificError->getMessage()
            ];
            $output->writeLineFormatted(implode(';', $result));
        }
        return $analysisResult->hasErrors() ? 1 : 0;
    }
}
