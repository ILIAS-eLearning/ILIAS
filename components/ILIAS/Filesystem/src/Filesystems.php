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

namespace ILIAS\Filesystem;

use ILIAS\Filesystem\Exception\IllegalStateException;

/**
 * The Filesystems interface defines the access methods which can be used to fetch the different filesystems.
 *
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
interface Filesystems
{
    /**
     * Fetches the web filesystem.
     * The web filesystem points to the data directory within the ILIAS web root.
     *
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     */
    public function web(): Filesystem;

    /**
     * Fetches the storage filesystem.
     * The storage filesystem is the data directory which located outside of the ILIAS web root.
     *
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     */
    public function storage(): Filesystem;

    /**
     * Fetches the temporary filesystem which can be used for temporary file operations.
     *
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     */
    public function temp(): Filesystem;

    /**
     * Fetches the customizing filesystem which is located at the root of the customizing directory of ILIAS.
     *
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     */
    public function customizing(): Filesystem;

    /**
     * Fetches the libs filesystem which is located at the root of the libs directory of ILIAS. This is read only
     *
     *
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     */
    public function libs(): Filesystem;

    /**
     * Fetches the node_modules filesystem which is located at the root of the libs directory of ILIAS. This is read only
     * @throws IllegalStateException Thrown if the filesystem is requested without initialisation.
     */
    public function nodeModules(): Filesystem;
}
