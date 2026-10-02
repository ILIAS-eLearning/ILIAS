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

namespace ILIAS\Search\GUI\Global\Service;

use ILIAS\DI\Container;
use ILIAS\Search\Presentation\Service\Service as PresentationService;
use ILIAS\Search\GUI\Global\Object\Actions as ObjectActions;
use ILIAS\Search\GUI\Global\Object\ActionsImpl as ObjectActionsImpl;
use ILIAS\Search\GUI\Global\SearchStateHandler;
use ILIAS\Search\GUI\Global\SearchStateHandlerImpl;
use ILIAS\Search\GUI\Global\Object\Searcher as ObjectSearcher;
use ILIAS\Search\GUI\Global\Object\Lucene\SearcherImpl as LuceneObjectSearcherImpl;
use ILIAS\Search\GUI\Global\Object\Direct\SearcherImpl as DirectObjectSearcherImpl;
use ILIAS\Data\Factory as DataFactory;
use ilSearchSettings;
use ILIAS\Search\GUI\Global\Object\FilterHandler as ObjectSearchFilterHandler;
use ILIAS\Search\GUI\Global\Object\Direct\FilterHandlerImpl as DirectObjectSearchFilterHandlerImpl;
use ILIAS\Search\GUI\Global\Object\Lucene\FilterHandlerImpl as LuceneObjectSearchFilterHandlerImpl;
use ILIAS\Search\GUI\Global\AccessChecker;
use ILIAS\Search\GUI\Global\AccessCheckerImpl;
use ILIAS\Search\GUI\Global\User\Actions as UserActions;
use ILIAS\Search\GUI\Global\User\ActionsImpl as UserActionsImpl;

class Service
{
    protected ObjectActions $object_actions;
    protected SearchStateHandler $state_handler;
    protected ObjectSearcher $direct_object_searcher;
    protected ObjectSearcher $lucene_object_searcher;
    protected ObjectSearchFilterHandler $direct_object_filter;
    protected ObjectSearchFilterHandler $lucene_object_filter;
    protected UserActions $user_actions;

    public function __construct(
        protected Container $dic,
        protected PresentationService $presentation_service
    ) {
    }

    public function accessChecker(): AccessChecker
    {
        return new AccessCheckerImpl(
            $this->dic->user(),
            $this->dic->rbac()->system(),
            ilSearchSettings::getInstance()
        );
    }

    public function objectSearchActions(): ObjectActions
    {
        return $this->object_actions ??= new ObjectActionsImpl(
            $this->dic->ctrl(),
            new DataFactory()
        );
    }

    public function searchStateHandler(): SearchStateHandler
    {
        return $this->state_handler ??= new SearchStateHandlerImpl(
            $this->dic->http(),
            $this->dic->refinery()
        );
    }

    public function luceneObjectSearcher(): ObjectSearcher
    {
        return $this->lucene_object_searcher ??= new LuceneObjectSearcherImpl(
            $this->dic->ui()->mainTemplate(),
            $this->dic->ui()->renderer(),
            $this->presentation_service->result(),
            $this->dic->language()
        );
    }

    public function directObjectSearcher(): ObjectSearcher
    {
        return $this->direct_object_searcher ??= new DirectObjectSearcherImpl(
            ilSearchSettings::getInstance(),
            $this->dic->ui()->mainTemplate(),
            $this->dic->ui()->renderer(),
            $this->presentation_service->result(),
            $this->dic->language()
        );
    }

    public function directObjectSearchFilterHandler(): ObjectSearchFilterHandler
    {
        return $this->direct_object_filter ??= new DirectObjectSearchFilterHandlerImpl(
            ilSearchSettings::getInstance(),
            $this->dic->learningObjectMetadata()
        );
    }

    public function luceneObjectSearchFilterHandler(): ObjectSearchFilterHandler
    {
        return $this->lucene_object_filter ??= new LuceneObjectSearchFilterHandlerImpl(
            ilSearchSettings::getInstance(),
            $this->dic->learningObjectMetadata()
        );
    }

    public function userSearchActions(): UserActions
    {
        return $this->user_actions ??= new UserActionsImpl(
            $this->dic->ctrl(),
            new DataFactory()
        );
    }
}
