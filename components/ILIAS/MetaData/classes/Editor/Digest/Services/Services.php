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

namespace ILIAS\MetaData\Editor\Digest\Services;

use ilMDSettings;
use ILIAS\MetaData\Editor\Digest\PathCollection;
use ILIAS\MetaData\Editor\Digest\Digest;
use ILIAS\MetaData\Editor\Digest\CopyrightHandler;
use ILIAS\MetaData\Editor\Digest\ManipulatorAdapter;
use ILIAS\MetaData\Editor\Digest\ContentAssembler;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Editor\Services\InternalServices as InternalEditorServices;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;
use ILIAS\MetaData\Copyright\Services\Services as CopyrightServices;
use ILIAS\MetaData\OERHarvester\Services\Services as PublishingServices;

class Services
{
    protected Digest $digest;
    protected ManipulatorAdapter $manipulator_adapter;
    protected ContentAssembler $content_assembler;
    protected CopyrightHandler $copyright_handler;
    protected PathCollection $path_collection;

    public function __construct(
        protected GlobalContainer $dic,
        protected InternalEditorServices $internal_editor_services,
        protected PathServices $path_services,
        protected DataHelperServices $data_helper_services,
        protected CopyrightServices $copyright_services,
        protected PublishingServices $publishing_services
    ) {
    }

    public function digest(): Digest
    {
        return $this->digest ??= new Digest(
            $this->contentAssembler()
        );
    }

    public function manipulatorAdapter(): ManipulatorAdapter
    {
        return $this->manipulator_adapter ??= new ManipulatorAdapter(
            $this->contentAssembler(),
            $this->copyrightHandler(),
            $this->pathCollection(),
            $this->internal_editor_services->manipulator(),
            $this->path_services->pathFactory(),
            $this->path_services->navigatorFactory(),
            $this->internal_editor_services->vocabularyAdapter()
        );
    }

    protected function contentAssembler(): ContentAssembler
    {
        return $this->content_assembler ??= new ContentAssembler(
            $this->path_services->pathFactory(),
            $this->path_services->navigatorFactory(),
            $this->dic->ui()->factory(),
            $this->dic->refinery(),
            $this->internal_editor_services->presenter(),
            $this->pathCollection(),
            $this->internal_editor_services->linkFactory(),
            $this->copyrightHandler(),
            $this->data_helper_services->dataHelper(),
            $this->internal_editor_services->vocabularyAdapter()
        );
    }

    protected function copyrightHandler(): CopyrightHandler
    {
        return $this->copyright_handler ??= new CopyrightHandler(
            $this->copyright_services->repository(),
            ilMDSettings::_getInstance(),
            $this->publishing_services->settings(),
            $this->copyright_services->identifiersHandler()
        );
    }

    protected function pathCollection(): PathCollection
    {
        return $this->path_collection ??= new PathCollection(
            $this->path_services->pathFactory()
        );
    }
}
