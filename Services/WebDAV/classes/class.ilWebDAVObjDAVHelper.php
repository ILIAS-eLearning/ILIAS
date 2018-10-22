<?php

/**
 * Class ilWebDAVObjectDAVFactory
 */
class ilWebDAVObjDAVHelper
{
    /** @var ilWebDAVRepositoryHelper */
    protected $repo_helper;

    public function __construct(ilWebDAVRepositoryHelper $repo_helper)
    {
        $this->repo_helper = $repo_helper;
    }

    public function isDAVableObject($id, $is_reference = true)
    {
        $ref_id = $is_reference ? $id : $this->repo_helper->getRefIdFromObjId($id);

        $type = $this->repo_helper->getObjectTypeFromRefId($ref_id);
        $title = $this->repo_helper->getObjectTitleFromRefId($ref_id);

        $is_davable = $this->isDAVableObjType($type) && $this->isDAVableObjTitle($title);
        return $is_davable;
    }

    /**
     * @param $type
     * @return bool
     */
    public function isDAVableObjType($type) : bool
    {
        switch($type)
        {
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
     * @param $title
     * @return bool
     */
    public function isDAVableObjTitle($title)
    {
        return ($this->hasTitleForbiddenChars($title) === false)
            && ($this->hasInvalidPrefixInTitle($title) === false);
    }

    /**
     * @param $title
     * @return bool
     */
    public function hasTitleForbiddenChars($title)
    {
        foreach(str_split('\\<>/:*?"|#') as $forbidden_character)
        {
            if(strpos($title, $forbidden_character) !== false)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Forbidden are titles that begin with a dot. There are also forbidden extensions like '.$' or '..'. But since
     * they both start with a single dot, we can aim only for that.
     * @param $title
     * @return bool
     */
    public function hasInvalidPrefixInTitle($title)
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
    public function createDAVObjectForRefId($ref_id, $type = '') : ilObjectDAV
    {
        if($type == '')
        {
            $type = $this->repo_helper->getObjectTypeFromRefId($ref_id);
        }

        if($this->repo_helper->objectWithRefIdExists($ref_id))
        {
            switch($type)
            {
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
}