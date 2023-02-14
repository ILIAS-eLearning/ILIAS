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

namespace ILIAS\FileUpload\Processor;

/**
 * Class InsecureFilenameSanitizerPreProcessor
 *
 * PreProcessor which chechs for file with potentially dangerous names
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class InsecureFilenameSanitizerPreProcessor extends AbstractRecursiveZipPreProcessor implements PreProcessor
{
    private $prohibited_names = [
        '...'
    ];

    protected function checkPath(string $path) : bool
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        $path = trim($path, '/');
        $parts = explode('/', $path);
        foreach ($parts as $part) {
            if (in_array($part, $this->prohibited_names)) {
                return false;
            }
        }
        return true;
    }

    protected function getRejectionMessage() : string
    {
        return 'A Security Issue has been detected, File-upload aborted...';
    }

    protected function getOKMessage() : string
    {
        return 'Extension is not blacklisted.';
    }
}
