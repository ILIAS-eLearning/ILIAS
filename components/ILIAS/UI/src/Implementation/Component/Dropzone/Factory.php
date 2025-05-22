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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Dropzone;

use ILIAS\UI\Component\Dropzone as D;

/**
 * @author  nmaerchy <nm@studer-raimann.ch>
 */
class Factory implements D\Factory
{
    protected File\Factory $file_factory;

    public function __construct(File\Factory $file_factory)
    {
        $this->file_factory = $file_factory;
    }

    public function file(): File\Factory
    {
        return $this->file_factory;
    }
}
