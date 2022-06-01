<?php declare(strict_types = 1);

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
 
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\NotFound;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilWebDAVLockUriPathResolver
{
    protected ilWebDAVRepositoryHelper $webdav_repository_helper;
    
    public function __construct(ilWebDAVRepositoryHelper $webdav_repository_helper)
    {
        $this->webdav_repository_helper = $webdav_repository_helper;
    }
    
    public function getRefIdForWebDAVPath(string $uri) : int
    {
        $uri = trim($uri, '/');
        
        $split_path = explode('/', $uri, 2);
        
        if (!isset($split_path[0])
            || $split_path[0] === ''
            || $split_path[0] !== CLIENT_ID) {
            throw new BadRequest('Invalid client id given');
        }
        
        $path_inside_of_mountpoint = isset($split_path[1]) ? $split_path[1] : '';
        $mountpoint = '';
        
        if ($path_inside_of_mountpoint !== ''
            && substr($path_inside_of_mountpoint, 0, 4) === 'ref_') {
            $split_path = explode('/', $path_inside_of_mountpoint, 2);
            $mountpoint = $split_path[0];
            $path_inside_of_mountpoint = isset($split_path[1]) ? $split_path[1] : '';
        }
        
        if ($mountpoint !== '') {
            return $this->getRefIdFromPathInRefMount($mountpoint, $path_inside_of_mountpoint);
        }
        
        return $this->getRefIdFromPathInRepositoryMount($path_inside_of_mountpoint);
    }
    
    protected function getRefIdFromPathInRepositoryMount(string $path_inside_of_mountpoint) : int
    {
        if ($path_inside_of_mountpoint === '') {
            return ROOT_FOLDER_ID;
        }
        
        return $this->getRefIdFromGivenParentRefAndTitlePath(ROOT_FOLDER_ID, explode('/', $path_inside_of_mountpoint));
    }
    
    protected function getRefIdFromPathInRefMount(string $repository_mountpoint, string $path_inside_of_mountpoint) : int
    {
        $relative_mountpoint_ref_id = (int) explode('_', $repository_mountpoint)[1];
        
        if ($relative_mountpoint_ref_id < 1) {
            throw new NotFound('Mount point not found');
        }
        
        if ($path_inside_of_mountpoint === '') {
            return $relative_mountpoint_ref_id;
        }
        
        return $this->getRefIdFromGivenParentRefAndTitlePath($relative_mountpoint_ref_id, explode('/', $path_inside_of_mountpoint));
    }
    
    /**
     * @param string[] $current_path_array
     */
    protected function getRefIdFromGivenParentRefAndTitlePath(int $a_parent_ref, array $current_path_array) : int
    {
        $current_ref_id = $a_parent_ref;
        while (count($current_path_array) >= 1) {
            $next_searched_title = array_shift($current_path_array);
            if ($next_searched_title !== '') {
                try {
                    $current_ref_id = $this->getChildRefIdByGivenTitle($current_ref_id, $next_searched_title);
                } catch (NotFound $e) {
                    if (count($current_path_array) === 0) {
                        /* This is a really special case. It occurs, if the lock is meant for an object that does not
                           exist yet (so called NullRessources) since we can't and won't lock non existing objects, we
                           set the Exception code to -1. The receiving class SHOULD handle what to do with this value */
                        throw new NotFound('Last node not found', -1);
                    }
                    
                    throw new NotFound('Node not found', 0);
                }
            }
        }
        return $current_ref_id;
    }

    protected function getChildRefIdByGivenTitle(int $a_parent_ref_id, string $a_searched_title) : int
    {
        $ref_to_return = null;
        
        foreach ($this->webdav_repository_helper->getChildrenOfRefId($a_parent_ref_id) as $child_ref) {
            $child_title = $this->webdav_repository_helper->getObjectTitleFromRefId($child_ref, true);
            if ($a_searched_title === $child_title) {
                $ref_to_return = $child_ref;
            }
        }
        
        if (!is_null($ref_to_return)) {
            return $ref_to_return;
        }

        throw new NotFound('Node not found');
    }
}
