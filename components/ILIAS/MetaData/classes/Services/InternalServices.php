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

namespace ILIAS\MetaData\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;
use ILIAS\MetaData\Vocabularies\Services\Services as VocabulariesServices;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\Editor\Services\Services as EditorServices;
use ILIAS\MetaData\Manipulator\Services\Services as ManipulatorServices;
use ILIAS\MetaData\Copyright\Services\Services as CopyrightServices;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;
use ILIAS\MetaData\Presentation\Services\Services as PresentationServices;

class InternalServices
{
    protected GlobalContainer $dic;
    protected PathServices $path_services;
    protected StructureServices $structure_services;
    protected DataHelperServices $data_helper_services;
    protected PresentationServices $presentation_services;
    protected RepositoryServices $repository_services;
    protected VocabulariesServices $vocabularies_services;
    protected EditorServices $editor_services;
    protected ManipulatorServices $manipulator_services;
    protected CopyrightServices $copyright_services;

    public function __construct(GlobalContainer $dic)
    {
        $this->dic = $dic;
        $this->structure_services = new StructureServices();
        $this->path_services = new PathServices(
            $this->structure_services
        );
        $this->data_helper_services = new DataHelperServices();
        $this->presentation_services = new PresentationServices(
            $this->dic,
            $this->data_helper_services
        );
        $this->vocabularies_services = new VocabulariesServices(
            $this->path_services,
            $this->structure_services
        );
        $this->repository_services = new RepositoryServices(
            $this->dic,
            $this->path_services,
            $this->structure_services,
            $this->vocabularies_services,
            $this->data_helper_services
        );
        $this->manipulator_services = new ManipulatorServices(
            $this->path_services,
            $this->repository_services
        );
        $this->editor_services = new EditorServices(
            $this->dic,
            $this->path_services,
            $this->structure_services,
            $this->repository_services,
            $this->manipulator_services,
            $this->presentation_services
        );
        $this->copyright_services = new CopyrightServices(
            $this->dic
        );
    }

    public function dic(): GlobalContainer
    {
        return $this->dic;
    }

    public function paths(): PathServices
    {
        return $this->path_services;
    }

    public function structure(): StructureServices
    {
        return $this->structure_services;
    }

    public function dataHelper(): DataHelperServices
    {
        return $this->data_helper_services;
    }

    public function presentation(): PresentationServices
    {
        return $this->presentation_services;
    }

    public function repository(): RepositoryServices
    {
        return $this->repository_services;
    }

    public function vocabularies(): VocabulariesServices
    {
        return $this->vocabularies_services;
    }

    public function editor(): EditorServices
    {
        return $this->editor_services;
    }

    public function manipulator(): ManipulatorServices
    {
        return $this->manipulator_services;
    }

    public function copyright(): CopyrightServices
    {
        return $this->copyright_services;
    }
}
