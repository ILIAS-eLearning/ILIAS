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

class Services
{
    protected GlobalContainer $dic;
    protected PathServices $path_services;
    protected StructureServices $structure_services;
    protected RepositoryServices $repository_services;
    protected VocabulariesServices $vocabularies_services;
    protected EditorServices $editor_services;

    public function __construct(GlobalContainer $dic)
    {
        $this->dic = $dic;
        $this->path_services = new PathServices();
        $this->structure_services = new StructureServices();
        $this->vocabularies_services = new VocabulariesServices(
            $this->path_services,
            $this->structure_services
        );
        $this->repository_services = new RepositoryServices(
            $this->dic,
            $this->path_services,
            $this->structure_services,
            $this->vocabularies_services
        );
        $this->editor_services = new EditorServices(
            $this->dic,
            $this->path_services,
            $this->structure_services,
            $this->repository_services
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
}
