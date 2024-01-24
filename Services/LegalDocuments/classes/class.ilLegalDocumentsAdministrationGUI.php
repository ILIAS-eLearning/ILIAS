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

use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\Config;
use ILIAS\DI\Container;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\DocumentId;
use ILIAS\LegalDocuments\Administration;
use ILIAS\LegalDocuments\AdministrationEditLinks;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\HTMLPurifier;

class ilLegalDocumentsAdministrationGUI
{
    private readonly Container $container;
    private readonly UI $ui;
    private readonly Administration $admin;

    public function __construct(
        private readonly string $parent_class,
        private readonly Config $config,
        private readonly Closure $after_document_deletion,
        ?Container $container = null
    ) {
        $this->container = $container ?? $GLOBALS['DIC'];
        $this->container->language()->loadLanguageModule('ldoc');
        $this->ui = new UI(
            $this->config->legalDocuments()->id(),
            $this->container->ui()->factory(),
            $this->container->ui()->mainTemplate(),
            $this->container->language()
        );
        $this->admin = new Administration($this->config, $this->container, $this->ui);
    }

    public function executeCommand(): void
    {
        $cmd = $this->container->ctrl()->getCmd('documents');
        if (!$this->isCommand($cmd)) {
            throw new Exception('Unknown command: ' . $cmd);
        }
        $this->$cmd();
    }

    public function history(): void
    {
        $this->container->tabs()->activateTab('history');
        $this->admin->setContent($this->config->legalDocuments()->history()->table(
            $this,
            'history',
            'historyResetFilter',
            'searchUser'
        ));
    }

    public function searchUser(): void
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(['login', 'firstname', 'lastname', 'email']);
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if ($this->container->http()->wrapper()->query()->has('fetchall')) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        if ($this->container->http()->wrapper()->query()->has('term')) {
            $query = ilUtil::stripSlashes(
                $this->container->http()->wrapper()->query()->retrieve('term', $this->container->refinery()->kindlyTo()->string())
            );
            $response = $this->container->http()
                                        ->response()
                                        ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                                        ->withBody(Streams::ofString($auto->getList($query)));
            $this->container->http()->saveResponse($response);
        }

