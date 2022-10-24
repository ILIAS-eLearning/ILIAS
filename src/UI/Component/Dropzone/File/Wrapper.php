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

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Implementation\Component\Signal;

/**
 * A wrapper file dropzone wraps around any other component from the UI framework, e.g. a calendar entry.
 * Any wrapper dropzone is highlighted as soon as some files are dragged over the browser window.
 * Dropping the files opens a modal where the user can start the upload process.
 * @author  nmaerchy <nm@studer-raimann.ch>
 */
interface Wrapper extends File, Triggerable
{
    /**
     * Get the components being wrapped by this dropzone.
     *
     * @return Component[]
     */
    public function getContent(): array;

    /**
     * gets the Signal to clear the file-list in the modal of a wrapper dropzone.
     */
    public function getClearSignal(): Signal;
}
