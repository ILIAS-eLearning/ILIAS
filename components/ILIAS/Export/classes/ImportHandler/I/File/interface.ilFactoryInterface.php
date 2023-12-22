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

namespace ILIAS\Export\ImportHandler\I\File;

use ILIAS\Export\ImportHandler\I\File\Validation\ilFactoryInterface as ilFileValidationFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XML\ilFactoryInterface as ilXMLFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\XSD\ilFactoryInterface as ilXSDFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Path\ilFactoryInterface as ilFilePathFactoryInterface;
use ILIAS\Export\ImportHandler\I\File\Namespace\ilFactoryInterface as ilFileNamespaceFactoryInterface;

interface ilFactoryInterface
{
    public function xml(): ilXMLFileFactoryInterface;

    public function xsd(): ilXSDFileFactoryInterface;

    public function validation(): ilFileValidationFactoryInterface;

    public function path(): ilFilePathFactoryInterface;

    public function namespace(): ilFileNamespaceFactoryInterface;
}
