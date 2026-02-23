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

namespace ILIAS\Search\GUI\Service;

use ILIAS\DI\Container;
use ILIAS\Search\Presentation\Service\Service as PresentationService;
use ILIAS\Search\GUI\Actions;
use ILIAS\Search\GUI\ActionsImpl;
use ILIAS\Search\GUI\SearchStateHandler;
use ILIAS\Search\GUI\Lucene\SearchStateHandlerImpl as LuceneSearchStateHandlerImpl;
use ILIAS\Search\GUI\Direct\SearchStateHandlerImpl as DirectSearchStateHandlerImpl;
use ILIAS\Search\GUI\Searcher;
use ILIAS\Search\GUI\Lucene\SearcherImpl as LuceneSearcherImpl;
use ILIAS\Search\GUI\Direct\SearcherImpl as DirectSearcherImpl;
use ILIAS\Data\Factory as DataFactory;
use ilSearchSettings;

class Service
{
    protected Actions $actions;
    protected SearchStateHandler $lucene_search_state_handler;
    protected SearchStateHandler $direct_search_state_handler;
    protected Searcher $lucene_searcher;
    protected Searcher $direct_searcher;

    public function __construct(
        protected Container $dic,
        protected PresentationService $presentation_service
    ) {
    }

    public function actions(): Actions
    {
        return $this->actions ??= new ActionsImpl(
            $this->dic->ctrl(),
            new DataFactory()
        );
    }

    public function luceneSearchStateHandler(): SearchStateHandler
    {
        return $this->lucene_search_state_handler ??= new LuceneSearchStateHandlerImpl(
            ilSearchSettings::getInstance(),
            $this->dic->learningObjectMetadata(),
            $this->dic->http(),
            $this->dic->refinery()
        );
    }

    public function directSearchStateHandler(): SearchStateHandler
    {
        return $this->direct_search_state_handler ??= new DirectSearchStateHandlerImpl(
            ilSearchSettings::getInstance(),
            $this->dic->learningObjectMetadata(),
            $this->dic->http(),
            $this->dic->refinery()
        );
    }

    public function luceneSearcher(): Searcher
    {
        return $this->lucene_searcher ??= new LuceneSearcherImpl(
            ilSearchSettings::getInstance(),
            $this->dic->ui()->mainTemplate(),
            $this->dic->ui()->renderer(),
            $this->presentation_service->result(),
            $this->dic->language()
        );
    }

    public function directSearcher(): Searcher
    {
        return $this->direct_searcher ??= new DirectSearcherImpl(
            ilSearchSettings::getInstance(),
            $this->dic->ui()->mainTemplate(),
            $this->dic->ui()->renderer(),
            $this->presentation_service->result(),
            $this->dic->language()
        );
    }
}
