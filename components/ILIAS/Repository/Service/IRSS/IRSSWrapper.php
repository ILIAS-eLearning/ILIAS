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

namespace ILIAS\Repository\IRSS;

use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\Filesystem\Stream\Stream;
use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\Filesystem\Util\Archive\Unzip;
use ILIAS\Filesystem\Stream\ZIPStream;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\components\ResourceStorage\Container\Wrapper\ZipReader;
use ILIAS\FileDelivery\Delivery\Disposition;
use ILIAS\Filesystem\Util\Archive\ZipOptions;
use ILIAS\Filesystem\Filesystems;

class IRSSWrapper
{
    protected Filesystems $filesystems;
    protected \ILIAS\FileDelivery\Services $file_delivery;
    protected \ILIAS\FileUpload\FileUpload $upload;
    protected \ILIAS\ResourceStorage\Services $irss;
    protected Archives $archives;

    public function __construct(
        protected DataService $data
    ) {
        global $DIC;

        if (!$DIC->isDependencyAvailable("resourceStorage")) {
            return;
        }
        $this->irss = $DIC->resourceStorage();
        $this->archives = $DIC->archives();
        $this->upload = $DIC->upload();
        $this->archives = $DIC->archives();
        $this->file_delivery = $DIC->fileDelivery();
        $this->filesystems = $DIC->filesystem();
    }

    protected function getNewCollectionId(): ResourceCollectionIdentification
    {
        return $this->irss->collection()->id();
    }

    protected function getNewCollectionIdAsString(): string
    {
        return $this->getNewCollectionId()->serialize();
    }

    public function createEmptyCollection(): string
    {
        $new_id = $this->getNewCollectionId();
        $new_collection = $this->irss->collection()->get($new_id);
        $this->irss->collection()->store($new_collection);
        return $new_id->serialize();
    }

    public function getCollectionForIdString(string $rcid): ResourceCollection
    {
        return $this->irss->collection()->get($this->irss->collection()->id($rcid));
    }

    public function deleteCollectionForIdString(
        string $rcid,
        ResourceStakeholder $stakeholder
    ): void {
        $id = $this->irss->collection()->id($rcid);
        $this->irss->collection()->remove($id, $stakeholder, true);
    }

    /*
    public function copyResourcesToDir(
        string $rcid,
        ResourceStakeholder $stakeholder,
        string $dir
    ) {
        $collection = $this->irss->collection()->get($this->irss->collection()->id($rcid));
        foreach ($collection->getResourceIdentifications() as $rid) {
            $info = $this->irss->manage()->getResource($rid)
                               ->getCurrentRevision()
                               ->getInformation();
            $stream = $this->irss->consume()->stream($rid);
            $stream->getContents();
        }
    }*/

    public function importFilesFromLegacyUploadToCollection(
        ResourceCollection $collection,
        array $file_input,
        ResourceStakeholder $stakeholder
    ): void {
        $upload = $this->upload;

        if (is_array($file_input)) {
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            foreach ($upload->getResults() as $name => $result) {
                // we must check if these are files from this input
                if (!in_array($name, $file_input["tmp_name"] ?? [], true)) {
                    continue;
                }
                // if the result is not OK, we skip it
                if (!$result->isOK()) {
                    continue;
                }

                // we store the file in the IRSS
                $rid = $this->irss->manage()->upload(
                    $result,
                    $stakeholder
                );
                // and add its identification to the collection
                $collection->add($rid);
            }
            // we store the collection after all files have been added
            $this->irss->collection()->store($collection);
        }
    }

    public function importFilesFromDirectoryToCollection(
        ResourceCollection $collection,
        string $directory,
        ResourceStakeholder $stakeholder
    ): void {
        $sourceFS = LegacyPathHelper::deriveFilesystemFrom($directory);
        $sourceDir = LegacyPathHelper::createRelativePath($directory);

        // check if arguments are directories
        if (!$sourceFS->hasDir($sourceDir)) {
            return;
        }

        $sourceList = $sourceFS->listContents($sourceDir, false);

        foreach ($sourceList as $item) {
            if ($item->isDir()) {
                continue;
            }
            try {
                $stream = $sourceFS->readStream($item->getPath());
                $rid = $this->irss->manage()->stream(
                    $stream,
                    $stakeholder
                );
                $collection->add($rid);
            } catch (\ILIAS\Filesystem\Exception\FileAlreadyExistsException $e) {
            }
        }
        $this->irss->collection()->store($collection);
    }

