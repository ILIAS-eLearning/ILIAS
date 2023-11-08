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

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Util\Archive\Unzip;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
interface ContainerConsumer
{
    /**
     * @description Currently we allow a Read-Only Unzip of the Container. This Consumers will provide a FileSystem later
     */
    public function getZIP(): Unzip;
}
