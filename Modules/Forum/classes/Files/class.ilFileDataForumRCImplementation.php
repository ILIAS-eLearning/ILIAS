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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\UI\NotImplementedException;

/**
 * Class ilFileDataForumRCImplementation
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilFileDataForumRCImplementation implements ilFileDataForumInterface
{
    public const FORUM_PATH_RCID = 'RCID';
    private \ILIAS\ResourceStorage\Services $irss;
    private \ILIAS\FileUpload\FileUpload $upload;
    private array $collection_cache = [];
    private array $posting_cache = [];
    private ilForumPostingFileStakeholder $stakeholder;


    public function __construct(private int $obj_id = 0, private int $pos_id = 0)
    {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
        $this->upload = $DIC->upload();
        $this->stakeholder = new ilForumPostingFileStakeholder();
    }

    private function getCurrentPosting(): ilForumPost
    {
        return $this->getPostingById($this->pos_id);
    }

    private function getPostingById(int $posting_id): ilForumPost
    {
        if (isset($this->posting_cache[$posting_id])) {
            return $this->posting_cache[$posting_id];
        }
        return $this->posting_cache[$posting_id] = new ilForumPost($posting_id);
    }

    private function getCurrentCollection(): \ILIAS\ResourceStorage\Collection\ResourceCollection
    {
        if (isset($this->collection_cache[$this->pos_id])) {
            return $this->collection_cache[$this->pos_id];
        }
        return $this->collection_cache[$this->pos_id] = $this->irss->collection()->get(
            $this->irss->collection()->id(
                $this->getCurrentPosting()->getRCID()
            )
        );
    }

    private function getResourceIdByHash(string $hash): ?ResourceIdentification
    {
        foreach ($this->getCurrentCollection()->getResourceIdentifications() as $identification) {
            $revision = $this->irss->manage()->getCurrentRevision($identification);
            if ($revision->getTitle() === $hash) {
                return $identification;
            }
        }
        return null;
    }

    private function getResourceIdByName(string $filename): ?ResourceIdentification
    {
        return $this->getFileDataByMD5Filename(md5($filename));
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getPosId(): int
    {
        return $this->pos_id;
    }

    public function setPosId(int $posting_id): void
    {
        $this->pos_id = $posting_id;
    }

    public function getForumPath(): string
    {
        return self::FORUM_PATH_RCID;
    }

    /**
     * @return array<string, array{path: string, md5: string, name: string, size: int, ctime: string}>
     */
    public function getFilesOfPost(): array
    {
        $files = [];
        foreach ($this->getCurrentCollection()->getResourceIdentifications() as $identification) {
            $revision = $this->irss->manage()->getCurrentRevision($identification);
            $info = $revision->getInformation();
            $files[] = [
                'path' => $this->irss->consume()->stream($identification)->getStream()->getMetadata('uri'),
                'md5' => $revision->getTitle(),
                'name' => $info->getTitle(),
                'size' => $info->getSize(),
                'ctime' => $info->getCreationDate()->format('Y-m-d H:i:s')
            ];
        }

        return $files;
    }

    public function moveFilesOfPost(int $new_frm_id = 0): bool
    {
        // nothing to do here since collections are related to the post
        return true;
    }

    public function ilClone(int $new_obj_id, int $new_posting_id): bool
    {
        $current_collection_id = $this->getCurrentCollection()->getIdentification();
        $new_collection_id = $this->irss->collection()->clone($current_collection_id);
        $new_posting = $this->getPostingById($new_posting_id);
        $new_posting->setRCID($new_collection_id->serialize());
        $new_posting->update();
        return true;
    }

    public function delete(array $posting_ids_to_delete = null): bool
    {
        foreach ($posting_ids_to_delete as $post_id) {
            $this->irss->collection()->remove(
                $this->irss->collection()->id(
                    $this->getPostingById($post_id)->getRCID()
                ),
                $this->stakeholder,
                true
            );
        }
        return true;
    }

    public function storeUploadedFiles(): bool
    {
        if (!$this->upload->hasBeenProcessed()) {
            $this->upload->process();
        }
        $collection = $this->getCurrentCollection();

        foreach ($this->upload->getResults() as $result) {
            if (!$result->isOK()) {
                continue;
            }
            $rid = $this->irss->manage()->upload(
                $result,
                $this->stakeholder,
                md5($result->getName())
            );
            $collection->add($rid);
        }
        $this->irss->collection()->store($collection);
        $posting = $this->getCurrentPosting();
        $posting->setRCID($collection->getIdentification()->serialize());
        $posting->update();

        return true;
    }

    public function unlinkFile(string $filename): bool
    {
        $rid = $this->getResourceIdByName($filename);
        if ($rid !== null) {
            $this->irss->manage()->remove($rid, $this->stakeholder);
        }
        return true;
    }

    /**
     * @return array{path: string, filename: string, clean_filename: string}|null
     */
    public function getFileDataByMD5Filename(string $hashed_filename): ?array
    {
        foreach ($this->getCurrentCollection()->getResourceIdentifications() as $identification) {
            $revision = $this->irss->manage()->getCurrentRevision($identification);
            if ($revision->getTitle() === $hashed_filename) {
                $info = $revision->getInformation();
                return [
                    'path' => '',
                    'filename' => $info->getTitle(),
                    'clean_filename' => $info->getTitle()
                ];
            }
        }

        return null;
    }

    /**
     * @param string|string[] $hashed_filename_or_filenames
     */
    public function unlinkFilesByMD5Filenames($hashed_filename_or_filenames): bool
    {
        $hashes = is_array($hashed_filename_or_filenames)
            ? $hashed_filename_or_filenames
            : [$hashed_filename_or_filenames];

        foreach ($hashes as $hash) {
            $identification = $this->getResourceIdByHash($hash);
            if ($identification !== null) {
                $this->irss->manage()->remove($identification, $this->stakeholder);
            }
        }
        return true;
    }


    public function deliverFile(string $file): void
    {
        $rid = $this->getResourceIdByHash($file);
        if ($rid !== null) {
            $this->irss->consume()->download($rid)->run();
        }
    }

    public function deliverZipFile(): bool
    {
        $zip_filename = $this->getCurrentPosting()->getSubject() . '.zip';
        $rcid = $this->getCurrentCollection()->getIdentification();

        $this->irss->consume()->downloadCollection($rcid, $zip_filename)
            ->useRevisionTitlesForFileNames(false)
            ->run();
        return true;
    }

    public function importFileToCollection(string $path_to_file, ilForumPost $post): void
    {
        if ($post->getRCID() === ilForumPost::NO_RCID || empty($post->getRCID())) {
            $rcid = $this->irss->collection()->id();
            $post->setRCID($rcid->serialize());
            $post->update();
        } else {
            $rcid = $this->irss->collection()->id($post->getRCID());
        }

        $collection = $this->irss->collection()->get($rcid);
        $rid = $this->irss->manage()->stream(
            Streams::ofResource(fopen($path_to_file, 'rb')),
            $this->stakeholder,
            md5(basename($path_to_file))
        );
        $collection->add($rid);
        $this->irss->collection()->store($collection);
    }
}
