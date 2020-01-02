<?php

use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\BadRequest;

/**
 * Class ilWebDAVTree
 *
 * This class is used for manual tree traversal from a WebDAV-Request if it isn't handled by Sabre\DAV\Tree
 *
 * Mostly used for lock and unlock calls. Might be refactored to be a substitute for Sabre\DAV\Tree in a future
 * version.
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVTree
{
    protected static $instance;
    
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Returns the ref_id of the given webdav path. Path starts without php-script
     *
     * Examples
     *
     *  Path starts at a ref: <client_name>/ref_<ref_id>/folder1/folder2
     *  Path starts at root:  <client_name>/ILIAS/foo_container1/course1/
     *
     * @param $a_uri
     * @return int|mixed
     * @throws BadRequest
     * @throws NotFound
     */
    public static function getRefIdForWebDAVPath($a_uri)
    {
        $a_uri = strtolower(trim($a_uri, '/'));
        
        /* After this funciton, the array SHOULD look like this:
         * $splitted_path[0] = '<client_name>'
         * $splitted_path[1] = 'ref_<ref_id>' or <ilias_root_name>
         * $splitted_path[2] = '<rest>/<of>/<the>/<path>
         */
        $splitted_path = explode('/', $a_uri, 3);
        
        // Early exit for bad request
        if (count($splitted_path) < 2) {
            throw new BadRequest();
        }
        
        $repository_mountpoint = $splitted_path[1];
        $path_in_mountpoint = $splitted_path[2];
        
        // Since we already know our client, we only have to check the requested root for our path
        // if second string = 'ilias', the request was for the ilias root
        if ($repository_mountpoint == 'ilias') {
            if ($path_in_mountpoint != '') {
                $ref_path = self::getRefIdForGivenRootAndPath(ROOT_FOLDER_ID, $path_in_mountpoint);
                $searched_node = $ref_path[count($ref_path)-1];
                $ref_id = $searched_node['child'];
            } else {
                $ref_id = ROOT_FOLDER_ID;
            }
        }
        // if the first 4 letters are 'ref_', we are searching for a ref ID in the tree
        elseif (substr($splitted_path[1], 0, 4) == 'ref_') {
            // Make a 'ref_1234' to a '1234'
            // Since we already tested for 'ref_', we can be sure there is at least one '_' character
            $start_node = (int) explode('_', $repository_mountpoint)[1];
            if ($path_in_mountpoint != '' && $start_node > 0) {
                $ref_id = self::getRefIdForGivenRootAndPath($start_node, $path_in_mountpoint);
            } elseif ($path_in_mountpoint == '') {
                $ref_id = $start_node;
            } else {
                throw new NotFound();
            }
        }
        // if there was no 'ilias' and no 'ref_' in the second string, this was a bad request...
        else {
            throw new BadRequest();
        }
        
        return $ref_id;
    }

    /**
     * @param int $start_ref
     * @param string $path_from_startnode
     * @return int
     */
    public static function getRefIdForGivenRootAndPath(int $start_ref, string $path_from_startnode)
    {
        return self::iterateRecursiveThroughTree(explode('/', $path_from_startnode), 0, $start_ref);
    }

    /**
     * Recursive function to iterate through tree with given path
     *
     * @param $path_title_array        Array with all object titles in this path
     * @param $searched_element_index  Index for the path_title_array which points to the searched obj title
     * @param $parent_ref_id           Ref ID of parent of the searched element
     * @return int
     */
    protected static function iterateRecursiveThroughTree($path_title_array, $searched_element_index, $parent_ref_id)
    {
        global $DIC;

        // Check if last element was already found
        if ($path_title_array[$searched_element_index] == '' || count($path_title_array) == $searched_element_index) {
            return $parent_ref_id;
        }

        // Search if any child of the given ref has the name of the given searched element
        foreach ($DIC->repositoryTree()->getChildIds($parent_ref_id) as $child_ref) {
            $child_obj_id = ilObject::_lookupObjectId($child_ref);
            $child_title = strtolower(ilObject::_lookupTitle($child_obj_id));
            if ($path_title_array[$searched_element_index] == $child_title) {
                if (count($path_title_array)-1 == $searched_element_index) {
                    // Last element found. Return ref_id
                    return $child_ref;
                } else {
                    // Search next element in path
                    return self::iterateRecursiveThroughTree($path_title_array, $searched_element_index+1, $child_ref);
                }
            }
        }
        
        return -1;
    }
}
