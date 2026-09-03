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

namespace ILIAS\Filesystem\Upload;

use ILIAS\FileUpload\Processor\AbstractRecursiveZipPreProcessor;

class InsecureFilenameSanitizerPreProcessor extends AbstractRecursiveZipPreProcessor
{
    private array $prohibited_names = ['...'];

    protected function checkPath(string $path): bool
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        $path = trim((string) $path, '/');
        return array_all(explode('/', $path), fn($part): bool => !in_array($part, $this->prohibited_names, true));
    }

    protected function getRejectionMessage(): string
    {
        return 'A Security Issue has been detected, File-upload aborted...';
    }

    protected function getOKMessage(): string
    {
        return 'Extension is not blacklisted.';
    }
}
