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

use ILIAS\UI\Component\Button\Button;
use ILIAS\LegalDocuments\DocumentId;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use ilDatePresentation;
use ilDateTime;
use InvalidArgumentException;
use ILIAS\Filesystem\Stream\Streams;
use Closure;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\LegalDocuments\DocumentId\NumberId;
use ILIAS\LegalDocuments\DocumentId\HashId;
use ILIAS\LegalDocuments\FileUpload\UploadHandler;
use ILIAS\LegalDocuments\FileUpload\PreProcessor;
use Exception;
use DateTimeImmutable;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Legacy\Confirmation;
use ilObjUserFolderGUI;

class Administration
{
    /** @var Closure(): Confirmation */
    private readonly Closure $confirmation;

    /**
     * @param null|Closure(): Confirmation $confirmation
     */
    public function __construct(
        private readonly Config $config,
        private readonly Container $container,
        private readonly UI $ui,
        ?Closure $confirmation = null
    ) {
        $this->confirmation = $confirmation ?? fn() => new Confirmation($this->container->language());
    }

    /**
     * @param list<Document> $documents
     */
    public function deleteDocumentsConfirmation(string $form_link, string $submit_command, string $cancel_command, array $documents): string
    {
        $items = array_column(array_map(fn($x) => [$x->id(), $x->content()->title()], $documents), 1, 0);

        return (($this->confirmation)($this->container->language()))->render(
            $form_link,
            $submit_command,
            $cancel_command,
            $this->ui->txt('sure_reset_tos'),
            $items
        );
    }

    /**
     * @param list<Document> $documents
     */
    public function deleteDocuments(array $documents): void
    {
        array_map(
            $this->config->legalDocuments()->document()->repository()->deleteDocument(...),
            $documents
        );
    }

    /**
     * @param Closure(Document, Criterion): void $proc
     */
    public function withDocumentAndCriterion(Closure $proc): void
    {
        $document = $this->currentDocument()->value();
        $criterion_id = ($this->container->http()->request()->getQueryParams()['criterion_id'] ?? null);
        if (null === $criterion_id) {
            throw new InvalidArgumentException('Missing query parameter criterion_id.');
        }
        $criterion_id = (int) $criterion_id;

        $criterion = $this->find(
            fn($criterion) => $criterion->id() === $criterion_id,
            $document->criteria()
        );
        if (!$criterion) {
            throw new InvalidArgumentException('Invalid criterion_id given.');
        }

        $proc($document, $criterion);
    }

    public function retrieveDocuments(): array
    {
        $ids = $this->retrieveIds();
        $documents = $this->config->legalDocuments()->document()->repository()->select($ids);
        if (count($documents) !== count($ids)) {
            throw new InvalidArgumentException('List contains invalid documents.');
        }

        return $documents;
    }

    public function retrieveIds(): array
    {
        return $this->container->http()->wrapper()->post()->retrieve('ids', $this->container->refinery()->to()->listOf($this->container->refinery()->kindlyTo()->int()));
    }

    /**
     * @param Closure(Closure(string): string, DocumentId): void $then
     */
    public function idOrHash(object $gui, Closure $then): void
    {
        $with_doc_id = fn($document) => $then($this->willLinkWith($gui, ['doc_id' => $document->id()]), $document->content()->title(), new NumberId($document));
        $with_hash = fn(string $hash) => $then($this->willLinkWith($gui, ['hash' => $hash]), '', new HashId($hash));
        $try_hash = fn() => new Ok($with_hash($this->requireDocumentHash()));

        $this->currentDocument()
             ->map($with_doc_id)
             ->except($try_hash)
             ->value();

    }

    public function targetWithDoc(object $gui, $document, string $cmd, string $method = 'getLinkTarget'): string
    {
        $link = $this->willLinkWith($gui, ['doc_id' => (string) $document->id()]);
        return $link($cmd, $method);
    }

