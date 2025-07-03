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

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\DTO\UploadResult;

class ilFileDataMail extends ilFileData
{
    public string $mail_path;
    protected int $mail_max_upload_file_size;
    protected Filesystem $tmp_directory;
    protected Filesystem $storage_directory;
    protected ilDBInterface $db;
    protected ILIAS $ilias;

    public function __construct(public int $user_id = 0)
    {
        global $DIC;

        if (!defined('MAILPATH')) {
            define('MAILPATH', 'mail');
        }
        parent::__construct();
        $this->mail_path = $this->getPath() . '/' . MAILPATH;
        $this->ilias = $DIC['ilias'];
        $this->db = $DIC->database();
        $this->tmp_directory = $DIC->filesystem()->temp();
        $this->storage_directory = $DIC->filesystem()->storage();

        $this->checkReadWrite();
        $this->initAttachmentMaxUploadSize();
    }

    public function initDirectory(): bool
    {
        if (is_writable($this->getPath())
            && mkdir($this->getPath() . '/' . MAILPATH)
            && chmod($this->getPath() . '/' . MAILPATH, 0755)) {
            $this->mail_path = $this->getPath() . '/' . MAILPATH;
            return true;
        }

        return false;
    }

    public function getUploadLimit(): int
    {
        return $this->mail_max_upload_file_size;
    }

    public function getAttachmentsTotalSizeLimit(): ?float
    {
        $max_size = $this->ilias->getSetting('mail_maxsize_attach', '');
        if ($max_size === '') {
            return null;
        }

        return (float) $this->ilias->getSetting('mail_maxsize_attach', '0') * 1024;
    }

    public function getMailPath(): string
    {
        return $this->mail_path;
    }

    public function getAbsoluteAttachmentPoolPathPrefix(): string
    {
        return $this->mail_path . '/' . $this->user_id . '_';
    }

