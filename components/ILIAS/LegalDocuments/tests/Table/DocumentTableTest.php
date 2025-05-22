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

namespace ILIAS\LegalDocuments\test\Table;

use ilCtrl;
use ILIAS\Data\Factory;
use ILIAS\LegalDocuments\EditLinks;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\TableSelection;
use ILIAS\LegalDocuments\TableConfig;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Table\DocumentModal;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use ILIAS\UI\Renderer;
use ilLegalDocumentsAdministrationGUI;
use ilObjUser;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Table\DocumentTable;
use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

require_once __DIR__ . '/../ContainerMock.php';

class DocumentTableTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->method('__toString')->willReturn('http://myIlias/ilias.php?baseClass=iladministrationgui&cmdNode=2g:qo:gq&cmdClass=ilLegalDocumentsAdministrationGUI&cmd=documents&ref_id=50');

        $request = $this->mock(ServerRequestInterface::class);
        $request->method("getUri")->willReturn($uri);

        $this->assertInstanceOf(DocumentTable::class, new DocumentTable(
            $this->fail(...),
            $this->mock(DocumentRepository::class),
            $this->mock(UI::class),
            $this->mock(DocumentModal::class),
            $this->mock(ilLegalDocumentsAdministrationGUI::class),
            null,
            $request,
            new Factory(),
            $this->mock(ilCtrl::class),
            $this->mock(Renderer::class),
            $this->mockTree(ilObjUser::class, ['getTimeZone' => 'europe/berlin'])
        ));
    }

    public function testCriterionName(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->method('__toString')->willReturn('http://myIlias/ilias.php?baseClass=iladministrationgui&cmdNode=2g:qo:gq&cmdClass=ilLegalDocumentsAdministrationGUI&cmd=documents&ref_id=50');

        $request = $this->mock(ServerRequestInterface::class);
        $request->method("getUri")->willReturn($uri);

        $content = $this->mock(CriterionContent::class);
        $component = $this->mock(Component::class);

        $instance = new DocumentTable(
            function (CriterionContent $c) use ($content, $component) {
                $this->assertSame($content, $c);
                return $component;
            },
            $this->mock(DocumentRepository::class),
            $this->mock(UI::class),
            $this->mock(DocumentModal::class),
            $this->mock(ilLegalDocumentsAdministrationGUI::class),
            null,
            $request,
            new Factory(),
            $this->mock(ilCtrl::class),
            $this->mock(Renderer::class),
            $this->mockTree(ilObjUser::class, ['getTimeZone' => 'europe/berlin'])
        );

        $this->assertSame(
            $component,
            $instance->criterionName($this->mockTree(Criterion::class, ['content' => $content]))
        );
    }
}
