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

namespace ILIAS\MetaData\Repository\Services;

use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Repository\LOMDatabaseRepository;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDFactory;
use ILIAS\MetaData\Repository\Utilities\ScaffoldProvider;
use ILIAS\MetaData\Elements\Scaffolds\ScaffoldFactory;
use ILIAS\MetaData\Elements\Data\DataFactory;
use ILIAS\MetaData\Repository\Utilities\DatabaseManipulator;
use ILIAS\MetaData\Repository\Dictionary\LOMDictionaryInitiator as RepositoryDictionaryInitiator;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface as RepositoryDictionary;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;
use ILIAS\MetaData\Repository\Dictionary\TagFactory as RepositoryTagFactory;
use ILIAS\MetaData\Repository\Utilities\DatabaseReader;
use ILIAS\MetaData\Elements\Factory as ElementFactory;
use ILIAS\MetaData\Repository\Validation\Cleaner;
use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Repository\Validation\Data\DataValidator;
use ILIAS\MetaData\Repository\Validation\Data\DataValidatorService;
use ILIAS\MetaData\Repository\Validation\Dictionary\LOMDictionaryInitiator as ValidationDictionaryInitiator;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ValidationDictionary;
use ILIAS\MetaData\Repository\Validation\Dictionary\TagFactory as ValidationTagFactory;
use ILIAS\MetaData\Vocabularies\Services\Services as VocabulariesServices;
use ILIAS\MetaData\DataHelper\Services\Services as DataHelperServices;
use ILIAS\MetaData\Repository\Utilities\Queries\DatabaseQuerier;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\ResultFactory;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\AssignmentFactory;

class Services
{
    protected RepositoryInterface $repository;
    protected ValidationDictionary $validation_dictionary;
    protected RepositoryDictionary $repository_dictionary;


    protected GlobalContainer $dic;
    protected PathServices $path_services;
    protected StructureServices $structure_services;
    protected VocabulariesServices $vocabularies_services;
    protected DataHelperServices $data_helper_services;

    public function __construct(
        GlobalContainer $dic,
        PathServices $path_services,
        StructureServices $structure_services,
        VocabulariesServices $vocabularies_services,
        DataHelperServices $data_helper_services
    ) {
        $this->dic = $dic;
        $this->path_services = $path_services;
        $this->structure_services = $structure_services;
        $this->vocabularies_services = $vocabularies_services;
        $this->data_helper_services = $data_helper_services;
    }

    public function constraintDictionary(): ValidationDictionary
    {
        if (isset($this->validation_dictionary)) {
            return $this->validation_dictionary;
        }
        return $this->validation_dictionary = (new ValidationDictionaryInitiator(
            new ValidationTagFactory(),
            $this->path_services->pathFactory(),
            $this->structure_services->structure()
        ))->get();
    }

    public function databaseDictionary(): RepositoryDictionary
    {
        if (isset($this->repository_dictionary)) {
            return $this->repository_dictionary;
        }
        return $this->repository_dictionary = (new RepositoryDictionaryInitiator(
            new RepositoryTagFactory(),
            $this->path_services->pathFactory(),
            $this->structure_services->structure()
        ))->get();
    }

    public function repository(): RepositoryInterface
    {
        if (isset($this->repository)) {
            return $this->repository;
        }
        $logger = $this->dic->logger()->meta();
        $data_factory = new DataFactory();
        $querier = new DatabaseQuerier(
            new ResultFactory(),
            $this->dic->database(),
            $logger
        );
        $element_factory = new ElementFactory($data_factory);
        return $this->repository = new LOMDatabaseRepository(
            new RessourceIDFactory(),
            new ScaffoldProvider(
                new ScaffoldFactory($data_factory),
                $this->path_services->pathFactory(),
                $this->path_services->navigatorFactory(),
                $this->structure_services->structure()
            ),
            new DatabaseManipulator(
                $this->databaseDictionary(),
                $querier,
                new AssignmentFactory(),
                $logger
            ),
            new DatabaseReader(
                $element_factory,
                $this->structure_services->structure(),
                $this->databaseDictionary(),
                $this->path_services->navigatorFactory(),
                $querier,
                $logger
            ),
            new Cleaner(
                $element_factory,
                $this->structure_services->structure(),
                new DataValidator(
                    new DataValidatorService(
                        $this->vocabularies_services->vocabularies(),
                        $this->data_helper_services->dataHelper()
                    )
                ),
                $this->constraintDictionary(),
                $logger
            )
        );
    }
}
