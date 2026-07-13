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

use ILIAS\MetaData\Editor\Full\Components\Actions\Actions;
use ILIAS\MetaData\Editor\Full\Components\Inputs\InputFactory;
use ILIAS\MetaData\Editor\Full\Components\Tables\TableFactory;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Repository\Services\Services as RepositoryServices;
use ILIAS\MetaData\Editor\Services\InternalServices as InternalEditorServices;
use ILIAS\MetaData\Editor\Full\Components\Actions\LinkProvider;
use ILIAS\MetaData\Editor\Full\Components\Actions\ButtonFactory;
use ILIAS\MetaData\Editor\Full\Components\Actions\ModalFactory;
use ILIAS\MetaData\Editor\Full\Components\Inputs\Conditions\FactoryWithConditionTypesService;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;
use ILIAS\MetaData\Editor\Full\Components\PropertiesFetcher;
use ILIAS\MetaData\Editor\Full\Components\FormFactory;
use ILIAS\MetaData\Editor\Full\Components\DataFinder;

class InternalServices
{
    protected Actions $actions;
    protected InputFactory $input_factory;
    protected PropertiesFetcher $properties_fetcher;
    protected FormFactory $form_factory;
    protected TableFactory $table_factory;
    protected DataFinder $data_finder;
    protected LinkProvider $link_provider;

    public function __construct(
        protected GlobalContainer $dic,
        protected PathServices $path_services,
        protected RepositoryServices $repository_services,
        protected InternalEditorServices $internal_editor_services,
        protected DataHelperServices $data_helper_services
    ) {
    }

    public function dataFinder(): DataFinder
    {
        return $this->data_finder ??= new DataFinder();
    }

    public function inputFactory(): InputFactory
    {
        if (isset($this->input_factory)) {
            return $this->input_factory;
        }
        $field_factory = $this->dic->ui()->factory()->input()->field();
        $refinery = $this->dic->refinery();
        $presenter = $this->internal_editor_services->presenter();
        $path_factory = $this->path_services->pathFactory();
        $vocabulary_adapter = $this->internal_editor_services->vocabularyAdapter();
        return $this->input_factory = new InputFactory(
            $field_factory,
            $refinery,
            $presenter,
            $path_factory,
            $this->dataFinder(),
            $this->repository_services->databaseDictionary(),
            new FactoryWithConditionTypesService(
                $field_factory,
                $presenter,
                $this->repository_services->constraintDictionary(),
                $refinery,
                $path_factory,
                $this->data_helper_services->dataHelper(),
                $vocabulary_adapter
            ),
            $vocabulary_adapter
        );
    }

    public function propertiesFetcher(): PropertiesFetcher
    {
        return $this->properties_fetcher ??= new PropertiesFetcher(
            $this->internal_editor_services->dictionary(),
            $this->internal_editor_services->presenter(),
            $this->dataFinder()
        );
    }

    public function actions(): Actions
    {
        if (isset($this->actions)) {
            return $this->actions;
        }
        $ui_factory = $this->dic->ui()->factory();
        $presenter = $this->internal_editor_services->presenter();
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
        return $this->form_factory ??= new FormFactory(
            $this->dic->ui()->factory(),
            $this->linkProvider(),
            $this->inputFactory(),
            $this->internal_editor_services->dictionary(),
            $this->path_services->navigatorFactory()
        );
    }

    public function tableFactory(): TableFactory
    {
        return $this->table_factory ??= new TableFactory(
            $this->dic->ui()->factory(),
            $this->dic->ui()->renderer(),
            $this->internal_editor_services->presenter(),
            $this->dataFinder(),
            $this->actions()->getButton()
        );
    }

    protected function linkProvider(): LinkProvider
    {
        return $this->link_provider ??= new LinkProvider(
            $this->internal_editor_services->linkFactory(),
            $this->path_services->pathFactory()
        );
    }
}
