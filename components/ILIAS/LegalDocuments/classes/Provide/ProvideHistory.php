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

namespace ILIAS\LegalDocuments\Provide;

use ILIAS\Data\Result;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\Legacy\Table;
use ILIAS\LegalDocuments\Repository\HistoryRepository;
use ILIAS\LegalDocuments\Table\HistoryTable;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\TableConfig;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Table\DocumentModal;
use ILIAS\UI\Component\Component;
use ilObjUser;
use Closure;

class ProvideHistory
{
    /** @var Closure(object, string, TableInterface): Table */
    private readonly Closure $create_table_gui;

    /**
     * @param null|Closure(object, string, TableInterface): Table $create_table_gui
     */
    public function __construct(
        private readonly string $id,
        private readonly HistoryRepository $repository,
        private readonly ProvideDocument $document,
        private readonly Container $container,
        ?Closure $create_table_gui = null
    ) {
        $this->create_table_gui = $create_table_gui ?? fn($gui, $command, $t) => new Table($gui, $command, $t);
    }

    public function table(object $gui, string $command, string $reset_command, string $auto_complete_command): Component
    {
        $auto_complete_link = $this->container->ctrl()->getLinkTarget($gui, $auto_complete_command, '', true);
        $ui = new UI($this->id, $this->container->ui()->factory(), $this->container->ui()->mainTemplate(), $this->container->language());
        $modal = new DocumentModal($this->container->ui(), $this->document->contentAsComponent(...));
        $create = fn(string $class, ...$args) => $class === ilObjUser::class && !ilObjUser::_lookupLogin($args[0]) ?
                null :
                new $class(...$args);

        $table = ($this->create_table_gui)($gui, $command, new HistoryTable(
            $this->repository,
            $this->document,
            $reset_command,
            $auto_complete_link,
            $ui,
            $modal,
            $create
        ));

        return $this->container->ui()->factory()->legacy($table->getHTML());
    }

    public function acceptDocument(ilObjUser $user, Document $document): void
    {
        $this->repository->acceptDocument($user, $document);
    }

    public function alreadyAccepted(ilObjUser $user, Document $document): bool
    {
        return $this->repository->alreadyAccepted($user, $document);
    }

    /**
     * @return Result<DocumentContent>
     */
    public function acceptedVersion(ilObjUser $user): Result
    {
        return $this->repository->acceptedVersion($user);
    }

    /**
     * @return Result<Document>
     */
    public function currentDocumentOfAcceptedVersion(ilObjUser $user): Result
    {
        return $this->repository->currentDocumentOfAcceptedVersion($user);
    }
}
