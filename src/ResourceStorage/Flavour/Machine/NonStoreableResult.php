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

namespace ILIAS\ResourceStorage\Flavour\Machine;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class NonStoreableResult extends Result
{
    protected FlavourDefinition $definition;
    protected FileStream $stream;

    public function __construct(
        FlavourDefinition $definition,
        FileStream $stream,
        int $index = 0
    ) {
        $this->definition = $definition;
        $this->stream = $stream;
        parent::__construct($definition, $stream, $index, false);
    }
}
