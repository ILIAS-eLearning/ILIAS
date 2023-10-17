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

namespace ILIAS\MetaData\Editor\Digest;

use ILIAS\MetaData\Services\InternalServices;

class DigestInitiator
{
    protected InternalServices $services;

    public function __construct(InternalServices $services)
    {
        $this->services = $services;
    }

    public function init(): Digest
    {
        return new Digest(
            $content_assembler = new ContentAssembler(
                $path_factory = $this->services->paths()->pathFactory(),
                $navigator_factory = $this->services->paths()->navigatorFactory(),
                $this->services->dic()->ui()->factory(),
                $this->services->dic()->refinery(),
                $this->services->editor()->presenter(),
                $path_collection = new PathCollection(
                    $path_factory
                ),
                $this->services->editor()->linkFactory(),
                $copyright_handler = new CopyrightHandler($this->services->copyright()->repository()),
                $this->services->dataHelper()->dataHelper()
            ),
            new ManipulatorAdapter(
                $content_assembler,
                $copyright_handler,
                $path_collection,
                $this->services->editor()->manipulator(),
                $path_factory,
                $navigator_factory
            )
        );
    }
}
