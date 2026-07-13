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

namespace ILIAS\MetaData\Editor\Full\Services;

use ILIAS\MetaData\Editor\Full\Services\InternalServices as FullEditorInternalServices;
use ILIAS\MetaData\Editor\Full\FullEditor;
use ILIAS\MetaData\Editor\Full\RootContent;
use ILIAS\MetaData\Editor\Full\PanelContent;
use ILIAS\MetaData\Editor\Full\FormContent;
use ILIAS\MetaData\Editor\Full\TableContent;
use ILIAS\MetaData\Editor\Full\ManipulatorAdapter;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;
use ILIAS\MetaData\Editor\Services\InternalServices as InternalEditorServices;

class Services
{
    protected ManipulatorAdapter $manipulator_adapter;
    protected FullEditor $full_editor;
    protected FullEditorInternalServices $internal_services;

    public function __construct(
        protected GlobalContainer $dic,
        protected InternalEditorServices $internal_editor_services,
        protected PathServices $path_services,
        protected RepositoryServices $repository_services,
        protected DataHelperServices $data_helper_services
    ) {
    }

    public function fullEditor(): FullEditor
    {
        return $this->full_editor ??= new FullEditor(
            $this->internal_editor_services->dictionary(),
            $this->path_services->navigatorFactory(),
            new FormContent(
                $this->internal()->actions(),
                $this->internal()->formFactory()
            ),
            new TableContent(
                $this->internal()->actions(),
                $this->internal()->tableFactory()
            ),
            $panel_content = new PanelContent(
                $this->internal()->actions(),
                $this->internal()->propertiesFetcher(),
                $ui_factory = $this->dic->ui()->factory(),
                $presenter = $this->internal_editor_services->presenter()
            ),
            new RootContent(
                $this->internal()->actions(),
                $ui_factory,
                $presenter,
                $panel_content
            ),
        );
    }

    public function manipulatorAdapter(): ManipulatorAdapter
    {
        return $this->manipulator_adapter ??= new ManipulatorAdapter(
            $this->internal_editor_services->manipulator(),
            $this->internal()->formFactory(),
            $this->path_services->pathFactory(),
            $this->path_services->navigatorFactory()
        );
    }

    protected function internal(): InternalServices
    {
        return $this->internal_services ??= new FullEditorInternalServices(
            $this->dic,
            $this->path_services,
            $this->repository_services,
            $this->internal_editor_services,
            $this->data_helper_services
        );
    }
}