        $this->container->http()->sendResponse();
        $this->container->http()->close();
    }

    public function historyResetFilter(): void
    {
        $this->history();
    }

    public function addDocument(): void
    {
        $this->admin->requireEditable();
        $this->ctrlTo('setParameterByClass', 'hash', $this->config->legalDocuments()->document()->hash());
        $this->ctrlTo('redirectByClass', 'editDocument');
    }

    public function addCriterion(): void
    {
        $this->admin->requireEditable();
        $this->container->tabs()->clearTargets();
        $this->container->tabs()->setBackTarget($this->container->language()->txt('back'), $this->ctrlTo('getLinkTargetByClass', 'documents'));

        $document = $this->admin->currentDocument()->value();

        $this->container->language()->loadLanguageModule('meta');

        $url = $this->admin->targetWithDoc($this, $document, 'addCriterion', 'getFormAction');
        $form = $this->admin->criterionForm($url, $document);

        $form = $this->admin->withFormData($form, function (array $x) use ($document) {
            $content = new CriterionContent(...$x[0]['content']);
            $this->config->legalDocuments()->document()->validateCriteriaContent($document->criteria(), $content)->map(
                fn() => $this->config->legalDocuments()->document()->repository()->createCriterion($document, $content)
            )->except($this->criterionInvalid(...))->value();

            $this->returnWithMessage('doc_crit_attached', 'documents');
        });

        $this->admin->setContent($form);
    }

    public function editCriterion(): void
    {
        $this->admin->requireEditable();
        $this->admin->withDocumentAndCriterion(function (Document $document, Criterion $criterion) {
            $this->container->language()->loadLanguageModule('meta');
            $url = $this->admin->targetWithDocAndCriterion($this, $document, $criterion, 'editCriterion', 'getFormAction');
            $form = $this->admin->criterionForm($url, $document, $criterion->content());
            $form = $this->admin->withFormData($form, function (array $data) use ($document, $criterion) {
                $content = new CriterionContent(...$data[0]['content']);
                $criteria = array_filter($document->criteria(), fn(Criterion $other) => $other->id() !== $criterion->id());
                $this->config->legalDocuments()->document()->validateCriteriaContent($criteria, $content)->map(
                    fn() => $this->config->legalDocuments()->document()->repository()->updateCriterionContent($criterion->id(), $content)
                )->except($this->criterionInvalid(...))->value();

                $this->returnWithMessage('doc_crit_changed', 'documents');
            });

            $this->container->tabs()->clearTargets();
            $this->container->tabs()->setBackTarget($this->container->language()->txt('back'), $this->ctrlTo('getLinkTargetByClass', 'documents'));
            $condition = $this->config->legalDocuments()->document()->toCondition($criterion->content());
            $this->container->ui()->mainTemplate()->setTitle(join(' - ', [$document->content()->title(), $condition->definition()->translatedType()]));
            $this->admin->setContent($form);
        });
    }

    public function deleteCriterion(): void
    {
        $this->admin->requireEditable();
        $this->admin->withDocumentAndCriterion(function (Document $document, Criterion $criterion) {
            $this->config->legalDocuments()->document()->repository()->deleteCriterion($criterion->id());
            $this->returnWithMessage('doc_crit_detached', 'documents');
        });
    }

    public function upload(): void
    {
        $this->admin->requireEditable();
        $this->admin->idOrHash($this, function (Closure $link, string $title, DocumentId $id) {
            $raw_content = $this->admin->uploadContent();
            $sanitised_value = trim((new HTMLPurifier())->purify($raw_content));
            if ($this->admin->isInvalidHTML($sanitised_value)) {
                $sanitised_value = nl2br($sanitised_value);
            }

            $this->config->legalDocuments()->document()->repository()->updateDocumentContent($id, new DocumentContent('html', $title, $sanitised_value));
            $this->admin->exitWithJsonResponse(['status' => 1]);
        });
    }

    public function documents(): void
    {
        $this->container->language()->loadLanguageModule('meta');
        $this->container->tabs()->activateTab('documents');

        if ($this->config->editable()) {
            $this->container->toolbar()->addStickyItem($this->admin->addDocumentButton($this->ctrlTo('getLinkTargetByClass', 'addDocument')));
        }

        $edit_links = $this->config->editable() ? new AdministrationEditLinks($this, $this->admin) : null;
        $this->admin->setContent($this->config->legalDocuments()->document()->table($this, __FUNCTION__, $edit_links));
    }

    public function deleteDocuments(): void
    {
        $this->deleteDocumentsConfirmation($this->admin->retrieveDocuments());
    }

    public function deleteDocument(): void
    {
        $this->deleteDocumentsConfirmation([$this->admin->currentDocument()->value()]);
    }

    public function deleteConfirmed(): void
    {
        $this->admin->requireEditable();
        $docs = $this->admin->retrieveDocuments();
        $this->admin->deleteDocuments($docs);
        ($this->after_document_deletion)();
        $this->returnWithMessage(count($docs) === 1 ? 'deleted_documents_s' : 'deleted_documents_p', 'documents');
    }

    public function editDocument(): void
    {
        $this->admin->requireEditable();
        $this->container->tabs()->clearTargets();
        $this->admin->idOrHash($this, function (Closure $link, string $title, DocumentId $id, bool $may_be_new) {
            $content = fn() => $this->config->legalDocuments()->document()->repository()->findId($id)->map(fn($d) => $d->content());
            $form = $this->admin->documentForm($link, $title, $content, $may_be_new);
            $form = $this->admin->withFormData($form, function ($data) use (/* $edit_link, */$id) {
                $this->config->legalDocuments()->document()->repository()->updateDocumentTitle($id, $data[0]['title']);
                $this->returnWithMessage('saved_successfully', 'documents');
            });

            $this->container->tabs()->setBackTarget($this->container->language()->txt('back'), $this->ctrlTo('getLinkTargetByClass', 'documents'));
            $this->container->tabs()->activateTab('documents');
            $this->admin->setContent($form);
        });
    }

    public function saveOrder(): void
    {
        $this->admin->requireEditable();
        $this->admin->withDocumentsAndOrder($this->admin->saveDocumentOrder(...));
        $this->returnWithMessage('saved_successfully', 'documents');
    }

    /**
     * @param array<string, Closure(): void> $run_after
     */
    public function tabs(array $run_after = []): void
    {
        $this->admin->tabs($this->admin->defaultTabs(
            $this->ctrlTo('getLinkTargetByClass', 'documents'),
            $this->ctrlTo('getLinkTargetByClass', 'history')
        ), $run_after);
    }

    public function admin(): Administration
    {
        return $this->admin;
    }

    private function ctrlTo(string $method, ...$args)
    {
        $path = [$this->parent_class, self::class];
        if ($method === 'setParameterByClass') {
            $path = self::class;
        }
        return $this->container->ctrl()->$method($path, ...$args);
    }

    private function isCommand(string $cmd): bool
    {
        $reflection = new ReflectionClass($this);
        return $reflection->hasMethod($cmd)
            && $reflection->getMethod($cmd)->isPublic()
            && (string) $reflection->getMethod($cmd)->getReturnType() === 'void'
            && $reflection->getMethod($cmd)->getNumberOfParameters() === 0;
    }

    /**
     * @param list<Document> $documents
     */
    private function deleteDocumentsConfirmation(array $documents): void
    {
        $this->admin->requireEditable();
        $this->container->tabs()->activateTab('documents');
        $this->admin->setContent($this->admin->deleteDocumentsConfirmation(
            $this->ctrlTo('getFormActionByClass', 'ignored'),
            'deleteConfirmed',
            'documents',
            $documents
        ));
    }

    /**
     * @param string|Exception $error
     */
    private function criterionInvalid($error): Result
    {
        if (!is_string($error)) {
            return new Error($error);
        }

        $message = match ($error) {
            ProvideDocument::CRITERION_ALREADY_EXISTS => $this->ui->txt('criterion_assignment_must_be_unique'),
            ProvideDocument::CRITERION_WOULD_NEVER_MATCH => $this->ui->txt('criterion_assignment_cannot_match'),
            default => $error,
        };

        return new Ok($this->ui->mainTemplate()->setOnScreenMessage('failure', $message, true));
    }

    private function returnWithMessage(string $message, string $command): void
    {
        $this->ui->mainTemplate()->setOnScreenMessage('success', $this->ui->txt($message), true);
        $this->ctrlTo('redirectByClass', $command);
    }
}
