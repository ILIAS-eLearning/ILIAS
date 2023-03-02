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

class BlacklistExtensionPreProcessor extends AbstractRecursiveZipPreProcessor implements PreProcessor
{
    private string $reason;
    /**
     * @var string[]
     */
    private array $blacklist;

    /**
     * BlacklistExtensionPreProcessor constructor.
     * Example:
     * ['jpg', 'svg', 'png', '']
     * Matches:
     * example.jpg
     * example.svg
     * example.png
     * example
     * No Match:
     * example.apng
     * example.png.exe
     * ...
     *
     * @param \string[] $blacklist The file extensions which should be blacklisted.
     */
    public function __construct(array $blacklist, string $reason = 'Extension is blacklisted.')
    {
        $this->blacklist = $blacklist;
        $this->reason = $reason;
    }

    protected function checkPath(string $path): bool
    {
        $extension = $this->getExtensionForFilename($path);
        $in_array = in_array($extension, $this->blacklist, true);
        if ($in_array) {
            $this->reason = $this->reason .= " ($path)";
            return false;
        }
        return true;
    }

    protected function getRejectionMessage(): string
    {
        return $this->reason;
    }

    protected function getOKMessage(): string
    {
        return 'Extension is not blacklisted.';
    }

    private function getExtensionForFilename(string $filename): string
    {
        $extensions = explode('.', $filename);

        return count($extensions) <= 1 ? '' : strtolower(end($extensions));
    }
}
