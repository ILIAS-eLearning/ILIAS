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

namespace ILIAS\WebDAV\Entity;

use Sabre\DAV\IFile;
use Sabre\DAV\Exception\Forbidden;
use ilLanguage;

/**
 * Virtual text file shown in every container that lists ILIAS objects which
 * cannot be exposed via WebDAV (forbidden characters, duplicate titles,
 * info-file name collisions). Read-only; cannot be written, renamed, deleted.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ProblemInfoFile implements IFile
{
    public const string FILE_NAME = '#!_WEBDAV_INFORMATION.txt';

    public function __construct(
        /** @var string[] */
        private array $duplicate_titles,
        /** @var string[] */
        private array $forbidden_titles,
        private bool $info_name_collision,
        private ilLanguage $lang
    ) {
    }

    public function hasProblems(): bool
    {
        return $this->info_name_collision
            || $this->duplicate_titles !== []
            || $this->forbidden_titles !== [];
    }

    public function getName(): string
    {
        return self::FILE_NAME;
    }

    public function setName($name): void
    {
        throw new Forbidden('The error info file cannot be renamed');
    }

    public function getLastModified(): int
    {
        return time();
    }

    public function put($data): void
    {
        throw new Forbidden('The error info file is virtual and cannot be overwritten');
    }

    public function get(): string
    {
        $message = '';

        if ($this->info_name_collision) {
            $message .= '# ' . $this->lang->txt('webdav_problem_info_duplicate') . "\n\n";
        }

        if ($this->duplicate_titles !== []) {
            $message .= '# ' . $this->lang->txt('webdav_duplicate_detected_title') . "\n";
            foreach ($this->duplicate_titles as $title) {
                $message .= $title . "\n";
            }
            $message .= "\n";
        }

        if ($this->forbidden_titles !== []) {
            $message .= '# ' . $this->lang->txt('webdav_forbidden_chars_title') . "\n";
            foreach ($this->forbidden_titles as $title) {
                $message .= $title . "\n";
            }
            $message .= "\n";
        }

        if ($message === '') {
            return $this->lang->txt('webdav_problem_free_container');
        }

        return $message;
    }

    public function getSize(): int
    {
        return strlen($this->get());
    }

    public function getContentType(): string
    {
        return 'text/plain';
    }

    public function getETag(): ?string
    {
        return null;
    }

    public function delete(): void
    {
        throw new Forbidden('The error info file cannot be deleted');
    }
}
