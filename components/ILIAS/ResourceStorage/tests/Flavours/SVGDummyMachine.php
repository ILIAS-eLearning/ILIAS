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

namespace ILIAS\ResourceStorage\Flavours;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Machine\Result;
use ILIAS\ResourceStorage\Information\FileInformation;

/**
 * @internal
 */
class SVGDummyMachine extends DummyMachine
{
    public function __construct()
    {
        $this->load(
            'svg_color_changing_machine',
            'svg_color_changing'
        );
    }


    public function processStream(
        FileInformation $information,
        FileStream $stream,
        FlavourDefinition $for_definition
    ): \Generator {
        $content = (string)$stream;

        $from_color = ':' . $for_definition->getColor() . ';';
        $to_color = ':' . $for_definition->getToColor() . ';';
        $content = str_replace($from_color, $to_color, $content);

        yield new Result(
            $for_definition,
            Streams::ofString($content)
        );
    }
}
