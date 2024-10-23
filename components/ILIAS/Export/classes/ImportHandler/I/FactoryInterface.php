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

use ILIAS\Export\ImportHandler\I\File\FactoryInterface as ilImportHandlerFileFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ilImportHandlerParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\FactoryInterface as ilImportHandlerPathFactoryInterface;
use ILIAS\Export\ImportHandler\I\Schema\FactoryInterface as ilImportHandlerSchemaFactoryInterface;
use ILIAS\Export\ImportHandler\I\Validation\FactoryInterface as ilImportHandlerValidationFactoryInterface;

interface FactoryInterface
{
    public function parser(): ilImportHandlerParserFactoryInterface;

    public function file(): ilImportHandlerFileFactoryInterface;

    public function schema(): ilImportHandlerSchemaFactoryInterface;

    public function path(): ilImportHandlerPathFactoryInterface;

    public function validation(): ilImportHandlerValidationFactoryInterface;
}
