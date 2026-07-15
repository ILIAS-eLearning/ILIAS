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

use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\Filesystem\Filesystem;
use ILIAS\ResourceStorage\Services;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\Repository\CollectionDBRepository;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\FileDelivery\Delivery as FileDelivery;

class ilFileDataMail extends ilFileData
{
    private const string POOL_RCID_PREF_KEY = 'mail_attachment_pool_rcid';

    public const string LEGACY_POOL_ITEM_PREFIX = 'legacy:';

    public string $mail_path;
    protected int $mail_max_upload_file_size;
    protected Filesystem $tmp_directory;
    protected Filesystem $storage_directory;
    protected ilDBInterface $db;
    protected ILIAS $ilias;
    private readonly Services $irss;
    private readonly ilMailAttachmentStakeholder $stakeholder;

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
        $this->irss = $DIC->resourceStorage();
        $this->stakeholder = new ilMailAttachmentStakeholder();
        $this->stakeholder->setOwner($this->user_id);

        $this->checkReadWrite();
        $this->initAttachmentMaxUploadSize();
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
        $rcid = $this->getRcidForMail($mail_id);
        if ($rcid !== null) {
            $resource_identification = $this->getResourceIdByHash($rcid, $md5FileHash);
            if ($resource_identification === null) {
                throw new OutOfBoundsException();
            }
            $info = $this->irss->manage()->getCurrentRevision($resource_identification)->getInformation();

            return [
                'path' => '',
                'filename' => $info->getTitle(),
                'rcid' => $rcid,
                'md5' => $md5FileHash,
            ];
        }

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


    /**
     * @deprecated Legacy pool write; use uploadToPool() instead.
     */
    public function storeUploadedFile(UploadResult $result): string
    {
        return $this->uploadToPool($result)->serialize();
    }

    public function uploadToPool(UploadResult $result): ResourceIdentification
    {
        $rid = $this->uploadToIrss($result);
        $pool_rcid = $this->resolveUserPoolRcid();
        $updated_rcid = $this->adoptPoolResourcesToCollection($pool_rcid, [$rid]);

        if ($updated_rcid === null) {
            throw new RuntimeException('Could not store mail attachment in pool.');
        }

        if ($pool_rcid === null || $pool_rcid->serialize() !== $updated_rcid->serialize()) {
            $this->persistUserPoolRcid($updated_rcid);
        }

        return $rid;
    }

    public function getUserPoolRcid(): ResourceCollectionIdentification
    {
        $pool_rcid = $this->resolveUserPoolRcid();
        if ($pool_rcid === null) {
            throw new RuntimeException('Mail attachment pool does not exist.');
        }

        return $pool_rcid;
    }

    private function resolveUserPoolRcid(): ?ResourceCollectionIdentification
    {
        $pref = ilObjUser::_lookupPref($this->user_id, self::POOL_RCID_PREF_KEY);
        if (!is_string($pref) || $pref === '') {
            return null;
        }

        $rcid = new ResourceCollectionIdentification($pref);
        if (!$this->collectionIsKnown($rcid)) {
            return null;
        }

        $this->repairCollectionHeaderIfNeeded($rcid);

        return $rcid;
    }

    /**
     * @return \Generator<int, ResourceIdentification>
     */
    private function iteratePoolResourceIdentifications(ResourceCollectionIdentification $pool_rcid): \Generator
    {
        $this->repairCollectionHeaderIfNeeded($pool_rcid);

        foreach ($this->getAssignedRidStrings($pool_rcid) as $rid_string) {
            $resource_identification = $this->irss->manage()->find($rid_string);
            if ($resource_identification !== null) {
                yield $resource_identification;
            }
        }
    }

