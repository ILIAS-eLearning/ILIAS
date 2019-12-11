<?php

/**
 * Class ilWebDAVUriPathResolver
 *
 * This class resolves given WebDAV-Uris and returns the reference id of the searched object.
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVUriPathResolver
{
    /** @var ilWebDAVRepositoryHelper */
    protected $repo_helper;

    /**
     * ilWebDAVUriPathResolver constructor.
     *
     * @param ilWebDAVRepositoryHelper $repo_helper
     */
    public function __construct(ilWebDAVRepositoryHelper $repo_helper)
    {
        $this->repo_helper = $repo_helper;
    }

    /**
     * Returns the ref_id of the given webdav path. Path starts without php-script
     *
     * Examples
     *
     *  Path starts at a ref: <client_name>/ref_<ref_id>/folder1/folder2
     *  Path starts at root:  <client_name>/ILIAS/foo_container1/course1
     *
     * Note: This URI is handled case sensitive, since URIS SHOULD BE case sensitive
     *
     * @param $a_uri
     * @return int|mixed
     * @throws \Sabre\DAV\Exception\BadRequest
     * @throws \Sabre\DAV\Exception\NotFound
     */
    public function getRefIdForWebDAVPath(string $a_uri) : int
    {
        $a_uri = trim($a_uri, '/');

        /* After this funciton, the array SHOULD look like this:
         * $splitted_path[0] = '<client_name>'
         * $splitted_path[1] = 'ref_<ref_id>' or <ilias_root_name>
         * $splitted_path[2] = '<rest>/<of>/<the>/<path>
         */
        $splitted_path = explode('/', $a_uri, 3);

        // Early exit for bad request
        if (count($splitted_path) < 2) {
            throw new \Sabre\DAV\Exception\BadRequest('Path too short');
        }

        $repository_mountpoint = $splitted_path[1];
        $path_inside_of_mountpoint = isset($splitted_path[2]) ? $splitted_path[2] : '';

        // Since we already know our client, we only have to check the requested root for our path
        // if second string = 'ilias', the request was for the ilias root
        if ($repository_mountpoint == 'ILIAS') {
            return $this->getRefIdFromPathInRepositoryMount($path_inside_of_mountpoint);
        } // if the first 4 letters are 'ref_', we are searching for a ref ID in the tree
        elseif (substr($repository_mountpoint, 0, 4) == 'ref_') {
            return $this->getRefIdFromPathInRefMount($repository_mountpoint, $path_inside_of_mountpoint);
        }

        // if there was no 'ilias' and no 'ref_' in the second string, then its an invalid request
        throw new \Sabre\DAV\Exception\BadRequest('Invalid mountpoint given');
    }

    /**
     * Gets the ref_id from a searched object in the repository.
     *
     * The search starts at the root node of the ILIAS-Repository, which  is defined with the constant ROOT_FOLDER_ID
     *
     * The string $path_inside_of_mountpoint is the title path (a path made out of object titles), with which the object
     * will be searched. It could look like following: "groupXYZ/folder123/file.txt"
     *
     * @param $path_inside_of_mountpoint
     * @return int
     * @throws \Sabre\DAV\Exception\NotFound
     */
    protected function getRefIdFromPathInRepositoryMount(string $path_inside_of_mountpoint) : int
    {
        if ($path_inside_of_mountpoint != '') {
            return $this->getRefIdFromGivenParentRefAndTitlePath(ROOT_FOLDER_ID, explode('/', $path_inside_of_mountpoint));
        } else {
            return ROOT_FOLDER_ID;
        }
    }

    /**
     * Gets the ref_id from a searched object in the repository.
     *
     * The string $repository_mountpoint indicates, on which object the search should start. It has a format of
     * "ref_<id>", where <id> is the actual reference id of an object.
     *
     * The string $path_inside_of_mountpoint is the title path (a path made out of object titles), with which the object
     * will be searched. It could look like following: "groupXYZ/folder123/file.txt"
     *
     * @param string $repository_mountpoint
     * @param string $path_inside_of_mountpoint
     * @return int
     * @throws \Sabre\DAV\Exception\NotFound
     */
    protected function getRefIdFromPathInRefMount(string $repository_mountpoint, string $path_inside_of_mountpoint) : int
    {
        // Make a 'ref_1234' to a '1234'
        // Since we already tested for 'ref_', we can be sure there is at least one '_' character
        $relative_mountpoint_ref_id = (int) explode('_', $repository_mountpoint)[1];

        // Case 1: Path to an object given and a ref_id is given
        if ($path_inside_of_mountpoint != '' && $relative_mountpoint_ref_id > 0) {
            return $this->getRefIdFromGivenParentRefAndTitlePath($relative_mountpoint_ref_id, explode('/', $path_inside_of_mountpoint));
        }
        // Case 2: No path is given, but a ref_id. This means, the searched object is actually the given ref_id
        // This happens for an URI like "<client_id>/ref_1234/" (the part after the '/' is actually an empty string)
        elseif ($path_inside_of_mountpoint == '' && $relative_mountpoint_ref_id > 0) {
            return $relative_mountpoint_ref_id;
        }
        // Case 3: Given ref_id is invalid (no number or 0 given). Throw an exception since there is no object like this
        else {
            throw new \Sabre\DAV\Exception\NotFound('Mount point not found');
        }
    }

    /**
     * Searches an object inside the given path, starting at the given reference id. The return value is the ref_id of
     * the last object in the given path.
     *
     * @param int $a_parent_ref
     * @param array $a_current_path_array
     * @return int
     * @throws \Sabre\DAV\Exception\NotFound
     */
    protected function getRefIdFromGivenParentRefAndTitlePath(int $a_parent_ref, array $a_current_path_array) : int
    {
        $current_ref_id = $a_parent_ref;
        while (count($a_current_path_array) >= 1) {
            // Pops out first element (respectively object title)
            $next_searched_title = array_shift($a_current_path_array);
            if ($next_searched_title != '') {
                try {
                    $current_ref_id = $this->getChildRefIdByGivenTitle($current_ref_id, $next_searched_title);
                } catch (\Sabre\DAV\Exception\NotFound $e) {
                    if (count($a_current_path_array) == 0) {
                        /* This is a really special case. It occurs, if the lock is meant for an object that does not
                           exist yet (so called NullRessources) since we can't ant won't lock non existing objects, we
                           set the Exception code to -1. The receiving class SHOULD handle what to do with this value */
                        throw new \Sabre\DAV\Exception\NotFound('Last node not found', -1);
                    } else {
                        // Set Exception code to 0, so their won't be any conflicts with default values
                        throw new \Sabre\DAV\Exception\NotFound('Node not found', 0);
                    }
                }
            }
        }
        return $current_ref_id;
    }

    /**
     * Searches for a an object with a specific title inside an other object (identified by ref_id)
     *
     * Note: This check is case sensitive, since URIS SHOULD BE case sensitive
     *
     * @param int $a_parent_ref_id
     * @param string $a_searched_title
     * @return int
     *
     * @throws \Sabre\DAV\Exception\NotFound
     */
    protected function getChildRefIdByGivenTitle(int $a_parent_ref_id, string $a_searched_title) : int
    {
        // Search if any child of the given ref has the name of the given searched element
        foreach ($this->repo_helper->getChildrenOfRefId($a_parent_ref_id) as $child_ref) {
            $child_title = $this->repo_helper->getObjectTitleFromRefId($child_ref, true);
            if ($a_searched_title == $child_title) {
                return $child_ref;
            }
        }

        throw new \Sabre\DAV\Exception\NotFound('Node not found');
    }
}