    public function getResourceIdForIdString(string $rid): ?ResourceIdentification
    {
        return $this->irss->manage()->find($rid);
    }

    public function importFileFromLegacyUpload(
        array $file_input,
        ResourceStakeholder $stakeholder
    ): string {
        $upload = $this->upload;

        if (is_array($file_input)) {
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            foreach ($upload->getResults() as $name => $result) {
                // we must check if these are files from this input
                if ($name !== ($file_input["tmp_name"] ?? "")) {
                    continue;
                }
                // if the result is not OK, we skip it
                if (!$result->isOK()) {
                    continue;
                }

                // we store the file in the IRSS
                $rid = $this->irss->manage()->upload(
                    $result,
                    $stakeholder
                );
                return $rid->serialize();
            }
        }
        return "";
    }

    public function importFileFromUploadResult(
        UploadResult $result,
        ResourceStakeholder $stakeholder
    ): string {
        // if the result is not OK, we skip it
        if (!$result->isOK()) {
            return "";
        }

        // we store the file in the IRSS
        $rid = $this->irss->manage()->upload(
            $result,
            $stakeholder
        );
        return $rid->serialize();
    }

    public function importLocalFile(
        string $file,
        string $name,
        ResourceStakeholder $stakeholder
    ): string {
        $sourceFS = LegacyPathHelper::deriveFilesystemFrom($file);
        $sourceFile = LegacyPathHelper::createRelativePath($file);

        //try {
        $stream = $sourceFS->readStream($sourceFile);
        $rid = $this->irss->manage()->stream(
            $stream,
            $stakeholder,
            $name
        );
        //} catch (\Exception $e) {
        //    return "";
        //}
        return $rid->serialize();
    }

    public function importStream(
        Stream $stream,
        ResourceStakeholder $stakeholder
    ): string {
        $rid = $this->irss->manage()->stream(
            $stream,
            $stakeholder
        );
        return $rid->serialize();
    }

    public function renameCurrentRevision(
        string $rid,
        string $title
    ): void {
        $id = $this->getResourceIdForIdString($rid);
        $rev = $this->irss->manage()->getCurrentRevision($id);
        $info = $rev->getInformation();
        $info->setTitle($title);
        $rev->setInformation($info);
        $this->irss->manage()->updateRevision($rev);
    }

    public function deliverFile(string $rid): void
    {
        $id = $this->getResourceIdForIdString($rid);
        if ($id) {
            $revision = $this->irss->manage()->getCurrentRevision($id);
            $this->irss->consume()->download($id)->overrideFileName($revision->getTitle())->run();
        }
    }

    public function stream(string $rid): ?FileStream
    {
        $id = $this->getResourceIdForIdString($rid);
        if ($id) {
            return $this->irss->consume()->stream($id)->getStream();
        }
        return null;
    }

    public function getResourcePath(string $rid): string
    {
        $stream = $this->stream($rid);
        if ($stream) {
            return $stream->getMetadata('uri') ?? '';
        }
        return "";
    }

    public function getResource(string $rid): ?StorableResource
    {
        $id = $this->getResourceIdForIdString($rid);
        if ($id) {
            return $this->irss->manage()->getResource($id);
        }
        return null;
    }

    public function getCollectionResourcesInfo(
        ResourceCollection $collection
    ): \Generator {
        foreach ($collection->getResourceIdentifications() as $rid) {
            $info = $this->irss->manage()->getResource($rid)
                               ->getCurrentRevision()
                               ->getInformation();
            $src = $this->irss->consume()->src($rid)->getSrc();
            yield $this->data->resourceInformation(
                $rid->serialize(),
                $info->getTitle(),
                $info->getSize(),
                $info->getCreationDate()->getTimestamp(),
                $info->getMimeType(),
                $src
            );
        }
    }

    public function getResourceInfo(
        string $rid
    ): ResourceInformation {
        $rid = $this->getResourceIdForIdString($rid);
        $info = $this->irss->manage()->getResource($rid)
                           ->getCurrentRevision()
                           ->getInformation();
        $src = $this->irss->consume()->src($rid)->getSrc();
        return $this->data->resourceInformation(
            $rid->serialize(),
            $info->getTitle(),
            $info->getSize(),
            $info->getCreationDate()->getTimestamp(),
            $info->getMimeType(),
            $src
        );
    }

