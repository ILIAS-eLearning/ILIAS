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

use ILIAS\MetaData\Editor\Full\Services\Actions\Actions;
use ILIAS\MetaData\Editor\Full\Services\Inputs\InputFactory;
use ILIAS\MetaData\Editor\Full\Services\Tables\TableFactory;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\Editor\Services\Services as EditorServices;
use ILIAS\MetaData\Vocabularies\Services\Services as VocabulariesServices;
use ILIAS\MetaData\Editor\Full\Services\Actions\LinkProvider;
use ILIAS\MetaData\Editor\Full\Services\Actions\ButtonFactory;
use ILIAS\MetaData\Editor\Full\Services\Actions\ModalFactory;
use ILIAS\MetaData\Editor\Full\Services\Inputs\Conditions\FactoryWithConditionTypesService;
use ILIAS\MetaData\Manipulator\Services\Services as ManipulatorServices;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;

class Services
{
    protected Actions $actions;
    protected InputFactory $input_factory;
    protected PropertiesFetcher $properties_fetcher;
    protected FormFactory $form_factory;
    protected TableFactory $table_factory;
    protected DataFinder $data_finder;
    protected ManipulatorAdapter $manipulator_adapter;
    protected LinkProvider $link_provider;

    protected GlobalContainer $dic;
    protected PathServices $path_services;
    protected RepositoryServices $repository_services;
    protected VocabulariesServices $vocabularies_services;
    protected EditorServices $editor_services;
    protected ManipulatorServices $manipulator_services;
    protected DataHelperServices $data_helper_services;

    public function __construct(
        GlobalContainer $dic,
        PathServices $path_services,
        RepositoryServices $repository_services,
        VocabulariesServices $vocabularies_services,
        ManipulatorServices $manipulator_services,
        EditorServices $editor_services,
        DataHelperServices $data_helper_services
    ) {
        $this->dic = $dic;
        $this->path_services = $path_services;
        $this->repository_services = $repository_services;
        $this->vocabularies_services = $vocabularies_services;
        $this->editor_services = $editor_services;
        $this->manipulator_services = $manipulator_services;
        $this->data_helper_services = $data_helper_services;
    }

    public function dataFinder(): DataFinder
    {
        if (isset($this->data_finder)) {
            return $this->data_finder;
        }
        return $this->data_finder = new DataFinder();
    }

    public function inputFactory(): InputFactory
    {
        if (isset($this->input_factory)) {
            return $this->input_factory;
        }
        $field_factory = $this->dic->ui()->factory()->input()->field();
        $refinery = $this->dic->refinery();
        $presenter = $this->editor_services->presenter();
        $path_factory = $this->path_services->pathFactory();
        $vocabularies = $this->vocabularies_services->vocabularies();
        return $this->input_factory = new InputFactory(
            $field_factory,
            $refinery,
            $presenter,
            $path_factory,
            $this->path_services->navigatorFactory(),
            $this->dataFinder(),
            $vocabularies,
            $this->repository_services->databaseDictionary(),
            new FactoryWithConditionTypesService(
                $field_factory,
                $presenter,
                $this->repository_services->constraintDictionary(),
                $vocabularies,
                $refinery,
                $path_factory,
                $this->data_helper_services->dataHelper()
            )
        );
    }

    public function propertiesFetcher(): PropertiesFetcher
    {
        if (isset($this->properties_fetcher)) {
            return $this->properties_fetcher;
        }
        return $this->properties_fetcher = new PropertiesFetcher(
            $this->editor_services->dictionary(),
            $this->editor_services->presenter(),
            $this->dataFinder()
        );
    }

    public function actions(): Actions
    {
        if (isset($this->actions)) {
            return $this->actions;
        }
        $ui_factory = $this->dic->ui()->factory();
        $presenter = $this->editor_services->presenter();
        $link_provider = $this->linkProvider();
        return $this->actions = new Actions(
            $link_provider,
            new ButtonFactory(
                $ui_factory,
                $presenter
            ),
            new ModalFactory(
                $link_provider,
                $ui_factory,
                $presenter,
                $this->propertiesFetcher(),
                $this->formFactory(),
                $this->repository_services->constraintDictionary(),
                $this->path_services->pathFactory()
            )
        );
    }

    public function formFactory(): FormFactory
    {
        if (isset($this->form_factory)) {
            return $this->form_factory;
        }
        return $this->form_factory = new FormFactory(
            $this->dic->ui()->factory(),
            $this->linkProvider(),
            $this->inputFactory(),
            $this->editor_services->dictionary(),
            $this->path_services->navigatorFactory()
        );
    }

    public function tableFactory(): TableFactory
    {
        if (isset($this->table_factory)) {
            return $this->table_factory;
        }
        return $this->table_factory = new TableFactory(
            $this->dic->ui()->factory(),
            $this->dic->ui()->renderer(),
            $this->editor_services->presenter(),
            $this->dataFinder(),
            $this->actions()->getButton()
        );
    }

    public function manipulatorAdapter(): ManipulatorAdapter
    {
        if (isset($this->manipulator_adapter)) {
            return $this->manipulator_adapter;
        }
        return $this->manipulator_adapter = new ManipulatorAdapter(
            $this->editor_services->manipulator(),
            $this->formFactory(),
            $this->path_services->pathFactory(),
            $this->path_services->navigatorFactory()
        );
    }

    protected function linkProvider(): LinkProvider
    {
        if (isset($this->link_provider)) {
            return $this->link_provider;
        }
        return $this->link_provider = new LinkProvider(
            $this->editor_services->linkFactory(),
            $this->path_services->pathFactory()
        );
    }
}
