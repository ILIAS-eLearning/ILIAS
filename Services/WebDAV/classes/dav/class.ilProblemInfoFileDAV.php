<?php

use Sabre\DAV\Exception\Forbidden;

class ilProblemInfoFileDAV implements Sabre\DAV\IFile
{
    const PROBLEM_INFO_FILE_NAME = '#!_WEBDAV_INFORMATION.txt';

    const PROBLEM_DUPLICATE_OBJECTNAME = 'duplicate';
    const PROBLEM_FORBIDDEN_CHARACTERS = 'forbidden_characters';
    const PROBLEM_INFO_NAME_DUPLICATE = 'info_name_duplicate';

    /** @var ilObjContainerDAV */
    protected $dav_container;

    /** @var ilWebDAVRepositoryHelper */
    protected $repo_helper;

    /** @var ilWebDAVObjDAVHelper */
    protected $dav_helper;

    public function __construct(
        ilObjContainerDAV $a_dav_container,
        ilWebDAVRepositoryHelper $a_repo_helper,
        ilWebDAVObjDAVHelper $a_dav_helper
    ) {
        $this->dav_container = $a_dav_container;
        $this->repo_helper = $a_repo_helper;
        $this->dav_helper = $a_dav_helper;
    }

    /**
     * Since this is a virtual file, put is not possible
     *
     * @param resource|string $data
     * @return string|null
     * @throws Forbidden
     */
    public function put($data)
    {
        throw new Forbidden("The error info file is virtual and can therefore not be overwritten");
    }

    /**
     * Get the information about problems in the DAV directory
     *
     * @return mixed
     */
    public function get()
    {
        $problem_infos = $this->analyseObjectsOfDAVContainer();
        return $this->createMessageStringFromProblemInfoArray($problem_infos);
    }

    /**
     * Returns the title of the problem info file
     *
     * @return string
     */
    public function getName()
    {
        return self::PROBLEM_INFO_FILE_NAME;
    }

    /**
     * Returns the mime-type for a file which is 'txt/plain'
     *
     * @return string|null
     */
    public function getContentType()
    {
        return 'text/plain';
    }

    /**
     * Returns the ETag for a file
     *
     * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
     *
     * Return null if the ETag can not effectively be determined.
     *
     * The ETag must be surrounded by double-quotes, so something like this
     * would make a valid ETag:
     *
     *   return '"someetag"';
     *
     * @return string|null
     */
    public function getETag()
    {
        return null;
    }

    /**
     * Returns the size of the node, in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return 0;
    }

    /**
     * @param string $a_name
     * @throws Forbidden
     */
    public function setName($a_name)
    {
        throw new Exception\Forbidden("The error info file cannot be renamed");
    }

    /**
     * Analyses objects of the in the constructor given DAV container
     *
     * @return array
     */
    protected function analyseObjectsOfDAVContainer() : array
    {
        // list of titles that were already checked (used for duplicate checking)
        $already_seen_titles = array();

        // Array with 3 different problem topics
        $problem_infos = array(
            self::PROBLEM_DUPLICATE_OBJECTNAME => array(),
            self::PROBLEM_FORBIDDEN_CHARACTERS => array(),
            self::PROBLEM_INFO_NAME_DUPLICATE => false // if a file is already named #!_WEBDAV_INFORMATION.txt (should not be the case)
        );

        // Loop to check every child of the container
        foreach ($this->repo_helper->getChildrenOfRefId($this->dav_container->getRefId()) as $ref_id) {
            $type = $this->repo_helper->getObjectTypeFromRefId($ref_id);
            if ($this->dav_helper->isDAVableObjType($type) && $this->repo_helper->checkAccess('read', $ref_id)) {
                $title = $this->repo_helper->getObjectTitleFromRefId($ref_id);
                if (!$this->dav_helper->hasInvalidPrefixInTitle($title)) {
                    // Check if object is a file with the same name as this info file
                    if ($title == self::PROBLEM_INFO_FILE_NAME) {
                        $problem_infos[self::PROBLEM_INFO_NAME_DUPLICATE] = true;
                    }
                    // Check if title contains forbidden characters
                    elseif ($this->dav_helper->hasTitleForbiddenChars($title)) {
                        $problem_infos[self::PROBLEM_FORBIDDEN_CHARACTERS][] = $title;
                    }
                    // Check for duplicates
                    elseif (in_array($title, $already_seen_titles)) {
                        $problem_infos[self::PROBLEM_DUPLICATE_OBJECTNAME][] = $title;
                    } else {
                        $already_seen_titles[] = $title;
                    }
                }
            }
        }

        return $problem_infos;
    }

    /**
     * Creates a message string out of the found problems in the DAV container
     *
     * @param array $problem_infos
     * @return string
     */
    protected function createMessageStringFromProblemInfoArray(array $problem_infos)
    {
        global $DIC;

        $lng = $DIC->language();
        $message_string = "";

        // If there is a file with the same name of the problem info file -> print message about it
        if ($problem_infos[self::PROBLEM_INFO_NAME_DUPLICATE]) {
            $message_string .= "# " . $lng->txt('webdav_problem_info_duplicate') . "\n\n";
        }

        // Print list with duplicate file names
        $duplicates_list = $problem_infos[self::PROBLEM_DUPLICATE_OBJECTNAME];
        if (count($duplicates_list) > 0) {
            $message_string .= "# " . $lng->txt('webdav_duplicate_detected_title') . "\n";
            foreach ($duplicates_list as $duplicate_title) {
                $message_string .= $duplicate_title . "\n";
            }
            $message_string .= "\n";
        }

        // Print list of files with forbidden characters
        $forbidden_character_titles_list = $problem_infos[self::PROBLEM_FORBIDDEN_CHARACTERS];
        if (count($forbidden_character_titles_list) > 0) {
            $message_string .= "# " . $lng->txt('webdav_forbidden_chars_title') . "\n";
            foreach ($forbidden_character_titles_list as $forbidden_character_title) {
                $message_string .= $forbidden_character_title . "\n";
            }
            $message_string .= "\n";
        }

        // If no problems were found -> create a default message (this happens only if the file is called directly)
        if (strlen($message_string) == 0) {
            $message_string = $lng->txt('webdav_problem_free_container');
        }

        return $message_string;
    }

    /**
     * Deleted the current node
     *
     * @return void
     */
    public function delete()
    {
        throw new \Sabre\DAV\Exception\Forbidden("It is not possible to delete this file since it is just virtual.");
    }

    /**
     * Returns the last modification time, as a unix timestamp. Return null
     * if the information is not available.
     *
     * @return int|null
     */
    public function getLastModified()
    {
        return null;
    }
}
