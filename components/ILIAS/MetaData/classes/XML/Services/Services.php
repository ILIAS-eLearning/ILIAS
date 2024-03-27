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

namespace ILIAS\MetaData\XML\Services;

use ILIAS\MetaData\XML\Writer\Standard\Standard as StandardWriter;
use ILIAS\MetaData\XML\Writer\WriterInterface;
use ILIAS\MetaData\XML\Dictionary\LOMDictionaryInitiator;
use ILIAS\MetaData\XML\Dictionary\TagFactory;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;
use ILIAS\MetaData\XML\Copyright\CopyrightHandler;
use ILIAS\MetaData\XML\Reader\Standard\Standard as StandardReader;
use ILIAS\MetaData\XML\Reader\ReaderInterface;
use ILIAS\MetaData\Elements\Markers\MarkerFactory;
use ILIAS\MetaData\Manipulator\Services\Services as ManipulatorServices;
use ILIAS\MetaData\XML\Reader\Standard\StructurallyCoupled;
use ILIAS\MetaData\XML\Reader\Standard\Legacy;

class Services
{
    protected WriterInterface $standard_writer;
    protected ReaderInterface $standard_reader;

    protected PathServices $path_services;
    protected StructureServices $structure_services;
    protected ManipulatorServices $manipulator_services;

    public function __construct(
        PathServices $path_services,
        StructureServices $structure_services,
        ManipulatorServices $manipulator_services
    ) {
        $this->path_services = $path_services;
        $this->structure_services = $structure_services;
        $this->manipulator_services = $manipulator_services;
    }

    public function standardWriter(): WriterInterface
    {
        if (isset($this->standard_writer)) {
            return $this->standard_writer;
        }
        $dictionary = (new LOMDictionaryInitiator(
            new TagFactory(),
            $this->path_services->pathFactory(),
            $this->structure_services->structure()
        ))->get();
        return $this->standard_writer = new StandardWriter(
            $dictionary,
            new CopyrightHandler()
        );
    }

    public function standardReader(): ReaderInterface
    {
        if (isset($this->standard_reader)) {
            return $this->standard_reader;
        }
        $dictionary = (new LOMDictionaryInitiator(
            new TagFactory(),
            $this->path_services->pathFactory(),
            $this->structure_services->structure()
        ))->get();
        $marker_factory = new MarkerFactory();
        $copyright_handler = new CopyrightHandler();
        return $this->standard_reader = new StandardReader(
            new StructurallyCoupled(
                $marker_factory,
                $this->manipulator_services->scaffoldProvider(),
                $dictionary,
                $copyright_handler
            ),
            new Legacy(
                $marker_factory,
                $this->manipulator_services->scaffoldProvider(),
                $copyright_handler
            )
        );
    }
}