    public function targetWithDocAndCriterion(object $gui, $document, $criterion, string $cmd, string $method = 'getLinkTarget')
    {
        $link = $this->willLinkWith($gui, [
            'doc_id' => (string) $document->id(),
            'criterion_id' => (string) $criterion->id(),
        ]);

        return $link($cmd, $method);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function willLinkWith(object $gui, array $parameters = []): Closure
    {
        return function (string $cmd, string $method = 'getLinkTarget') use ($gui, $parameters): string {
            foreach ($parameters as $key => $value) {
                $this->container->ctrl()->setParameter($gui, $key, $value);
            }
            $link = $this->container->ctrl()->$method($gui, $cmd);
            foreach ($parameters as $key => $_) {
                $this->container->ctrl()->setParameter($gui, $key, '');
            }

            return $link;
        };
    }

    /**
     * @param Closure(array): void $then
     */
    public function withFormData($form, Closure $then)
    {
        $request = $this->container->http()->request();
        if ($request->getMethod() !== 'POST') {
            return $form;
        }
        $form = $form->withRequest($request);
        $data = $form->getData();

        if ($data !== null) {
            $then($data);
        }

        return $form;
    }

    /**
     * @template A
     * @param Closure(A): bool $predicate
     * @param list<A> $array
     * @return A|null
     */
    public function find(Closure $predicate, array $array)
    {
        foreach ($array as $value) {
            if ($predicate($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return Result<Document>
     */
    public function currentDocument(): Result
    {
        $repo = $this->config->legalDocuments()->document()->repository();
        $doc_id = $this->container->http()->request()->getQueryParams()['doc_id'] ?? null;
        return $this->container->refinery()->kindlyTo()->int()->applyTo(new Ok($doc_id))->then($repo->find(...));
    }

    public function criterionForm(string $url, $criterion = null)
    {
        $groups = $this->config->legalDocuments()->document()->conditionGroups($criterion);
        $group = $this->ui->create()->input()->field()->switchableGroup($groups, $this->ui->txt('form_criterion'));
        if ($criterion) {
            $group = $group->withValue($criterion->type());
        }
        return $this->ui->create()->input()->container()->form()->standard($url, [
            'content' => $group,
        ]);
    }

    public function requireDocumentHash(): string
    {
        return $this->container->http()->wrapper()->query()->retrieve('hash', $this->container->refinery()->to()->string());
    }

    /**
     * @param list<array{0: string, 1: string, 2: string}> $tabs
     * @param array<string, Closure(): void> $run_after
     */
    public function tabs(array $tabs, array $run_after = []): void
    {
        foreach ($tabs as $tab) {
            $this->container->tabs()->addTab(...$tab);
            if (isset($run_after[$tab[0]])) {
                $run_after[$tab[0]]();
            }
        }
    }

    public function defaultTabs(string $documents_link, string $history_link): array
    {
        return [
            ['documents', $this->ui->txt('agreement_documents_tab_label'), $documents_link],
            ['history', $this->ui->txt('acceptance_history'), $history_link],
        ];
    }

    public function uploadContent(): string
    {
        $value = null;
        $upload = $this->container->upload();
        $upload->register(new PreProcessor(function (string $content) use (&$value): void {
            $value = $content;
        }));
        $upload->process();
        $result_array = $upload->getResults();
        if (count($result_array) !== 1 || !current($result_array)->isOk()) {
            throw new Exception('Unexpected upload result.');
        }

        return $value;
    }

    /**
     * @param list<Component>|Component $component
     */
    public function setContent($component): void
    {
        $this->ui->mainTemplate()->setContent($this->render($component));
    }

    public function addDocumentButton(string $add_document_link): Component
    {
        return $this->ui->create()->button()->primary(
            $this->ui->txt('add_document_btn_label'),
            $add_document_link
        );
    }

    /**
     * @param list<Component>|Component $component
     */
    public function setVariable(string $variable, $component): void
    {
        $this->ui->mainTemplate()->setVariable($variable, $this->render($component));
    }

    /**
     * @param list<Component>|Component|string $component
     */
    public function render($component): string
    {
        if (is_string($component)) {
            return $component;
        }
        return $this->container->ui()->renderer()->render($component);
    }

    /**
     * @param list<Button> $buttons
     */
    public function resetBox(DateTimeImmutable $reset_date, array $buttons = []): Component
    {
        $reset_date = new ilDateTime($reset_date->getTimeStamp(), IL_CAL_UNIX);
        return $this->ui->create()
                               ->messageBox()
                               ->info(sprintf($this->ui->txt('last_reset_date'), ilDatePresentation::formatDate($reset_date)))
                               ->withButtons($buttons);
    }

    public function resetButton(string $confirm_reset_link): Component
    {
        return $this->ui->create()->button()->standard(
            $this->ui->txt('reset_for_all_users'),
            $confirm_reset_link
        );
    }

    /**
     * @param Closure(string): string $link
     */
    public function documentForm(Closure $link, string $title): Component
    {
        $edit_link = $link('editDocument');
        return $this->ui->create()->input()->container()->form()->standard($edit_link, [
            'title' => $this->ui->create()->input()->field()->text('Title')->withRequired(true)->withValue($title),
            'content' => $this->ui->create()->input()->field()->file(new UploadHandler($link), $this->ui->txt('form_document'))->withAcceptedMimeTypes([
                'text/html',
                'text/plain',
            ]),
        ]);
    }

    /**
     * @param list<Document> $documents
     * @param array<int, int> $order_by_document
     */
    public function saveDocumentOrder(array $documents, array $order_by_document): void
    {
        $update = $this->config->legalDocuments()->document()->repository()->updateDocumentOrder(...);

        usort($documents, fn($document, $other) => $order_by_document[$document->id()] - $order_by_document[$other->id()]);

        array_map(
            fn($document, int $order) => $update(new NumberId($document), $order),
            $documents,
            range(10, 10 * count($documents), 10)
        );
    }

    /**
     * @param Closure(list<Document>, array<int, int>) $proc
     */
    public function withDocumentsAndOrder(Closure $proc)
    {
        // kindlyTo->int() does not accept numbers of the form "01".
        $to_int = $this->container->refinery()->byTrying([
            $this->container->refinery()->kindlyTo()->int(),
            $this->container->refinery()->in()->series([
                $this->container->refinery()->to()->string(),
                $this->container->refinery()->custom()->transformation(fn($s) => ltrim($s, '0') ?: '0'),
                $this->container->refinery()->kindlyTo()->int(),
            ]),
        ]);

        $order = $this->container->http()->request()->getParsedBody()['order'] ?? null;
        if (!is_array($order)) {
            throw new InvalidArgumentException('Invalid order given. List of numbers expected.');
        }

        $order = array_map($to_int, $order);
        $document_ids = array_map($to_int, array_keys($order));
        $order = array_combine($document_ids, array_values($order));

        $documents = $this->config->legalDocuments()->document()->repository()->all();

        foreach ($documents as $document) {
            if (!isset($order[$document->id()])) {
                $order[$document->id()] = $document->meta()->sorting();
            }
        }

        return $proc($documents, $order);
    }

    public function exitWithJsonResponse($value): void
    {
        // ... The content type cannot be set to application/json, because the components/ILIAS/UI/src/templates/js/Input/Field/file.js:392
        //     does not expect that the content type is correct and parses it again ...
        $this->container->http()->saveResponse($this->container->http()->response()/* ->withHeader('Content-Type', 'application/json') */->withBody(
            Streams::ofString(json_encode($value))
        ));

        $this->container->http()->sendResponse();
        $this->container->http()->close();
    }

    public function requireEditable(): void
    {
        if (!$this->config->editable()) {
            throw new Exception('Access denied.');
        }
    }

    public function externalSettingsMessage(bool $enabled): Component
    {
        return $this->ui->create()->messageBox()->info(
            $this->ui->txt('withdrawal_usr_deletion') . ': ' . $this->ui->txt($enabled ? 'enabled' : 'disabled')
        )->withLinks([
            $this->ui->create()->link()->standard(
                $this->ui->txt('adm_external_setting_edit'),
                $this->container->ctrl()->getLinkTargetByClass(ilObjUserFolderGUI::class, 'generalSettings')
            )
        ]);
    }

    public function isInvalidHTML(string $string): bool
    {
        return !$this->isValidHTML($string);
    }

    public function isValidHTML(string $string): bool
    {
        return (new ValidHTML())->isTrue($string);
    }
}
