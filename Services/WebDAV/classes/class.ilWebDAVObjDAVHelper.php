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

    public function isDAVableObject($ref_id, $is_reference = true)
    {
        $type = $this->repo_helper->getObjectTypeFromRefId($ref_id);
        return $this->isDAVableObjType($type);
    }

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