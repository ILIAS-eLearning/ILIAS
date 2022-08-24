<?php

declare(strict_types=1);

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

use Sabre\DAV\Exception\Forbidden;

class ilWebDAVRepositoryHelper
{
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected ilRepUtil $repository_util;
    protected ilWebDAVLocksRepository $locks_repository;

    public function __construct(ilAccessHandler $access, ilTree $tree, ilRepUtil $repository_util, ilWebDAVLocksRepository $locks_repository)
    {
        $this->access = $access;
        $this->tree = $tree;
        $this->repository_util = $repository_util;
        $this->locks_repository = $locks_repository;
    }

    public function deleteObject(int $ref_id): void
    {
        if (!$this->checkAccess('delete', $ref_id)) {
            throw new Forbidden("Permission denied");
        }

        $parent = $this->tree->getParentId($ref_id);
        $this->repository_util->deleteObjects($parent, [$ref_id]);
    }

    public function checkAccess(string $permission, int $ref_id): bool
    {
        return $this->access->checkAccess($permission, '', $ref_id);
    }

    public function checkCreateAccessForType(int $ref_id, string $type): bool
    {
        return $this->access->checkAccess('create', '', $ref_id, $type);
    }

    public function objectWithRefIdExists(int $ref_id): bool
    {
        return ilObject::_exists($ref_id, true);
    }

    public function getObjectIdFromRefId(int $ref_id): int
    {
        return ilObject::_lookupObjectId($ref_id);
    }

    public function getObjectTitleFromObjId(int $obj_id, bool $escape_forbidden_fileextension = false): string
    {
        if ($escape_forbidden_fileextension && ilObject::_lookupType($obj_id) === 'file') {
            $title = $this->getFilenameWithSanitizedFileExtension($obj_id);
        } else {
            $title = $this->getRawObjectTitleFromObjId($obj_id);
        }

        return $title;
    }

    public function getFilenameWithSanitizedFileExtension(int $obj_id): string
    {
        $unescaped_title = $this->getRawObjectTitleFromObjId($obj_id);

        try {
            $escaped_title = ilFileUtils::getValidFilename($unescaped_title);
        } catch (ilFileUtilsException $e) {
            $escaped_title = '';
        }

        return $escaped_title;
    }

    protected function getRawObjectTitleFromObjId(int $obj_id): string
    {
        return ilObject::_lookupTitle($obj_id);
    }

    public function getParentOfRefId(int $ref_id): int
    {
        return $this->tree->getParentId($ref_id);
    }

    public function getObjectTypeFromObjId(int $obj_id): string
    {
        return ilObject::_lookupType($obj_id);
    }

    public function getObjectTitleFromRefId(int $ref_id, bool $escape_forbidden_fileextension = false): string
    {
        $obj_id = $this->getObjectIdFromRefId($ref_id);

        return $this->getObjectTitleFromObjId($obj_id, $escape_forbidden_fileextension);
    }

    public function getObjectTypeFromRefId(int $ref_id): string
    {
        return ilObject::_lookupType($ref_id, true);
    }

    /**
     *
     * @return int[]
     */
    public function getChildrenOfRefId(int $ref_id): array
    {
        return array_map(
            'intval',
            $this->tree->getChildIds($ref_id)
        );
    }

    public function updateLocksAfterResettingObject(int $old_obj_id, int $new_obj_id): void
    {
        $this->locks_repository->updateLocks($old_obj_id, $new_obj_id);
    }
}
