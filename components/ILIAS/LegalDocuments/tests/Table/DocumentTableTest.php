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
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\Value\Criterion;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Table\DocumentModal;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Repository\DocumentRepository;
use ILIAS\UI\Renderer;
use ilLegalDocumentsAdministrationGUI;
use ilObjUser;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Table\DocumentTable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

require_once __DIR__ . '/../ContainerMock.php';

class DocumentTableTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $uri = $this->stub(UriInterface::class);
        $uri->method('__toString')->willReturn('http://myIlias/ilias.php?baseClass=iladministrationgui&cmdNode=2g.qo.gq&cmdClass=ilLegalDocumentsAdministrationGUI&cmd=documents&ref_id=50');

        $request = $this->stub(ServerRequestInterface::class);
        $request->method("getUri")->willReturn($uri);

        $this->assertInstanceOf(DocumentTable::class, new DocumentTable(
            $this->fail(...),
            $this->stub(DocumentRepository::class),
            $this->stub(UI::class),
            $this->stub(DocumentModal::class),
            $this->stub(ilLegalDocumentsAdministrationGUI::class),
            null,
            $request,
            new Factory(),
            $this->stub(ilCtrl::class),
            $this->stub(Renderer::class),
            $this->stubTree(ilObjUser::class, ['getTimeZone' => 'europe/berlin'])
        ));
    }

    public function testCriterionName(): void
    {
        $uri = $this->stub(UriInterface::class);
        $uri->method('__toString')->willReturn('http://myIlias/ilias.php?baseClass=iladministrationgui&cmdNode=2g.qo.gq&cmdClass=ilLegalDocumentsAdministrationGUI&cmd=documents&ref_id=50');

        $request = $this->stub(ServerRequestInterface::class);
        $request->method("getUri")->willReturn($uri);

        $content = $this->stub(CriterionContent::class);
        $component = $this->stub(Component::class);

        $instance = new DocumentTable(
            function (CriterionContent $c) use ($content, $component) {
                $this->assertSame($content, $c);
                return $component;
            },
            $this->stub(DocumentRepository::class),
            $this->stub(UI::class),
            $this->stub(DocumentModal::class),
            $this->stub(ilLegalDocumentsAdministrationGUI::class),
            null,
            $request,
            new Factory(),
            $this->stub(ilCtrl::class),
            $this->stub(Renderer::class),
            $this->stubTree(ilObjUser::class, ['getTimeZone' => 'europe/berlin'])
        );

        $this->assertSame(
            $component,
            $instance->criterionName($this->stubTree(Criterion::class, ['content' => $content]))
        );
    }
}
