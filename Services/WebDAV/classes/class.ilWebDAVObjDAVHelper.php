<?php

/**
 * Class ilWebDAVObjDAVHelper
 *
 * This class is a helper class for WebDAV functionalities that are used from ilObj*DAV Objects. With this class, the
 * behavior of the objects itself are unit testable.
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVObjDAVHelper
{
    /** @var ilWebDAVRepositoryHelper */
    protected $repo_helper;

    /**
     * ilWebDAVObjDAVHelper constructor.
     *
     * @param ilWebDAVRepositoryHelper $repo_helper
     */
    public function __construct(ilWebDAVRepositoryHelper $repo_helper)
    {
        $this->repo_helper = $repo_helper;
    }

    /**
     * Check if given object (either obj_id or ref_id) is compatible to be represented as a WebDAV object
     *
     * @param $id
     * @param bool $is_reference
     * @return bool
     */
    public function isDAVableObject($id, $is_reference = true)
    {
        $obj_id = $is_reference ? $this->repo_helper->getObjectIdFromRefId($id) : $id;

        $type = $this->repo_helper->getObjectTypeFromObjId($obj_id);
        $title = $this->repo_helper->getObjectTitleFromObjId($obj_id);

        $is_davable = $this->isDAVableObjType($type) && $this->isDAVableObjTitle($title);
        return $is_davable;
    }

    /**
     * Check if the given object type is compatible to be represented as a WebDAV object
     *
     * @param $type
     * @return bool
     */
    public function isDAVableObjType(string $type) : bool
    {
        switch ($type) {
            case 'cat':
            case 'crs':
            case 'grp':
            case 'fold':
            case 'file':
                return true;

            default:
                return false;
        }
    }

    /**
     * Check if title is displayable in WebDAV
     * @param $title
     * @return bool
     */
    public function isDAVableObjTitle(string $title) : bool
    {
        return ($this->hasTitleForbiddenChars($title) === false)
            && ($this->hasInvalidPrefixInTitle($title) === false);
    }

    /**
     * Check for forbidden chars in title that are making trouble if displayed in WebDAV
     *
     * @param $title
     * @return bool
     */
    public function hasTitleForbiddenChars(string $title) : bool
    {
        foreach (str_split('\\<>/:*?"|#') as $forbidden_character) {
            if (strpos($title, $forbidden_character) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Forbidden are titles that begin with a single dot. There are also forbidden prefixes like '.$' or '..'. But since
     * they both start with a single dot, we can aim only for that.
     *
     * @param $title
     * @return bool
     */
    public function hasInvalidPrefixInTitle(string $title)
    {
        $prefix = substr($title, 0, 1);
        return $prefix === '.';
    }

    /**
     * Creates a DAV Object for the given ref id
     *
     * @param integer $ref_id
     * @param string $type
     * @return ilObjectDAV
     */
    public function createDAVObjectForRefId(int $ref_id, string $type = '') : ilObjectDAV
    {
        if ($type == '') {
            $type = $this->repo_helper->getObjectTypeFromRefId($ref_id);
        }

        if ($this->repo_helper->objectWithRefIdExists($ref_id)) {
            switch ($type) {
                case 'cat':
                    return new ilObjCategoryDAV(new ilObjCategory($ref_id, true), $this->repo_helper, $this);

                case 'crs':
                    return new ilObjCourseDAV(new ilObjCourse($ref_id, true), $this->repo_helper, $this);

                case 'grp':
                    return new ilObjGroupDAV(new ilObjGroup($ref_id, true), $this->repo_helper, $this);

                case 'fold':
                    return new ilObjFolderDAV(new ilObjFolder($ref_id, true), $this->repo_helper, $this);

                case 'file':
                    return new ilObjFileDAV(new ilObjFile($ref_id, true), $this->repo_helper, $this);
            }
        }
        throw new BadRequest('Unknown filetype');
    }


    /**
     * @param $a_title
     * @return bool
     * @throws ilFileUtilsException
     */
    public function isValidFileNameWithValidFileExtension(string $a_title) : bool
    {
        include_once("./Services/Utilities/classes/class.ilFileUtils.php");
        return $a_title == ilFileUtils::getValidFilename($a_title) && $this->isDAVableObjTitle($a_title);
    }
}