    /**
     * @return list<array{rid: string, name: string, size: int, ctime: int, md5: string}>
     */
    public function getUserPoolFilesData(): array
    {
        $files = [];

        $pool_rcid = $this->resolveUserPoolRcid();
        if ($pool_rcid !== null) {
            foreach ($this->iteratePoolResourceIdentifications($pool_rcid) as $resource_identification) {
                $revision = $this->irss->manage()->getCurrentRevision($resource_identification);
                $info = $revision->getInformation();
                $files[] = [
                    'rid' => $resource_identification->serialize(),
                    'name' => $info->getTitle(),
                    'size' => $info->getSize(),
                    'ctime' => $info->getCreationDate()->getTimestamp(),
                    'md5' => $revision->getTitle(),
                ];
            }
        }

        foreach ($this->getUnsentFiles() as $file) {
            $files[] = [
                'rid' => self::LEGACY_POOL_ITEM_PREFIX . $file['name'],
                'name' => $file['name'],
                'size' => (int) $file['size'],
                'ctime' => (int) $file['ctime'],
                'md5' => md5($file['name']),
            ];
        }

        return $files;
    }

    public function isLegacyPoolItemIdentifier(string $identifier): bool
    {
        return str_starts_with($identifier, self::LEGACY_POOL_ITEM_PREFIX);
    }

    public function legacyPoolFilenameFromIdentifier(string $identifier): string
    {
        return substr($identifier, strlen(self::LEGACY_POOL_ITEM_PREFIX));
    }

    public function migrateLegacyPoolFilenameToResource(string $filename): ?ResourceIdentification
    {
        $path = $this->getAbsoluteAttachmentPoolPathByFilename($filename);
        if (!is_file($path)) {
            return null;
        }

        $rid = $this->streamFromPath($path, md5($filename));
        $pool_rcid = $this->resolveUserPoolRcid();
        $updated_rcid = $this->adoptPoolResourcesToCollection($pool_rcid, [$rid]);
        if ($updated_rcid === null) {
            return null;
        }

        if ($pool_rcid === null || $pool_rcid->serialize() !== $updated_rcid->serialize()) {
            $this->persistUserPoolRcid($updated_rcid);
        }

        $this->unlinkFile($filename);

        return $rid;
    }

    /**
     * @param list<string> $identifiers
     * @return list<ResourceIdentification>
     */
    public function resolvePoolIdentifiersToResources(array $identifiers): array
    {
        $resource_identifications = [];

        foreach ($identifiers as $identifier) {
            if ($this->isLegacyPoolItemIdentifier($identifier)) {
                $resource_identification = $this->migrateLegacyPoolFilenameToResource(
                    $this->legacyPoolFilenameFromIdentifier($identifier)
                );
            } else {
                $resource_identification = $this->irss->manage()->find($identifier);
            }

            if ($resource_identification !== null) {
                $resource_identifications[] = $resource_identification;
            }
        }

        return $resource_identifications;
    }

    /**
     * @param list<ResourceIdentification> $resource_identifications
     */
    public function adoptPoolResourcesToCollection(
        ?ResourceCollectionIdentification $rcid,
        array $resource_identifications
    ): ?ResourceCollectionIdentification {
        if ($resource_identifications === []) {
            return null;
        }

        $collection = $this->loadPoolCollectionForMutation($rcid);
        $added = false;

        foreach ($resource_identifications as $resource_identification) {
            $hash = $this->irss->manage()->getCurrentRevision($resource_identification)->getTitle();
            if ($this->resourceIdByHashInCollection($collection, $hash) !== null) {
                continue;
            }

            $collection->add($resource_identification);
            $added = true;
        }

        if (!$added) {
            return null;
        }

        $this->irss->collection()->store($collection);

        return $collection->getIdentification();
    }

    public function removeFromPool(ResourceIdentification $rid): void
    {
        $pool_rcid = $this->resolveUserPoolRcid();
        if ($pool_rcid === null) {
            return;
        }

        $collection = $this->getCollection($pool_rcid);
        if (!$collection->isIn($rid)) {
            return;
        }

        $collection->remove($rid);
        $this->irss->collection()->store($collection);

        if (!$this->isResourceReferencedOutsideCollection($rid, $pool_rcid)) {
            $this->irss->manage()->remove($rid, $this->stakeholder);
        }
    }

    public function poolResourceHash(ResourceIdentification $rid): string
    {
        return $this->irss->manage()->getCurrentRevision($rid)->getTitle();
    }


