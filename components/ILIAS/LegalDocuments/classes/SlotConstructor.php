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

namespace ILIAS\LegalDocuments;

use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\ConditionDefinition;
use Closure;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\Repository\DatabaseDocumentRepository;
use ILIAS\LegalDocuments\Repository\ReadOnlyDocumentRepository;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use ILIAS\LegalDocuments\Repository\DatabaseHistoryRepository;
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\ConsumerSlots\CriterionToCondition;

class SlotConstructor
{
    public function __construct(private readonly string $id, private readonly Container $container, private readonly UserAction $action)
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function history(ProvideDocument $document): ProvideHistory
    {
        return new ProvideHistory(
            $this->id(),
            new DatabaseHistoryRepository(
                $this->id(),
                $document->repository(),
                $this->container->database(),
                $this->action
            ),
            $document,
            $this->container
        );
    }

    /**
     * @param array<string, ConditionDefinition> $conditions
     * @param array<string, Closure(DocumentContent): Component $content_as_component
     */
    public function document(DocumentRepository $document_repository, array $conditions, array $content_as_component): ProvideDocument
    {
        return new ProvideDocument($this->id(), $document_repository, $conditions, $content_as_component, $this->container);
    }

    public function documentRepository(): DocumentRepository
    {
        return new DatabaseDocumentRepository(
            $this->id(),
            $this->container->database(),
            $this->action
        );
    }

    public function readOnlyDocuments(DocumentRepository $document_repository): DocumentRepository
    {
        return new ReadOnlyDocumentRepository($document_repository);
    }

    /**
     * @param Closure(): void $finished
     * @return Closure(): list<Component>
     */
    public function withdrawalFinished(Closure $finished): Closure
    {
        return function () use ($finished): array {
            if (($this->container->http()->request()->getQueryParams()['withdrawal_finished'] ?? null) === $this->id()) {
                $finished();
            }
            return [];
        };
    }
}
