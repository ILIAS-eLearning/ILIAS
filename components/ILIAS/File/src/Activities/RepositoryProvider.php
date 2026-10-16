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

namespace ILIAS\File\Activities;

use ILIAS\AccessControl\PublicInterface\Access;
use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\Activities\FileContent;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;

/**
 * The bits of the repository the file activities need: the tree to check where
 * a file may go and access and rbac to check whether a user may put it there.
 *
 * Access and rbac come from the AccessControl component. The repository tree,
 * the object definition and the settings are not offered by any component yet,
 * so these are still resolved from the legacy container on first use - the same way
 * AccessControl itself gets at the tree. They move to the constructor as soon
 * as their components provide them.
 */
class RepositoryProvider
{
    public function __construct(
        private readonly Access $access,
        private readonly RBAC $rbac,
        private ?\ilTree $tree = null,
        private ?\ilObjectDefinition $definition = null,
        private ?\ilSetting $settings = null,
        private ?int $acting_user_id = null,
    ) {
    }

    /**
     * Is this a node objects can be put into?
     */
    public function isContainer(int $ref_id): bool
    {
        if ($ref_id <= 0 || !$this->tree()->isInTree($ref_id)) {
            return false;
        }

        $type = (string) \ilObject::_lookupType($ref_id, true);

        return $type !== '' && $this->definition()->isContainer($type);
    }

    public function access(): Access
    {
        return $this->access;
    }

    public function mayRead(int $usr_id, int $ref_id, string $type = ''): bool
    {
        return $this->access()->checkAccessOfUser($usr_id, 'read', '', $ref_id, $type);
    }

    public function mayWrite(int $usr_id, int $ref_id, string $type = ''): bool
    {
        return $this->access()->checkAccessOfUser($usr_id, 'write', '', $ref_id, $type);
    }

    /**
     * The objects in a container, as the tree knows them - no object is loaded
     * for this, a listing must not cost one query per entry.
     *
     * @param string[] $types limit to these object types, all if empty
     *
     * @return list<ContainerEntry>
     */
    public function listContent(int $parent_ref_id, bool $recursive = false, array $types = []): array
    {
        $tree = $this->tree();

        $nodes = $recursive
            ? $tree->getSubTree($tree->getNodeData($parent_ref_id), true, $types)
            : $tree->getChilds($parent_ref_id);

        $entries = [];

        foreach ($nodes as $node) {
            $ref_id = (int) ($node['child'] ?? $node['ref_id'] ?? 0);
            $type = (string) ($node['type'] ?? '');

            if ($ref_id === 0 || $ref_id === $parent_ref_id) {
                continue;
            }

            if ($types !== [] && !in_array($type, $types, true)) {
                continue;
            }

            $entries[] = new ContainerEntry(
                $ref_id,
                (int) ($node['obj_id'] ?? 0),
                $type,
                (string) ($node['title'] ?? ''),
                (string) ($node['description'] ?? ''),
            );
        }

        return $entries;
    }

    /**
     * Everything about one file object, or null if that is not a file.
     */
    public function getFileEntry(int $ref_id): ?FileEntry
    {
        $file = \ilObjectFactory::getInstanceByRefId($ref_id, false);

        return $file instanceof \ilObjFile ? $this->entryOf($file) : null;
    }

    public function mayDelete(int $usr_id, int $ref_id, string $type = ''): bool
    {
        return $this->access()->checkAccessOfUser($usr_id, 'delete', '', $ref_id, $type);
    }

    public function mayCopy(int $usr_id, int $ref_id, string $type = ''): bool
    {
        return $this->access()->checkAccessOfUser($usr_id, 'copy', '', $ref_id, $type);
    }

    /**
     * Asked through the access handler like the GUI does, not rbac alone: that
     * way the path to the target and its conditions count as well.
     */
    public function mayCreate(int $usr_id, int $ref_id, string $type): bool
    {
        return $this->access()->checkAccessOfUser($usr_id, 'create_' . $type, '', $ref_id);
    }

    /**
     * Title and description of an object, nothing that belongs to its content.
     */
    public function updateFileMetadata(int $ref_id, ?string $title, ?string $description): FileEntry
    {
        $file = $this->fileObject($ref_id);

        if ($title !== null) {
            $file->setTitle($title);
        }
        if ($description !== null) {
            $file->setDescription($description);
        }

        $file->update();

        return $this->entryOf($file);
    }

    /**
     * Deletes an object exactly like the GUI does: into the trash if the
     * installation has one, for good if it has not.
     *
     * @return bool whether the object went to the trash and can still be restored
     */
    public function delete(int $ref_id): bool
    {
        $in_trash = $this->isTrashEnabled();

        // ilRepUtil::deleteObjects() is deprecated, but the deletion it hands on
        // to belongs to the internals of the Repository component - this moves
        // as soon as that offers a public deletion service. Its first argument,
        // the parent, is ignored.
        \ilRepUtil::deleteObjects(0, [$ref_id]);

        return $in_trash;
    }

    /**
     * Whether the legacy code would act as this user.
     *
     * Creating, copying, replacing and deleting go through ilObject, ilObjFile
     * and the deletion of the repository, and all of them take the user of the
     * session - as owner of an object, of a revision, as author of the news,
     * for a check of their own. A command performed for somebody else would be
     * checked for that user and done as another one. So commands are only
     * performed as the user of the session; queries are free, they read
     * through the access handler with the user they are given.
     */
    public function isActingUser(int $usr_id): bool
    {
        if ($this->acting_user_id === null) {
            global $DIC;

            $this->acting_user_id = $DIC->isDependencyAvailable('user') ? (int) $DIC->user()->getId() : 0;
        }

        return $usr_id > 0 && $usr_id === $this->acting_user_id;
    }

