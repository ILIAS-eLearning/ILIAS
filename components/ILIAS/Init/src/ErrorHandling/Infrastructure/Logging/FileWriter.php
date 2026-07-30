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

namespace ILIAS\Init\ErrorHandling\Infrastructure\Logging;

use Whoops\Exception\Inspector;
use ILIAS\Init\ErrorHandling\Application\ErrorLogFileStorage;
use ILIAS\Init\ErrorHandling\Logging\FileHandler;

class FileWriter implements ErrorLogFileStorage
{
    public function __construct(
        private readonly FileHandler $file_handler,
        private readonly ContentProcessor $content_processor
    ) {
    }

    /**
     * @param list<string> $sensitive_parameter_names
     */
    public function write(
        Inspector $inspector,
        string $directory,
        string $file_name,
        array $sensitive_parameter_names
    ): void {
        $this->file_handler->createFile(
            $directory,
            $file_name,
            $this->content_processor->collectAndFormatContent($inspector, $sensitive_parameter_names),
        );
    }
}
