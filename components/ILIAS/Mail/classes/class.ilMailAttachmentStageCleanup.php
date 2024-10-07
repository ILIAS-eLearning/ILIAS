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

final class ilMailAttachmentStageCleanup
{
    private const OLD_FILE_MTIME_EXPRESSION = '1 day ago';

    public function __construct(private readonly ilLogger $logger, private readonly ilFileDataMail $mail_file_manager)
    {
    }

    public function run(): void
    {
        $right_interval = (new DateTimeImmutable(self::OLD_FILE_MTIME_EXPRESSION))->format('U');

        $iter = new CallbackFilterIterator(
            new RegexIterator(
                new DirectoryIterator($this->mail_file_manager->getMailPath()),
                '/^' . $this->mail_file_manager->user_id . '_/'
            ),
            function (SplFileInfo $file) use ($right_interval): bool {
                if (!$file->isFile()) {
                    return false;
                }

                return (int) $file->getMTime() < (int) $right_interval;
            }
        );

        $filesystem = \ILIAS\Filesystem\Util\LegacyPathHelper::deriveFilesystemFrom(
            $this->mail_file_manager->getMailPath()
        );

        foreach ($iter as $file) {
            /** @var SplFileInfo $file */
            if (str_starts_with($file->getFilename(), $this->mail_file_manager->user_id . '_')) {
                try {
                    $relative_path = 'mail/' . $file->getFilename();
                    if ($filesystem->has($relative_path)) {
                        $filesystem->delete($relative_path);
                        $this->logger->info('Deleting file from attachment stage: ' . $file->getPathname());
                    }
                } catch (Exception) {
                    $this->logger->error('Error deleting file from attachment stage: ' . $file->getPathname());
                }
            }
        }
    }
}
