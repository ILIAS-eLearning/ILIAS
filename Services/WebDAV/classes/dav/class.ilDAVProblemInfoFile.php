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
use Sabre\DAV\Exception\NotFound;

class ilDAVProblemInfoFile implements Sabre\DAV\IFile
{
    public const PROBLEM_INFO_FILE_NAME = '#!_WEBDAV_INFORMATION.txt';

    public const PROBLEM_DUPLICATE_OBJECTNAME = 'duplicate';
    public const PROBLEM_FORBIDDEN_CHARACTERS = 'forbidden_characters';
    public const PROBLEM_INFO_NAME_DUPLICATE = 'info_name_duplicate';

    protected int $container_ref_id;
    protected ilWebDAVRepositoryHelper $repo_helper;
    protected ilWebDAVObjFactory $dav_object_factory;
    protected ilLanguage $language;

    public function __construct(
        int $container_ref_id,
        ilWebDAVRepositoryHelper $repo_helper,
        ilWebDAVObjFactory $dav_object_factory,
        ilLanguage $language
    ) {
        $this->container_ref_id = $container_ref_id;
        $this->repo_helper = $repo_helper;
        $this->dav_object_factory = $dav_object_factory;
        $this->language = $language;
    }

    public function put($data)
    {
        throw new Forbidden("The error info file is virtual and can therefore not be overwritten");
    }

    public function get()
    {
        $problem_infos = $this->analyseObjectsOfDAVContainer();
        return $this->createMessageStringFromProblemInfoArray($problem_infos);
    }

    public function getName()
    {
        return self::PROBLEM_INFO_FILE_NAME;
    }

    public function getContentType()
    {
        return 'text/plain';
    }

    public function getETag()
    {
        return null;
    }

    public function getSize(): int
    {
        return 0;
    }

    public function setName($a_name): void
    {
        throw new Forbidden("The error info file cannot be renamed");
    }

    /**
     * @return array<string, array<int, string>|bool>
     */
    protected function analyseObjectsOfDAVContainer(): array
    {
        $already_seen_titles = array();

        $problem_infos = array(
            self::PROBLEM_DUPLICATE_OBJECTNAME => array(),
            self::PROBLEM_FORBIDDEN_CHARACTERS => array(),
            self::PROBLEM_INFO_NAME_DUPLICATE => false
        );

        foreach ($this->repo_helper->getChildrenOfRefId($this->container_ref_id) as $ref_id) {
            try {
                $dav_object = $this->dav_object_factory->retrieveDAVObjectByRefID($ref_id);

                $title = $dav_object->getName();

                if ($title == self::PROBLEM_INFO_FILE_NAME) {
                    $problem_infos[self::PROBLEM_INFO_NAME_DUPLICATE] = true;
                } elseif (in_array($title, $already_seen_titles)) {
                    $problem_infos[self::PROBLEM_DUPLICATE_OBJECTNAME][] = $title;
                } else {
                    $already_seen_titles[] = $title;
                }
            } catch (ilWebDAVNotDavableException $e) {
                if ($e->getMessage() === ilWebDAVNotDavableException::OBJECT_TITLE_NOT_DAVABLE) {
                    $title = $this->repo_helper->getObjectTitleFromRefId($ref_id);
                    $problem_infos[self::PROBLEM_FORBIDDEN_CHARACTERS][] = $title;
                }
            } catch (Forbidden | NotFound | RuntimeException $e) {
            }
        }

        return $problem_infos;
    }

    /**
     * @param array[] $problem_infos
     */
    protected function createMessageStringFromProblemInfoArray(array $problem_infos): string
    {
        $message_string = "";

        if ($problem_infos[self::PROBLEM_INFO_NAME_DUPLICATE]) {
            $message_string .= "# " . $this->language->txt('webdav_problem_info_duplicate') . "\n\n";
        }

        $duplicates_list = $problem_infos[self::PROBLEM_DUPLICATE_OBJECTNAME];
        if (count($duplicates_list) > 0) {
            $message_string .= "# " . $this->language->txt('webdav_duplicate_detected_title') . "\n";
            foreach ($duplicates_list as $duplicate_title) {
                $message_string .= $duplicate_title . "\n";
            }
            $message_string .= "\n";
        }

        $forbidden_character_titles_list = $problem_infos[self::PROBLEM_FORBIDDEN_CHARACTERS];
        if (count($forbidden_character_titles_list) > 0) {
            $message_string .= "# " . $this->language->txt('webdav_forbidden_chars_title') . "\n";
            foreach ($forbidden_character_titles_list as $forbidden_character_title) {
                $message_string .= $forbidden_character_title . "\n";
            }
            $message_string .= "\n";
        }

        if (strlen($message_string) == 0) {
            $message_string = $this->language->txt('webdav_problem_free_container');
        }

        return $message_string;
    }

    public function delete(): void
    {
        throw new Forbidden("It is not possible to delete this file since it is just virtual.");
    }

    public function getLastModified()
    {
        return time();
    }
}
