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

namespace ILIAS\LegalDocuments\test;

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\HTTP\Wrapper\WrapperFactory;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Administration;
use ILIAS\LegalDocuments\Config;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Legacy\Confirmation;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use ILIAS\Data\Result\Ok;
use ILIAS\HTTP\Services as HTTPServices;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\KindlyTo\Group as KindlyToGroup;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\MockObject\Stub;

require_once __DIR__ . '/ContainerMock.php';

class AdministrationTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        /** @var Config&Stub $config */
        $config = $this->createStub(Config::class);
        /** @var Container&Stub $container */
        $container = $this->createStub(Container::class);
        /** @var UI&Stub $ui */
        $ui = $this->createStub(UI::class);

        $this->assertInstanceOf(Administration::class, new Administration($config, $container, $ui));
    }

    public function testDeleteDocumentsConfirmation(): void
    {
        /** @var Config&Stub $config */
        $config = $this->createStub(Config::class);
        /** @var Container&Stub $container */
        $container = $this->createStub(Container::class);
        $ui = $this->mockMethod(UI::class, 'txt', ['sure_delete_documents_p'], 'translated');

        $documents = [$this->doc(9, 'First title'), $this->doc(49, 'Second title')];

        $confirmation = $this->mockMethod(
            Confirmation::class,
            'render',
            [
                'link',
                'submitCommand',
                'cancelCommand',
                'translated',
                [9 => 'First title', 49 => 'Second title']
            ],
            'rendered'
        );

        $instance = new Administration($config, $container, $ui, fn() => $confirmation);
        $this->assertSame('rendered', $instance->deleteDocumentsConfirmation('link', 'submitCommand', 'cancelCommand', $documents));
    }

    public function testDeleteDocuments(): void
    {
        /** @var Container&Stub $container */
        $container = $this->createStub(Container::class);
        /** @var UI&Stub $ui */
        $ui = $this->createStub(UI::class);

        $documents = [
            $this->createStub(Document::class),
            $this->createStub(Document::class),
            $this->createStub(Document::class),
        ];

        $repository = $this->getMockBuilder(DocumentRepository::class)->getMock();

        $consecutive = $documents;
        $repository->expects($this->exactly(count($documents)))->method('deleteDocument')->with(
            $this->callback(function ($value) use (&$consecutive) {
                $this->assertSame(array_shift($consecutive), $value);
                return true;
            })
        );

        $config = $this->mockMethod(Config::class, 'legalDocuments', [], $this->mockMethod(
            Provide::class,
            'document',
            [],
            $this->mockMethod(ProvideDocument::class, 'repository', [], $repository)
        ));

        $instance = new Administration($config, $container, $ui);
        $instance->deleteDocuments($documents);
    }

    public function testWithDocumentAndCriterion(): void
    {
        /** @var Container&Stub $container */
        $container = $this->createStub(Container::class);
        /** @var UI&Stub $ui */
        $ui = $this->createStub(UI::class);

        $criterion = $this->mockMethod(Criterion::class, 'id', [], 98);
        $document = $this->mockMethod(Document::class, 'criteria', [], [
            $this->mockMethod(Criterion::class, 'id', [], 8),
            $criterion,
        ]);

        $repository = $this->mockMethod(DocumentRepository::class, 'find', [459], new Ok($document));
        $config = $this->mockMethod(Config::class, 'legalDocuments', [], $this->mockMethod(
            Provide::class,
            'document',
            [],
            $this->mockMethod(ProvideDocument::class, 'repository', [], $repository)
        ));

        $query_params = ['legal_document_id' => ['459'], 'criterion_id' => '98'];

        $query = $this->mock(ArrayBasedRequestWrapper::class);

        $query
            ->expects($this->exactly(2))
            ->method('retrieve')
            ->willReturnCallback(function ($parameter1) {
                return match ([$parameter1]) {
                    ['legal_document_id'] => [459],
                    ['doc_id'] => null,
                };
            });

        $httpWrapper = $this->mockMethod(WrapperFactory::class, 'query', [], $query, $this->exactly(2));

        $http = $this->mockMethod(HTTPServices::class, 'request', [], $this->mockMethod(
            ServerRequestInterface::class,
            'getQueryParams',
            [],
            $query_params,
            $this->exactly(1)
        ), $this->exactly(1));
        $http->method('wrapper')->willReturn($httpWrapper);

        $refinery = $this->mockMethod(Refinery::class, 'kindlyTo', [], $this->mockMethod(KindlyToGroup::class, 'int', [], $this->mockMethod(
            Transformation::class,
            'applyTo',
            [new Ok('459')],
            new Ok(459)
        ), $this->exactly(3)), $this->exactly(4));

        $container->method('http')->willReturn($http);
        $container->method('refinery')->willReturn($refinery);

        $called = false;
        $instance = new Administration($config, $container, $ui);
        $instance->withDocumentAndCriterion(function ($d, $c) use ($document, $criterion, &$called) {
            $this->assertSame($document, $d);
            $this->assertSame($criterion, $c);
            $called = true;
        });
        $this->assertTrue($called);
    }

    private function doc(int $id, string $title): Document
    {
        $document = $this->getMockBuilder(Document::class)->disableOriginalConstructor()->getMock();
        $document->expects($this->once())->method('id')->willReturn($id);
        $document->expects($this->once())->method('content')->willReturn(
            $this->mockMethod(DocumentContent::class, 'title', [], $title)
        );

        return $document;
    }
}
