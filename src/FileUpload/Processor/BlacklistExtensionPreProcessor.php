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

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class BlacklistExtensionPreProcessor
 * PreProcessor which denies all blacklisted file extensions.
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class BlacklistExtensionPreProcessor extends AbstractRecursiveZipPreProcessor implements PreProcessor
{

    /**
     * @var string
     */
    private $reason;
    /**
     * @var string[]
     */
    private $blacklist;

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
     * @param \string[] $blacklist The file extensions which should be blacklisted.
     * @param string    $reason
     */
    public function __construct(array $blacklist, $reason = 'Extension is blacklisted.')
    {
        $this->blacklist = $blacklist;
        $this->reason = $reason;
    }

    protected function checkPath(string $path) : bool
    {
        $extension = $this->getExtensionForFilename($path);
        $in_array = in_array($extension, $this->blacklist, true);
        // Regular expression pattern to match PHP file extensions, see https://mantis.ilias.de/view.php?id=0028626
        if ($in_array === true || preg_match('/^ph(p[3457]?|t|tml)$/i', $extension)) {
            $this->reason = $this->reason .= " ($path)";
            return false;
        }
        return true;
    }

    protected function getRejectionMessage() : string
    {
        return $this->reason;
    }

    protected function getOKMessage() : string
    {
        return 'Extension is not blacklisted.';
    }

    private function getExtensionForFilename($filename)
    {
        $extensions = explode('.', $filename);

        if (count($extensions) <= 1) {
            $extension = '';
        } else {
            $extension = strtolower(end($extensions));
        }

        return $extension;
    }
}
