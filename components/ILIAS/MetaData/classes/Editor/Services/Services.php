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

namespace ILIAS\MetaData\Editor\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\Manipulator\Services\Services as ManipulatorServices;
use ILIAS\MetaData\Presentation\Services\Services as PresentationServices;
use ILIAS\MetaData\Vocabularies\Services\Services as VocabulariesServices;
use ILIAS\MetaData\Editor\Full\Services\Services as FullEditorServices;
use ILIAS\MetaData\Editor\Digest\Services\Services as DigestServices;
use ILIAS\MetaData\Copyright\Services\Services as CopyrightServices;
use ILIAS\MetaData\OERHarvester\Services\Services as PublishingServices;

class Services
{
    protected FullEditorServices $full_editor_services;
    protected DigestServices $digest_services;
    protected InternalServices $internal_services;

    public function __construct(
        protected GlobalContainer $dic,
        protected PathServices $path_services,
        protected StructureServices $structure_services,
        protected RepositoryServices $repository_services,
        protected ManipulatorServices $manipulator_services,
        protected PresentationServices $presentation_services,
        protected VocabulariesServices $vocabularies_services,
        protected DataHelperServices $data_helper_services,
        protected CopyrightServices $copyright_services,
        protected PublishingServices $publishing_services
    ) {
    }

    public function fullEditor(): FullEditorServices
    {
        return $this->full_editor_services ??= new FullEditorServices(
            $this->dic,
            $this->internal(),
            $this->path_services,
            $this->repository_services,
            $this->data_helper_services
        );
    }

    public function digest(): DigestServices
    {
        return $this->digest_services ??= new DigestServices(
            $this->dic,
            $this->internal(),
            $this->path_services,
            $this->data_helper_services,
            $this->copyright_services,
            $this->publishing_services
        );
    }

    public function internal(): InternalServices
    {
        return $this->internal_services ??= new InternalServices(
            $this->dic,
            $this->path_services,
            $this->structure_services,
            $this->repository_services,
            $this->manipulator_services,
            $this->presentation_services,
            $this->vocabularies_services
        );
    }
}