    private function persistUserPoolRcid(ResourceCollectionIdentification $rcid): void
    {
        $this->db->replace(
            'usr_pref',
            [
                'usr_id' => [ilDBConstants::T_INTEGER, $this->user_id],
                'keyword' => [ilDBConstants::T_TEXT, self::POOL_RCID_PREF_KEY],
            ],
            [
                'value' => [ilDBConstants::T_TEXT, $rcid->serialize()],
            ]
        );
    }

    private function isResourceReferencedOutsideCollection(
        ResourceIdentification $rid,
        ResourceCollectionIdentification $collection_identification
    ): bool {
        $res = $this->db->queryF(
            'SELECT rcid FROM il_resource_rca WHERE rid = %s',
            [ilDBConstants::T_TEXT],
            [$rid->serialize()]
        );

        while ($row = $this->db->fetchObject($res)) {
            if ($row->rcid !== $collection_identification->serialize()) {
                return true;
            }
        }

        return false;
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

    public function assignAttachmentsToCollection(
        int $mail_id,
        ResourceCollectionIdentification $rcid
    ): void {
        $this->db->manipulateF(
            'INSERT INTO mail_attachment (mail_id, path, rcid) VALUES (%s, %s, %s)',
            [ilDBConstants::T_INTEGER, ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$mail_id, '', $rcid->serialize()]
        );
    }

    public function getRcidForMail(int $mail_id): ?ResourceCollectionIdentification
    {
        $res = $this->db->queryF(
            'SELECT rcid FROM mail_attachment WHERE mail_id = %s',
            ['integer'],
            [$mail_id]
        );

        if ($this->db->numRows($res) !== 1) {
            return null;
        }

        $row = $this->db->fetchAssoc($res);
        $rcid = (string) ($row['rcid'] ?? '');
        if ($rcid === '' || $rcid === '-') {
            return null;
        }

        return new ResourceCollectionIdentification($rcid);
    }

    public function countMailsReferencingRcid(string $rcid): int
    {
        $res = $this->db->queryF(
            'SELECT COUNT(mail_id) cnt FROM mail_attachment WHERE rcid = %s',
            ['text'],
            [$rcid]
        );

        return (int) $this->db->fetchObject($res)->cnt;
    }

    public function deassignAttachmentFromDirectory(int $a_mail_id): bool
    {
        $res = $this->db->query(
            'SELECT path, rcid FROM mail_attachment WHERE mail_id = ' . $this->db->quote($a_mail_id, 'integer')
        );

        $path = '';
        $rcid = '';
        while ($row = $this->db->fetchObject($res)) {
            $path = (string) $row->path;
            $rcid = (string) ($row->rcid ?? '');
        }

        if ($rcid !== '' && $rcid !== '-') {
            if ($this->countMailsReferencingRcid($rcid) === 1) {
                $this->removeCollection(new ResourceCollectionIdentification($rcid));
            }
        } elseif ($path !== '') {
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
        $pool_rcid = $this->resolveUserPoolRcid();
        if ($pool_rcid !== null) {
            try {
                $this->removeCollection($pool_rcid);
            } catch (Exception) {
            }
        }

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
			AND ma1.path IS NOT NULL AND ma1.path != ""
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

        $rcid_query = '
            SELECT DISTINCT(ma1.rcid)
            FROM mail_attachment ma1
            INNER JOIN mail ON mail.mail_id = ma1.mail_id
            WHERE mail.user_id = %s
            AND ma1.rcid IS NOT NULL AND ma1.rcid != "" AND ma1.rcid != "-"
            AND (SELECT COUNT(tmp.rcid) FROM mail_attachment tmp WHERE tmp.rcid = ma1.rcid) = 1
        ';
        $rcid_res = $this->db->queryF(
            $rcid_query,
            [ilDBConstants::T_INTEGER],
            [$this->user_id]
        );
        while ($row = $this->db->fetchAssoc($rcid_res)) {
            try {
                $this->removeCollection(new ResourceCollectionIdentification($row['rcid']));
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
        $rcid = $this->getRcidForMail($mail_id);
        if ($rcid !== null && !$is_draft) {
            $this->deliverCollectionAsZip($rcid, $basename);
            return;
        }

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

    public function getStakeholder(): ilMailAttachmentStakeholder
    {
        return $this->stakeholder;
    }

    public function streamFromPath(string $absolute_path, ?string $revision_title = null): ResourceIdentification
    {
        $stream = Streams::ofResource(fopen($absolute_path, 'rb'));

        return $this->irss->manage()->stream(
            $stream,
            $this->stakeholder,
            $revision_title ?? md5(basename($absolute_path))
        );
    }

    public function uploadToIrss(UploadResult $result): ResourceIdentification
    {
        return $this->irss->manage()->upload(
            $result,
            $this->stakeholder,
            md5($result->getName())
        );
    }

    /**
     * @param list<ResourceIdentification> $resource_identifications
     */
    public function createCollectionFromResourceIdentifications(
        array $resource_identifications
    ): ResourceCollectionIdentification {
        $rcid = $this->irss->collection()->id(null, $this->user_id);
        $collection = $this->irss->collection()->get($rcid);
        foreach ($resource_identifications as $resource_identification) {
            $collection->add($resource_identification);
        }
        $this->irss->collection()->store($collection);

        return $collection->getIdentification();
    }

    /**
     * @param list<string> $filenames Pool filenames without user prefix
     */
    public function createCollectionFromPoolFilenames(array $filenames): ?ResourceCollectionIdentification
    {
        return $this->adoptPoolFilenamesToCollection(null, $filenames);
    }

    public function migrateLegacyPoolAttachments(MailAttachments $attachments): ?MailAttachments
    {
        if (!$attachments->isLegacy()) {
            return $attachments;
        }

        if (!$this->checkFilesExist($attachments->legacyFilenames())) {
            return null;
        }

        $rcid = $this->createCollectionFromPoolFilenames($attachments->legacyFilenames());

        return $rcid !== null ? MailAttachments::fromIrss($rcid) : null;
    }

    /**
     * @param list<string> $filenames Pool filenames without user prefix
     */
    public function adoptPoolFilenamesToCollection(
        ?ResourceCollectionIdentification $rcid,
        array $filenames
    ): ?ResourceCollectionIdentification {
        if ($filenames === []) {
            return null;
        }

        if ($rcid !== null && $this->irss->collection()->exists($rcid->serialize())) {
            $collection_id = $this->irss->collection()->id($rcid->serialize(), $this->user_id);
        } else {
            $collection_id = $this->irss->collection()->id(null, $this->user_id);
        }

        $collection = $this->irss->collection()->get($collection_id);
        $added = false;

        foreach ($filenames as $filename) {
            $path = $this->getAbsoluteAttachmentPoolPathByFilename($filename);
            if (!is_file($path)) {
                continue;
            }

            $hash = md5(basename($path));
            if ($this->resourceIdByHashInCollection($collection, $hash) !== null) {
                continue;
            }

            $collection->add($this->streamFromPath($path, $hash));
            $added = true;
        }

        if (!$added) {
            return null;
        }

        $this->irss->collection()->store($collection);

        return $collection->getIdentification();
    }

    /**
     * @param list<string> $absolute_paths
     */
    public function createCollectionFromPaths(array $absolute_paths): ?ResourceCollectionIdentification
    {
        $resource_identifications = [];
        foreach ($absolute_paths as $absolute_path) {
            if (!is_file($absolute_path)) {
                continue;
            }
            $resource_identifications[] = $this->streamFromPath($absolute_path);
        }

        if ($resource_identifications === []) {
            return null;
        }

        return $this->createCollectionFromResourceIdentifications($resource_identifications);
    }

    public function createCollectionFromContent(string $name, string $content): ?ResourceCollectionIdentification
    {
        if ($name === '' || $content === '') {
            return null;
        }

        if (strlen($content) >= $this->getUploadLimit()) {
            throw new DomainException(
                sprintf(
                    'Mail upload limit reached for user with id %s',
                    $this->user_id
                )
            );
        }

        $sanitized_name = ilFileUtils::_sanitizeFilemame($name);

        return $this->createCollectionFromResourceIdentifications([
            $this->irss->manage()->stream(
                Streams::ofString($content),
                $this->stakeholder,
                md5($sanitized_name)
            ),
        ]);
    }

    public function copyCollectionForDelivery(
        ResourceCollectionIdentification $source
    ): ?ResourceCollectionIdentification {
        if (!$this->collectionIsKnown($source)) {
            return null;
        }

        $resource_identifications = iterator_to_array(
            $this->getCollection($source)->getResourceIdentifications(),
            false
        );

        return $this->createCollectionFromForeignResources($resource_identifications);
    }

    /**
     * @param list<ResourceIdentification> $resource_identifications
     */
    public function createCollectionFromForeignResources(
        array $resource_identifications
    ): ?ResourceCollectionIdentification {
        if ($resource_identifications === []) {
            return null;
        }

        $mail_resources = [];
        foreach ($resource_identifications as $source) {
            $revision = $this->irss->manage()->getCurrentRevision($source);
            $stream = $this->irss->consume()->stream($source);
            $mail_resources[] = $this->irss->manage()->stream(
                $stream->getStream(),
                $this->stakeholder,
                $revision->getTitle()
            );
        }

        return $this->createCollectionFromResourceIdentifications($mail_resources);
    }

    public function getCollection(ResourceCollectionIdentification $rcid): ResourceCollection
    {
        if (!$this->collectionIsKnown($rcid)) {
            throw new OutOfBoundsException(
                sprintf('Mail attachment collection "%s" does not exist.', $rcid->serialize())
            );
        }

        $this->repairCollectionHeaderIfNeeded($rcid);
        $this->irss->preloadCollections([$rcid->serialize()]);

        return $this->irss->collection()->get($rcid, $this->user_id);
    }

    private function loadPoolCollectionForMutation(?ResourceCollectionIdentification $pool_rcid): ResourceCollection
    {
        if ($pool_rcid !== null && $this->collectionIsKnown($pool_rcid)) {
            $this->repairCollectionHeaderIfNeeded($pool_rcid);
            $this->irss->preloadCollections([$pool_rcid->serialize()]);

            return $this->irss->collection()->get($pool_rcid, $this->user_id);
        }

        $collection_id = $this->irss->collection()->id(null, $this->user_id);

        return $this->irss->collection()->get($collection_id, $this->user_id);
    }

    /**
     * @return list<string>
     */
    private function getAssignedRidStrings(ResourceCollectionIdentification $rcid): array
    {
        $res = $this->db->queryF(
            'SELECT ' . CollectionDBRepository::R_IDENTIFICATION .
            ' FROM ' . CollectionDBRepository::COLLECTION_ASSIGNMENT_TABLE_NAME .
            ' WHERE ' . CollectionDBRepository::C_IDENTIFICATION . ' = %s ORDER BY position ASC',
            [ilDBConstants::T_TEXT],
            [$rcid->serialize()]
        );

        $rids = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $rids[] = (string) $row[CollectionDBRepository::R_IDENTIFICATION];
        }

        return $rids;
    }

    private function collectionIsKnown(ResourceCollectionIdentification $rcid): bool
    {
        return $this->irss->collection()->exists($rcid->serialize())
            || $this->getAssignedRidStrings($rcid) !== [];
    }

    private function repairCollectionHeaderIfNeeded(ResourceCollectionIdentification $rcid): void
    {
        if ($this->irss->collection()->exists($rcid->serialize())
            || $this->getAssignedRidStrings($rcid) === []) {
            return;
        }

        $this->db->replace(
            CollectionDBRepository::COLLECTION_TABLE_NAME,
            [
                CollectionDBRepository::C_IDENTIFICATION => [ilDBConstants::T_TEXT, $rcid->serialize()],
            ],
            [
                'title' => [ilDBConstants::T_TEXT, ''],
                'owner_id' => [ilDBConstants::T_INTEGER, $this->user_id],
            ]
        );
    }

    public function getResourceIdByHash(
        ResourceCollectionIdentification $rcid,
        string $hash
    ): ?ResourceIdentification {
        if (!$this->collectionIsKnown($rcid)) {
            return null;
        }

        foreach ($this->getAssignedRidStrings($rcid) as $rid) {
            $resource_identification = $this->irss->manage()->find($rid);
            if ($resource_identification === null) {
                continue;
            }

            $revision = $this->irss->manage()->getCurrentRevision($resource_identification);
            if ($revision->getTitle() === $hash) {
                return $resource_identification;
            }
        }

        return null;
    }

    private function resourceIdByHashInCollection(ResourceCollection $collection, string $hash): ?ResourceIdentification
    {
        foreach ($collection->getResourceIdentifications() as $resource_identification) {
            $revision = $this->irss->manage()->getCurrentRevision($resource_identification);
            if ($revision->getTitle() === $hash) {
                return $resource_identification;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function getRidsFromCollection(ResourceCollectionIdentification $rcid): array
    {
        $rids = [];
        foreach ($this->getAssignedRidStrings($rcid) as $rid) {
            if ($this->irss->manage()->find($rid) !== null) {
                $rids[] = $rid;
            }
        }

        return $rids;
    }


    /**
     * @param list<ResourceIdentification> $resource_identifications
     */
    public function collectionContainsResources(
        ResourceCollectionIdentification $rcid,
        array $resource_identifications
    ): bool {
        if ($resource_identifications === []) {
            return true;
        }

        $collection_serials = [];
        foreach ($this->getCollection($rcid)->getResourceIdentifications() as $resource_identification) {
            $collection_serials[] = $resource_identification->serialize();
        }

        $expected_serials = [];
        foreach ($resource_identifications as $resource_identification) {
            $expected_serials[] = $resource_identification->serialize();
        }

        sort($collection_serials);
        sort($expected_serials);

        return $collection_serials === $expected_serials;
    }

    /**
     * @return array<string, array{md5: string, name: string, size: int, ctime: string}>
     */
    public function getAttachmentListing(ResourceCollectionIdentification $rcid): array
    {
        $files = [];
        foreach ($this->getAssignedRidStrings($rcid) as $rid) {
            $resource_identification = $this->irss->manage()->find($rid);
            if ($resource_identification === null) {
                continue;
            }

            $revision = $this->irss->manage()->getCurrentRevision($resource_identification);
            $info = $revision->getInformation();
            $file_title = $info->getTitle();
            $files[$file_title] = [
                'md5' => $revision->getTitle(),
                'name' => $file_title,
                'size' => $info->getSize(),
                'ctime' => $info->getCreationDate()->format('Y-m-d H:i:s'),
            ];
        }

        return $files;
    }

    public function deliverFile(ResourceCollectionIdentification $rcid, string $md5_hash): void
    {
        $resource_identification = $this->getResourceIdByHash($rcid, $md5_hash);
        if ($resource_identification === null) {
            throw new OutOfBoundsException('mail_error_reading_attachment');
        }

        $this->irss->consume()->download($resource_identification)->run();
    }

    public function deliverCollectionAsZip(ResourceCollectionIdentification $rcid, string $zip_basename): void
    {
        $zip_filename = FileDelivery::returnASCIIFileName($zip_basename . '.zip');
        $this->irss
            ->consume()
            ->downloadCollection($rcid, $zip_filename)
            ->useRevisionTitlesForFileNames(false)
            ->run();
    }

    public function removeCollection(ResourceCollectionIdentification $rcid, bool $ignore_usage = true): void
    {
        $this->irss->collection()->remove(
            $this->irss->collection()->id($rcid->serialize()),
            $this->stakeholder,
            $ignore_usage
        );
    }

    /**
     * @return list<array{rid: string, name: string}>
     */
    public function getIrssMimeAttachments(ResourceCollectionIdentification $rcid): array
    {
        $attachments = [];
        foreach ($this->getAssignedRidStrings($rcid) as $rid) {
            $resource_identification = $this->irss->manage()->find($rid);
            if ($resource_identification === null) {
                continue;
            }

            $info = $this->irss->manage()->getCurrentRevision($resource_identification)->getInformation();
            $attachments[] = [
                'rid' => $rid,
                'name' => $info->getTitle(),
            ];
        }

        return $attachments;
    }
}