    public function isTrashEnabled(): bool
    {
        return (bool) $this->settings()->get('enable_trash');
    }

    /**
     * Moves a node to another container, keeping its identity - the permissions
     * of the new place have to be applied afterwards.
     */
    public function moveObject(int $ref_id, int $target_ref_id): void
    {
        $old_parent = (int) $this->tree()->getParentId($ref_id);

        $this->tree()->moveTree($ref_id, $target_ref_id);
        $this->rbac->admin()->adjustMovedObjectPermissions($ref_id, $old_parent);
    }

    /**
     * Copies a file object into another container.
     */
    public function copyFile(int $ref_id, int $target_ref_id): FileEntry
    {
        $copy = $this->fileObject($ref_id)->cloneObject($target_ref_id);

        if (!$copy instanceof \ilObjFile) {
            throw new \RuntimeException('The file could not be copied.');
        }

        return $this->entryOf($copy);
    }

    private function fileObject(int $ref_id): \ilObjFile
    {
        $file = \ilObjectFactory::getInstanceByRefId($ref_id, false);

        if (!$file instanceof \ilObjFile) {
            throw new \OutOfBoundsException("There is no file object {$ref_id}.", 404);
        }

        return $file;
    }

    public function lookupType(int $ref_id): string
    {
        if ($ref_id <= 0 || !$this->tree()->isInTree($ref_id)) {
            return '';
        }

        return (string) \ilObject::_lookupType($ref_id, true);
    }

    /**
     * @return string the resource of the file object, empty if it has none
     */
    public function lookupResourceId(int $ref_id): string
    {
        $file = \ilObjectFactory::getInstanceByRefId($ref_id, false);

        return $file instanceof \ilObjFile ? $file->getResourceId() : '';
    }

    public function definition(): \ilObjectDefinition
    {
        if (!$this->definition instanceof \ilObjectDefinition) {
            global $DIC;

            $this->definition = $DIC['objDefinition'];
        }

        return $this->definition;
    }

    public function settings(): \ilSetting
    {
        if (!$this->settings instanceof \ilSetting) {
            global $DIC;

            $this->settings = $DIC->settings();
        }

        return $this->settings;
    }

    public function tree(): \ilTree
    {
        if (!$this->tree instanceof \ilTree) {
            global $DIC;

            $this->tree = $DIC->repositoryTree();
        }

        return $this->tree;
    }

    /**
     * Creates the file object and gives it its content, or leaves nothing
     * behind if that does not work out.
     *
     * This is where the legacy choreography lives - create, reference, tree,
     * permissions, content - so the activity itself stays about the domain and
     * remains testable: the methods of ilObject are final.
     */
    public function createFile(
        int $parent_ref_id,
        ?string $title,
        ?string $description,
        FileContent $content
    ): FileEntry {
        $file = new \ilObjFile();
        $file->setTitle($title ?? $content->filename);
        $file->setDescription($description ?? '');
        $file->setFileName($content->filename);

        $file->create();
        $file->createReference();
        $file->putInTree($parent_ref_id);
        $file->setPermissions($parent_ref_id);

        try {
            $this->storeContent($file, $content->stream(), $content->filename, $title, true);
        } catch (\Throwable $e) {
            // no half created objects - and ilObject::delete() removes the object
            // and its reference, but not its node in the tree
            $this->tree()->deleteTree($this->tree()->getNodeData($file->getRefId()));
            $file->delete();

            throw $e;
        }

        return $this->entryOf($file);
    }

    /**
     * Gives an existing file object new content.
     *
     * @param bool $keep_previous_version keep the old content as a version of
     *                                    its own instead of overwriting it
     */
    public function replaceFileContent(
        int $ref_id,
        FileContent $content,
        bool $keep_previous_version = true
    ): FileEntry {
        $file = \ilObjectFactory::getInstanceByRefId($ref_id, false);

        if (!$file instanceof \ilObjFile) {
            throw new \OutOfBoundsException("There is no file object {$ref_id}.", 404);
        }

        $this->storeContent($file, $content->stream(), $content->filename, null, $keep_previous_version);

        return $this->entryOf($file);
    }

    private function storeContent(
        \ilObjFile $file,
        FileStream $stream,
        string $filename,
        ?string $title,
        bool $as_new_version
    ): void {
        // ilObjFile stores the content itself, with its own stakeholder
        try {
            if ($as_new_version) {
                $file->appendStream($stream, $filename);
            } else {
                $file->replaceWithStream($stream, $filename);
            }
        } catch (FileNamePolicyException $e) {
            // the file names the installation does not accept are the caller's business
            throw new \InvalidArgumentException($e->getMessage(), 400, $e);
        }

        // storing a revision takes the title from that revision, so a title the
        // caller asked for has to be written again afterwards
        if ($title !== null && $file->getTitle() !== $title) {
            $file->setTitle($title);
            $file->update();
        }
    }

    private function entryOf(\ilObjFile $file): FileEntry
    {
        return new FileEntry(
            $file->getRefId(),
            $file->getId(),
            $file->getTitle(),
            (string) $file->getDescription(),
            $file->getFileName(),
            $file->getFileType(),
            $file->getFileSize(),
            $file->getVersion(),
            $file->getResourceId(),
        );
    }
}
