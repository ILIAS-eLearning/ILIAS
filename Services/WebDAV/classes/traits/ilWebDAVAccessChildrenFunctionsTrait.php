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

use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\INode;

trait ilWebDAVAccessChildrenFunctionsTrait
{
    protected function getChildByParentRefId(
        ilWebDAVRepositoryHelper $repository_helper,
        ilWebDAVObjFactory $dav_object_factory,
        int $parent_ref_id,
        string $name
    ): INode {
        $child_node = null;
        $has_only_files_with_missing_storage = false;

        if ($name === ilDAVProblemInfoFile::PROBLEM_INFO_FILE_NAME) {
            return $dav_object_factory->getProblemInfoFile($parent_ref_id);
        }

        foreach ($repository_helper->getChildrenOfRefId($parent_ref_id) as $child_ref) {
            try {
                $child = $dav_object_factory->retrieveDAVObjectByRefID($child_ref);

                if ($child->getName() === $name) {
                    $child_node = $child;
                }
            } catch (RuntimeException $e) {
                if ($e->getMessage() === 'cannot read rource from resource storage server') {
                    $has_only_files_with_missing_storage = true;
                }
            } catch (ilWebDAVNotDavableException | Forbidden | NotFound $e) {
            }
        }

        if (!is_null($child_node)) {
            return $child_node;
        }

        if ($has_only_files_with_missing_storage) {
            throw new ilWebDAVMissingResourceException(ilWebDAVMissingResourceException::MISSING_RESSOURCE);
        }

        throw new NotFound("$name not found");
    }

    /**
     * @return INode[]
     */
    protected function getChildrenByParentRefId(
        ilWebDAVRepositoryHelper $repository_helper,
        ilWebDAVObjFactory $dav_object_factory,
        int $parent_ref_id
    ): array {
        $child_nodes = array();
        $already_seen_titles = array();
        $problem_info_file_needed = false;

        foreach ($repository_helper->getChildrenOfRefId($parent_ref_id) as $child_ref) {
            try {
                $child = $dav_object_factory->retrieveDAVObjectByRefID($child_ref);
                if (($key = array_search($child->getName(), $already_seen_titles)) !== false) {
                    unset($child_nodes[$key]);
                    $problem_info_file_needed = true;
                }

                $already_seen_titles[$child_ref] = $child->getName();

                $child_nodes[$child_ref] = $child;
            } catch (ilWebDAVNotDavableException | Forbidden $e) {
                if (!$problem_info_file_needed) {
                    $problem_info_file_needed == true;
                }
            } catch (NotFound | RuntimeException $e) {
            }
        }

        if ($problem_info_file_needed) {
            $child_nodes[] = $dav_object_factory->getProblemInfoFile($parent_ref_id);
        }

        return $child_nodes;
    }

    protected function checkIfChildExistsByParentRefId(
        ilWebDAVRepositoryHelper $repository_helper,
        ilWebDAVObjFactory $dav_object_factory,
        int $parent_ref_id,
        string $name
    ): bool {
        if ($name === ilDAVProblemInfoFile::PROBLEM_INFO_FILE_NAME) {
            return true;
        }

        foreach ($repository_helper->getChildrenOfRefId($parent_ref_id) as $child_ref) {
            try {
                $child = $dav_object_factory->retrieveDAVObjectByRefID($child_ref);

                if ($child->getName() === $name) {
                    return true;
                }
            } catch (ilWebDAVNotDavableException | Forbidden | NotFound | RuntimeException $e) {
            }
        }

        return false;
    }
}
