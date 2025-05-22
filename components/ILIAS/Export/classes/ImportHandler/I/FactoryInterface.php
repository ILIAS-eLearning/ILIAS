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

namespace ILIAS\Export\ImportHandler\I;

use ILIAS\Export\ImportHandler\I\File\FactoryInterface as FileFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as PathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as SchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\SchemaFolder\FactoryInterface as SchemaFolderFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\FactoryInterface as ValidationFactoryInterface;

interface FactoryInterface
{
    public function parser(): ParserFactoryInterface;

    public function file(): FileFactoryInterface;

    public function schema(): SchemaFactoryInterface;

    public function schemaFolder(): SchemaFolderFactoryInterface;

    public function path(): PathFactoryInterface;

    public function validation(): ValidationFactoryInterface;
}