    public function clone(
        string $from_rc_id
    ): string {
        if ($from_rc_id !== "") {
            $cloned_rcid = $this->irss->collection()->clone($this->irss->collection()->id($from_rc_id));
            return $cloned_rcid->serialize();
        }
        return "";
    }

    public function cloneResource(
        string $from_rid
    ): string {
        if ($from_rid !== "") {
            $cloned_rid = $this->irss->manage()->clone($this->getResourceIdForIdString($from_rid));
            return $cloned_rid->serialize();
        }
        return "";
    }

    public function cloneContainer(
        string $from_rid
    ): string {
        if ($from_rid !== "") {
            $cloned_rid = $this->irss->manageContainer()->clone($this->getResourceIdForIdString($from_rid));
            return $cloned_rid->serialize();
        }
        return "";
    }

    public function deleteResource(string $rid, ResourceStakeholder $stakeholder): void
    {
        if ($rid !== "") {
            $res = $this->getResourceIdForIdString($rid);
            if ($res) {
                $this->irss->manage()->remove($this->getResourceIdForIdString($rid), $stakeholder);
            }
        }
    }

    public function addEntryOfZipResourceToCollection(
        string $rid,
        string $entry,
        ResourceCollection $target_collection,
        ResourceStakeholder $target_stakeholder
    ): void {
        $entry_parts = explode("/", $entry);
        $stream = $this->getStreamOfContainerEntry($rid, $entry);
        $feedback_rid = $this->irss->manage()->stream(
            $stream,
            $target_stakeholder,
            $entry_parts[2]
        );
        $target_collection->add($feedback_rid);
        $this->irss->collection()->store($target_collection);
    }

    //
    // Container
    //

    public function getStreamOfContainerEntry(
        string $rid,
        string $entry
    ): ZIPStream {
        $zip_path = $this->stream($rid)->getMetadata("uri");
        return Streams::ofFileInsideZIP(
            $zip_path,
            $entry
        );
    }

    public function getContainerEntryInfo(
        string $container_id,
        string $path
    ): array {
        $reader = new ZipReader(
            $this->irss->consume()->stream($this->getResourceIdForIdString($container_id))->getStream()
        );
        return $reader->getItem($path)[1];
    }

    public function deliverContainerEntry(
        string $container_id,
        string $path
    ): void {
        $reader = new ZipReader(
            $this->irss->consume()->stream($this->getResourceIdForIdString($container_id))->getStream()
        );
        [$stream, $info] = $reader->getItem($path);

        $this->file_delivery->delivery()->deliver(
            $stream,
            $info['basename'],
            $info['mime_type'],
            Disposition::ATTACHMENT
        );
    }

