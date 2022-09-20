<?php
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

/**
 *
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilFSStoragePreview extends ilFileSystemAbstractionStorage
{
    /**
     * Constructor
     *
     * @access public
     * @param int storage type
     * @param bool En/Disable automatic path conversion. If enabled files with id 123 will be stored in directory files/1/file_123
     * @param int object id of container (e.g file_id or mob_id)
     *
     */
    public function __construct(int $a_container_id = 0)
    {
        parent::__construct(self::STORAGE_WEB, true, $a_container_id);
    }

    /**
     * Get directory name. E.g for files => file
     * Only relative path, no trailing slash
     * '_<obj_id>' will be appended automatically
     *
     * @access protected
     *
     * @return string directory name
     */
    protected function getPathPostfix(): string
    {
        return "preview";
    }

    /**
     * Get path prefix. Prefix that will be prepended to the path
     * No trailing slash. E.g ilFiles for files
     *
     * @access protected
     *
     * @return string path prefix e.g files
     */
    protected function getPathPrefix(): string
    {
        return "previews";
    }
}
