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

namespace ILIAS\FileUpload;

/**
 * Interface Location
 *
 * Defines the valid filesystem locations for the file upload service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
interface Location
{
    /**
     * The filesystem within the ilias web root.
     * Equal to the filesystem->web
     */
    public const WEB = 1;
    /**
     * The filesystem outside of the ilias web root.
     * Equal to the filesystem->storage
     */
    public const STORAGE = 2;
    /**
     * The filesystem within the web root where all the skins and plugins are saved.
     * Equal to the filesystem->customizing
     */
    public const CUSTOMIZING = 3;
    /**
     * The ILIAS temporary directory.
     * Equal to the filesystem->temp
     */
    public const TEMPORARY = 4;
}