    /**
     * Is there a better way to check this?
     */
    public function hasContainerEntry(
        string $rid,
        string $entry
    ): bool {
        if ($rid === "") {
            return false;
        }
        $zip_path = $this->stream($rid)?->getMetadata("uri");
        if (is_null($zip_path)) {
            return false;
        }
        try {
            $stream = Streams::ofFileInsideZIP(
                $zip_path,
                $entry
            );
            $stream->close();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    // this currently does not work due to issues in the irss
    /*
    public function importContainerFromZipUploadResult(
        UploadResult $result,
        ResourceStakeholder $stakeholder
    ): string {
        // if the result is not OK, we skip it
        if (!$result->isOK()) {
            return "";
        }

        // we store the file in the IRSS
        $container_id = $this->irss->manage()->containerFromUpload(
            $result,
            $stakeholder
        );
        return $container_id->serialize();
    }*/

    /**
     * @return \Generator<Stream>
     */
    public function getContainerStreams(
        string $container_id,
        ResourceStakeholder $stakeholder
    ): \Generator {
        foreach ($this->irss->consume()->containerZIP(
            $this->getResourceIdForIdString($container_id)
        )->getZIP()->getFileStreams() as $stream) {
            yield $stream;
        }
    }

    public function getContainerPaths(
        string $container_id
    ): \Generator {
        foreach ($this->irss->consume()->containerZIP(
            $this->getResourceIdForIdString($container_id)
        )->getZIP()->getPaths() as $path) {
            yield $path;
        }
    }

    public function getContainerEntries(
        string $container_id
    ): array {
        $rid = $this->getResourceIdForIdString($container_id);
        if (is_null($rid)) {
            return [];
        }
        $reader = new ZipReader(
            $this->irss->consume()->stream($rid)->getStream()
        );
        return $reader->getStructure();
    }

    public function getContainerEntriesOfPath(
        string $container_id,
        string $dir_path
    ): array {
        $rid = $this->getResourceIdForIdString($container_id);
        if (is_null($rid)) {
            return [];
        }
        $reader = new ZipReader(
            $this->irss->consume()->stream($rid)->getStream()
        );
        $entries = [];
        foreach ($reader->getStructure() as $path => $entry) {
            $dirname = $entry['dirname'] ?? '';
            if ($dirname !== $dir_path) {
                continue;
            }
            $entries[$path] = $entry;
        }
        return $entries;
    }

    public function addContainerDirToTargetContainer(
        string $source_container_id,
        string $target_container_id,
        string $source_dir_path = "",
        string $target_dir_path = ""
    ): void {
        $reader = new ZipReader(
            $this->irss->consume()->stream($this->getResourceIdForIdString($source_container_id))->getStream()
        );
        $entries = [];
        foreach ($reader->getStructure() as $path => $entry) {
            if (str_starts_with($entry['dirname'], $source_dir_path) && !$entry['is_dir']) {
                $this->addStreamToContainer(
                    $target_container_id,
                    $this->getStreamOfContainerEntry($source_container_id, $path),
                    $target_dir_path . "/" . substr($path, strlen($source_dir_path))
                );
            }
        }
    }

    public function createContainer(
        ResourceStakeholder $stakeholder,
        string $title = "container.zip"
    ): string {
        if ($title === "") {
            throw new \ilException("Container title missing.");
        }
        // create empty container resource. empty zips are not allowed, we need at least one file which is hidden
        $tmp_dir_info = new \SplFileInfo(\ilFileUtils::ilTempnam());
        $this->filesystems->temp()->createDir($tmp_dir_info->getFilename());
        $tmp_dir = $tmp_dir_info->getRealPath();
        $options = (new ZipOptions())
            ->withZipOutputName($title)
            ->withZipOutputPath($tmp_dir);
        $empty_zip = $this->archives->zip(
            [],
            $options
        );
        $rid = $this->irss->manageContainer()->containerFromStream(
            $empty_zip->get(),
            $stakeholder,
            $title
        );
        \ilFileUtils::delDir($tmp_dir);
        return $rid->serialize();
    }

    public function createContainerFromLocalZip(
        string $local_zip_path,
        ResourceStakeholder $stakeholder
    ): string {
        $stream = fopen($local_zip_path, 'r');
        $fs = new Stream($stream);

        $rid = $this->irss->manageContainer()->containerFromStream(
            $fs,
            $stakeholder
        );
        return $rid->serialize();
    }

    public function createContainerFromLocalDir(
        string $local_dir_path,
        ResourceStakeholder $stakeholder,
        string $container_path = "",
        bool $recursive = true,
        string $title = "container.zip"
    ): string {
        $real_dir_path = realpath($local_dir_path);
        $rid = $this->createContainer($stakeholder, $title);
        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $local_dir_path,
                    \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::CURRENT_AS_SELF
                ),
                \RecursiveIteratorIterator::SELF_FIRST
            );
        } else {
            $iterator = new \DirectoryIterator($local_dir_path);
        }
        if ($container_path !== "") {
            $container_path = $container_path . "/";
        }
        foreach ($iterator as $file) {
            if (!$file->isDir() && !$file->isDot()) {
                $file->getRealPath();
                $this->addLocalFileToContainer(
                    $rid,
                    $file->getRealPath(),
                    $container_path . substr($file->getRealPath(), strlen($real_dir_path) + 1)
                );
            }
        }
        return $rid;
    }

    public function addLocalFileToContainer(
        string $rid,
        string $fullpath,
        string $path
    ): void {
        $id = $this->getResourceIdForIdString($rid);
        $stream = fopen($fullpath, 'r');
        $fs = new Stream($stream);
        $this->irss->manageContainer()->removePathInsideContainer($id, $path);
        $this->irss->manageContainer()->addStreamToContainer(
            $id,
            $fs,
            $path
        );
        fclose($stream);
    }

    public function addStringToContainer(
        string $rid,
        string $content,
        string $path
    ): void {
        $id = $this->getResourceIdForIdString($rid);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        $fs = new Stream($stream);
        $this->irss->manageContainer()->removePathInsideContainer($id, $path);
        $this->irss->manageContainer()->addStreamToContainer(
            $id,
            $fs,
            $path
        );
        fclose($stream);
    }

    public function addDirectoryToContainer(
        string $rid,
        string $source_dir,
        string $target_path = ""
    ): void {
        $source_dir = realpath($source_dir);
        $directoryIterator = new \RecursiveDirectoryIterator(
            $source_dir,
            \FilesystemIterator::SKIP_DOTS
        );

        $recursiveIterator = new \RecursiveIteratorIterator(
            $directoryIterator,
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($recursiveIterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $fullPath = $fileInfo->getPathname();
                $relativePath = substr($fullPath, strlen($source_dir) + 1);
                $files[] = $relativePath;
                $this->addLocalFileToContainer(
                    $rid,
                    $fullPath,
                    $target_path . "/" . $relativePath
                );
            }
        }
    }

    public function addUploadToContainer(
        string $rid,
        UploadResult $result
    ): void {
        $id = $this->getResourceIdForIdString($rid);
        $this->irss->manageContainer()->addUploadToContainer(
            $id,
            $result,
            "images"
        );
    }

    public function getContainerUri(
        string $rid,
        string $path
    ): string {
        $id = $this->getResourceIdForIdString($rid);
        $uri = $this->irss->consume()->containerURI(
            $id,
            $path,
            8 * 60
        )->getURI();
        // temp fixes wrong slash escaping, maybe due to 24805bcaabb33b1c5c82609dbe6791c55577c6a4
        $uri = str_replace("%2F", "/", (string) $uri);
        return (string) $uri;
    }

    public function getContainerZip(
        string $rid
    ): Unzip {
        $id = $this->getResourceIdForIdString($rid);
        return $this->irss->consume()->containerZIP(
            $id
        )->getZIP();
    }



    public function importFileFromLegacyUploadToContainer(
        string $rid,
        string $tmp_name,
        string $target_path
    ): void {
        $upload = $this->upload;

        if (!$upload->hasBeenProcessed()) {
            $upload->process();
        }
        foreach ($upload->getResults() as $name => $result) {
            // we must check if these are files from this input
            if ($name !== $tmp_name) {
                continue;
            }
            // if the result is not OK, we skip it
            if (!$result->isOK()) {
                continue;
            }

            $id = $this->getResourceIdForIdString($rid);

            if (!is_null($id)) {
                // if target path is a directory, addUploadToContainer
                // can be used, the original filename will be appended
                if ($target_path === "" || str_ends_with($target_path, "/")) {
                    $this->irss->manageContainer()->addUploadToContainer(
                        $id,
                        $result,
                        "/"
                    );
                } else {
                    // we have a full path given (renaming the
                    // original name)
                    $this->addLocalFileToContainer(
                        $rid,
                        $result->getPath(),
                        $target_path
                    );
                }
            }
        }
    }

    public function importFileFromUploadResultToContainer(
        string $rid,
        UploadResult $result,
        string $target_path
    ): void {
        // if the result is not OK, we skip it
        if (!$result->isOK()) {
            return;
        }

        $id = $this->getResourceIdForIdString($rid);

        if (!is_null($id)) {
            $this->irss->manageContainer()->addUploadToContainer(
                $id,
                $result,
                $target_path
            );
        }
    }

    public function addStreamToContainer(
        string $rid,
        FileStream $stream,
        string $path
    ): void {
        $id = $this->getResourceIdForIdString($rid);

        if (!is_null($id)) {
            $this->irss->manageContainer()->addStreamToContainer(
                $id,
                $stream,
                $path
            );
        }
    }

    public function removePathFromContainer(
        string $rid,
        string $path
    ): void {
        $id = $this->getResourceIdForIdString($rid);
        if (!is_null($id)) {
            $this->irss->manageContainer()->removePathInsideContainer($id, $path);
        }
    }

    // currently broken, see https://mantis.ilias.de/view.php?id=44135
    public function renameContainer(
        string $rid,
        string $title
    ): void {
        $id = $this->getResourceIdForIdString($rid);
        $rev = $this->irss->manageContainer()->getCurrentRevision($id);
        $info = $rev->getInformation();
        $info->setTitle($title);
        $rev->setInformation($info);
        $this->irss->manageContainer()->updateRevision($rev);
    }

}
