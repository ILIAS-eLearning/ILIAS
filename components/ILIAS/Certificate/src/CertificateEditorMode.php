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

namespace ILIAS\Certificate;

/**
 * What the current subject may do in the certificate editor.
 * The issuing component decides this. The editor does not resolve repository permissions.
 */
enum CertificateEditorMode
{
    case Read;
    case Write;

    public function canWrite(): bool
    {
        return $this === self::Write;
    }

    public function allowsCommand(string $command): bool
    {
        if ($this->canWrite()) {
            return true;
        }

        return !in_array($command, self::writeCommands(), true);
    }

    /**
     * @return list<string>
     */
    private static function writeCommands(): array
    {
        return [
            'certificateSave',
            'certificateDelete',
            'certificateDeleteConfirm',
            'certificateImport',
            'certificateUpload',
        ];
    }
}
