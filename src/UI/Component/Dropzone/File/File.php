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

use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\FileUpload;
use ILIAS\UI\Component\Droppable;
use ILIAS\UI\Component\Triggerable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface File extends FileUpload, Form, Droppable
{
    /**
     * Get a dropzone like this, but showing a custom title in the appearing modal.
     */
    public function withTitle(string $title): File;

    /**
     * Get the custom title if set.
     */
    public function getTitle(): string;
}
