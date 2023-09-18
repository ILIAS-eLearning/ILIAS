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

namespace ILIAS\UI\Component\Dropzone\File;

use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\FileUpload;
use ILIAS\UI\Component\Droppable;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Modal\RoundTrip;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface File extends RoundTrip, Droppable
{
    /**
     * Get the custom title if set.
     */
    public function getTitle(): string;

    /**
     * Returns a signal that can be used to clear the current file queue.
     */
    public function getClearSignal(): Signal;
}