    /**
     * @return array{path: string, filename: string} An array containing 'path' and 'filename' for the passed MD5 hash
     * @throws OutOfBoundsException
     */
    public function getAttachmentPathAndFilenameByMd5Hash(string $md5FileHash, int $mail_id): array
    {
        $res = $this->db->queryF(
            'SELECT path FROM mail_attachment WHERE mail_id = %s',
            ['integer'],
            [$mail_id]
        );

        if ($this->db->numRows($res) !== 1) {
            throw new OutOfBoundsException();
        }

        $row = $this->db->fetchAssoc($res);

        $relative_path = $row['path'];
        $path = $this->getMailPath() . '/' . $row['path'];

        $files = ilFileUtils::getDir($path);
        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $md5FileHash) {
                return [
                    'path' => $this->getMailPath() . '/' . $relative_path . '/' . $file['entry'],
                    'filename' => $file['entry'],
                ];
            }
        }

        throw new OutOfBoundsException();
    }


    private function getAttachmentPathByMailId(int $mail_id): string
    {
        $query = $this->db->query(
            'SELECT path FROM mail_attachment WHERE mail_id = ' . $this->db->quote($mail_id, 'integer')
        );

        while ($row = $this->db->fetchObject($query)) {
            return $row->path;
        }

        return '';
    }

    public function getAttachmentPath(string $a_filename, int $a_mail_id): string
    {
        $path = $this->getMailPath() . '/' . $this->getAttachmentPathByMailId($a_mail_id) . '/' . $a_filename;

        if (is_readable($path)) {
            return $path;
        }

        return '';
    }

    /**
     * @param list<string> $a_attachments
     */
    public function adoptAttachments(array $a_attachments, int $a_mail_id): string
    {
        foreach ($a_attachments as $file) {
            $path = $this->getAttachmentPath($file, $a_mail_id);
            if (!copy($path, $this->getMailPath() . '/' . $this->user_id . '_' . $file)) {
                return 'ERROR: ' . $this->getMailPath() . '/' . $this->user_id . '_' . $file . ' cannot be created';
            }
        }

        return '';
    }

    public function checkReadWrite(): bool
    {
        if (is_writable($this->mail_path) && is_readable($this->mail_path)) {
            return true;
        }

        $this->ilias->raiseError(
            'Mail directory is not readable/writable by webserver: ' .
            $this->mail_path,
            $this->ilias->error_obj->FATAL
        );

        return false;
    }

    /**
     * @return list<array{name: string, size: int, ctime: int}>
     */
    public function getUserFilesData(): array
    {
        return $this->getUnsentFiles();
    }

    /**
     * @return list<array{name: string, size: int, ctime: int}>
     */
    private function getUnsentFiles(): array
    {
        $files = [];

        $iter = new RegexIterator(new DirectoryIterator($this->mail_path), "/^{$this->user_id}_(.+)$/");
        foreach ($iter as $file) {
            /** @var SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            [$uid, $rest] = explode('_', $file->getFilename(), 2);
            if ($uid === (string) $this->user_id) {
                $files[] = [
                    'name' => $rest,
                    'size' => $file->getSize(),
                    'ctime' => $file->getCTime(),
                ];
            }
        }

        return $files;
    }

    public function storeAsAttachment(string $a_filename, string $a_content): string
    {
        if (strlen($a_content) >= $this->getUploadLimit()) {
            throw new DomainException(
                sprintf(
                    'Mail upload limit reached for user with id %s',
                    $this->user_id
                )
            );
        }

        $name = ilFileUtils::_sanitizeFilemame($a_filename);
        $this->rotateFiles($this->getMailPath() . '/' . $this->user_id . '_' . $name);

        $abs_path = $this->getMailPath() . '/' . $this->user_id . '_' . $name;

        $fp = fopen($abs_path, 'wb+');
        if (!is_resource($fp)) {
            throw new RuntimeException(
                sprintf(
                    'Could not read file: %s',
                    $abs_path
                )
            );
        }

        if (fwrite($fp, $a_content) === false) {
            fclose($fp);
            throw new RuntimeException(
                sprintf(
                    'Could not write file: %s',
                    $abs_path
                )
            );
        }

        fclose($fp);

        return $name;
    }

    public function storeUploadedFile(UploadResult $result): string
    {
        $filename = ilFileUtils::_sanitizeFilemame(
            $result->getName()
        );

        $this->rotateFiles($this->getMailPath() . '/' . $this->user_id . '_' . $filename);

        ilFileUtils::moveUploadedFile(
            $result->getPath(),
            $filename,
            $this->getMailPath() . '/' . $this->user_id . '_' . $filename
        );

        return $filename;
    }

    public function copyAttachmentFile(string $a_abs_path, string $a_new_name): bool
    {
        @copy($a_abs_path, $this->getMailPath() . '/' . $this->user_id . '_' . $a_new_name);

        return true;
    }

    private function rotateFiles(string $a_path): bool
    {
        if (is_file($a_path)) {
            $this->rotateFiles($a_path . '.old');
            return ilFileUtils::rename($a_path, $a_path . '.old');
        }

        return true;
    }

    /**
     * @param list<string> $a_filenames
     */
    public function unlinkFiles(array $a_filenames): string
    {
        foreach ($a_filenames as $file) {
            if (!$this->unlinkFile($file)) {
                return $file;
            }
        }

        return '';
    }

    public function unlinkFile(string $a_filename): bool
    {
        if (is_file($this->mail_path . '/' . basename($this->user_id . '_' . $a_filename))) {
            return unlink($this->mail_path . '/' . basename($this->user_id . '_' . $a_filename));
        }

        return false;
    }

    /**
     * Resolves a path for a passed filename in regards of a user's mail attachment pool,
     * meaning attachments not being sent
     */
    public function getAbsoluteAttachmentPoolPathByFilename(string $filename): string
    {
        return $this->getAbsoluteAttachmentPoolPathPrefix() . $filename;
    }

    /**
     * Saves all attachment files in a specific mail directory .../mail/<calculated_path>/mail_<mail_id>_<user_id>/...
     * @param list<string> $a_attachments
     */
    public function saveFiles(int $a_mail_id, array $a_attachments): void
    {
        if (!is_numeric($a_mail_id) || $a_mail_id < 1) {
            throw new InvalidArgumentException('The passed mail_id must be a valid integer!');
        }

        foreach ($a_attachments as $attachment) {
            $this->saveFile($a_mail_id, $attachment);
        }
    }

    public static function getStorage(int $a_mail_id, int $a_usr_id): ilFSStorageMail
    {
        static $fsstorage_cache = [];

        $fsstorage_cache[$a_mail_id][$a_usr_id] = new ilFSStorageMail($a_mail_id, $a_usr_id);

        return $fsstorage_cache[$a_mail_id][$a_usr_id];
    }

    /**
     * Save attachment file in a specific mail directory .../mail/<calculated_path>/mail_<mail_id>_<user_id>/...
     */
    public function saveFile(int $a_mail_id, string $a_attachment): bool
    {
        $storage = self::getStorage($a_mail_id, $this->user_id);
        $storage->create();
        $storage_directory = $storage->getAbsolutePath();

        if (!is_dir($storage_directory)) {
            return false;
        }

        return copy(
            $this->mail_path . '/' . $this->user_id . '_' . $a_attachment,
            $storage_directory . '/' . $a_attachment
        );
    }

    /**
     * @param list<string> $a_files
     */
    public function checkFilesExist(array $a_files): bool
    {
        if ($a_files !== []) {
            foreach ($a_files as $file) {
                if (!is_file($this->mail_path . '/' . $this->user_id . '_' . $file)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function assignAttachmentsToDirectory(int $a_mail_id, int $a_sent_mail_id): void
    {
        $storage = self::getStorage($a_sent_mail_id, $this->user_id);
        $this->db->manipulateF(
            '
			INSERT INTO mail_attachment 
			( mail_id, path) VALUES (%s, %s)',
            ['integer', 'text'],
            [$a_mail_id, $storage->getRelativePathExMailDirectory()]
        );
    }

    public function deassignAttachmentFromDirectory(int $a_mail_id): bool
    {
        $res = $this->db->query(
            'SELECT path FROM mail_attachment WHERE mail_id = ' . $this->db->quote($a_mail_id, 'integer')
        );

        $path = '';
        while ($row = $this->db->fetchObject($res)) {
            $path = (string) $row->path;
        }

        if ($path !== '') {
            $res = $this->db->query(
                'SELECT COUNT(mail_id) count_mail_id FROM mail_attachment WHERE path = ' .
                $this->db->quote($path, 'text')
            ) ;

            $cnt_mail_id = 0;
            while ($row = $this->db->fetchObject($res)) {
                $cnt_mail_id = (int) $row->count_mail_id;
            }

            if ($cnt_mail_id === 1) {
                $this->deleteAttachmentDirectory($path);
            }
        }

        $this->db->manipulateF(
            'DELETE FROM mail_attachment WHERE mail_id = %s',
            ['integer'],
            [$a_mail_id]
        );

        return true;
    }

    private function deleteAttachmentDirectory(string $a_rel_path): void
    {
        ilFileUtils::delDir($this->mail_path . '/' . $a_rel_path);
    }

    protected function initAttachmentMaxUploadSize(): void
    {
        /** @todo mjansen: Unfortunately we cannot reuse the implementation of ilFileInputGUI */

        // Copy of ilFileInputGUI: begin
        // get the value for the maximal uploadable filesize from the php.ini (if available)
        $umf = ini_get('upload_max_filesize');
        // get the value for the maximal post data from the php.ini (if available)
        $pms = ini_get('post_max_size');

        //convert from short-string representation to "real" bytes
        $multiplier_a = ['K' => 1024, 'M' => 1024 * 1024, 'G' => 1024 * 1024 * 1024];

        $umf_parts = preg_split(
            "/(\d+)([K|G|M])/",
            (string) $umf,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        $pms_parts = preg_split(
            "/(\d+)([K|G|M])/",
            (string) $pms,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

        if ((is_countable($umf_parts) ? count($umf_parts) : 0) === 2) {
            $umf = (float) $umf_parts[0] * $multiplier_a[$umf_parts[1]];
        }
        if ((is_countable($pms_parts) ? count($pms_parts) : 0) === 2) {
            $pms = (float) $pms_parts[0] * $multiplier_a[$pms_parts[1]];
        }

        // use the smaller one as limit
        $max_filesize = min($umf, $pms);

        if (!$max_filesize) {
            $max_filesize = max($umf, $pms);
        }

        $this->mail_max_upload_file_size = (int) $max_filesize;
    }

    public function onUserDelete(): void
    {
        // Delete uploaded mail files which are not attached to any message
        try {
            $iter = new RegexIterator(
                new DirectoryIterator($this->getMailPath()),
                '/^' . $this->user_id . '_/'
            );
            foreach ($iter as $file) {
                /** @var SplFileInfo $file */
                if ($file->isFile()) {
                    @unlink($file->getPathname());
                }
            }
        } catch (Exception) {
        }

        // Select all files attached to messages which are not shared (... = 1) with other messages anymore
        $query = '
			SELECT DISTINCT(ma1.path)
			FROM mail_attachment ma1
			INNER JOIN mail
				ON mail.mail_id = ma1.mail_id
			WHERE mail.user_id = %s
			AND (SELECT COUNT(tmp.path) FROM mail_attachment tmp WHERE tmp.path = ma1.path) = 1
		';
        $res = $this->db->queryF(
            $query,
            ['integer'],
            [$this->user_id]
        );
        while ($row = $this->db->fetchAssoc($res)) {
            try {
                $path = $this->getMailPath() . DIRECTORY_SEPARATOR . $row['path'];
                $iter = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($iter as $file) {
                    /** @var SplFileInfo $file */
                    if ($file->isDir()) {
                        @rmdir($file->getPathname());
                    } else {
                        @unlink($file->getPathname());
                    }
                }
                @rmdir($path);
            } catch (Exception) {
            }
        }

        // Delete each mail attachment rows assigned to a message of the deleted user.
        $this->db->manipulateF(
            '
				DELETE
				FROM mail_attachment
				WHERE EXISTS(
					SELECT mail.mail_id
					FROM mail
					WHERE mail.user_id = %s AND mail.mail_id = mail_attachment.mail_id
				)
				',
            ['integer'],
            [$this->user_id]
        );
    }

    /**
     * @param list<string> $files
     */
    public function deliverAttachmentsAsZip(
        string $basename,
        int $mail_id,
        array $files = [],
        bool $is_draft = false
    ): void {
        $path = '';
        if (!$is_draft) {
            $path = $this->getAttachmentPathByMailId($mail_id);
            if ($path === '') {
                throw new ilMailException('mail_download_zip_no_attachments');
            }
        }

        $download_filename = ilFileUtils::getASCIIFilename($basename);
        if ($download_filename === '') {
            $download_filename = 'attachments';
        }

        $processing_directory = ilFileUtils::ilTempnam();
        $relative_processing_directory = basename($processing_directory);

        $absolute_zip_directory = $processing_directory . '/' . $download_filename;
        $relative_zip_directory = $relative_processing_directory . '/' . $download_filename;

        $this->tmp_directory->createDir($relative_zip_directory);

        foreach ($files as $filename) {
            if ($is_draft) {
                $source = str_replace(
                    $this->mail_path,
                    MAILPATH,
                    $this->getAbsoluteAttachmentPoolPathByFilename($filename)
                );
            } else {
                $source = MAILPATH . '/' . $path . '/' . $filename;
            }

            $source = str_replace('//', '/', $source);
            if (!$this->storage_directory->has($source)) {
                continue;
            }

            $target = $relative_zip_directory . '/' . $filename;

            $stream = $this->storage_directory->readStream($source);
            $this->tmp_directory->writeStream($target, $stream);
        }

        $path_to_zip_file = $processing_directory . '/' . $download_filename . '.zip';
        ilFileUtils::zip($absolute_zip_directory, $path_to_zip_file);

        $this->tmp_directory->deleteDir($relative_zip_directory);

        ilFileDelivery::deliverFileAttached(
            $processing_directory . '/' . $download_filename . '.zip',
            ilFileUtils::getValidFilename($download_filename . '.zip')
        );
    }
}
