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

namespace ILIAS\ResourceStorage\Flavours;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Consumer\StreamAccess\Token;
use ILIAS\ResourceStorage\Consumer\StreamAccess\TokenStream;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Flavour\FlavourBuilder;
use ILIAS\ResourceStorage\Flavour\Machine\Factory;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Resource\Repository\FlavourRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\FileRevision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class FlavorTest
 * @author Fabian Schmid <fabian@sr.solutions>
 */
require_once __DIR__ . '/../AbstractBaseTest.php';

class FlavourTest extends AbstractBaseTest
{
    public $resource_builder;
    private const BASE_DIR = '/var';
    private Factory $machine_factory;
    private StorageHandler $storage_handler_mock;
    /**
     * @var StorageHandlerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storage_handler_factory;
    private FlavourRepository $flavour_repo;
    private StreamAccess $stream_access;

    protected function setUp(): void
    {
        $this->machine_factory = new Factory(new \ILIAS\ResourceStorage\Flavour\Engine\Factory());
        $this->storage_handler_mock = $this->createMock(StorageHandler::class);
        $this->storage_handler_mock->expects($this->any())->method('isPrimary')->willReturn(true);
        $this->storage_handler_factory = new StorageHandlerFactory([
            $this->storage_handler_mock
        ], self::BASE_DIR);
        $this->flavour_repo = $this->createMock(FlavourRepository::class);
        $this->resource_builder = $this->createMock(ResourceBuilder::class);
        $this->stream_access = new StreamAccess(self::BASE_DIR, $this->storage_handler_factory);
    }


    public function testDefinitionVariantNameLengths(): void
    {
        $flavour_builder = new FlavourBuilder(
            $this->flavour_repo,
            $this->machine_factory,
            $this->resource_builder,
            $this->storage_handler_factory,
            $this->stream_access
        );

        // Length OK
        $flavour_definition = $this->createMock(FlavourDefinition::class);
        $flavour_definition->expects($this->exactly(2))
            ->method('getVariantName')
            ->willReturn(str_repeat('a', 768));

        $flavour_builder->has(
            new ResourceIdentification('1'),
            $flavour_definition
        );
        $this->assertTrue(true); // no exception thrown

        // Too long
        $flavour_definition = $this->createMock(FlavourDefinition::class);
        $flavour_definition->expects($this->exactly(2))
            ->method('getVariantName')
            ->willReturn(str_repeat('a', 769));

        $this->expectException(\InvalidArgumentException::class);
        $flavour_builder->has(
            new ResourceIdentification('1'),
            $flavour_definition
        );
    }

    public function testHasFlavour(): void
    {
        // Data
        $rid_one = new ResourceIdentification('1');
        $rid_two = new ResourceIdentification('2');
        $flavour_builder = new FlavourBuilder(
            $this->flavour_repo,
            $this->machine_factory,
            $this->resource_builder,
            $this->storage_handler_factory,
            $this->stream_access
        );

        // Expectations
        $flavour_definition = $this->createMock(FlavourDefinition::class);
        $flavour_definition->expects($this->exactly(4))
            ->method('getVariantName')
            ->willReturn('short');

        $this->flavour_repo->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([$rid_one, 0, $flavour_definition], [$rid_two, 0, $flavour_definition])
            ->willReturnOnConsecutiveCalls(false, true);


        // Assertions
        $this->assertFalse(
            $flavour_builder->has(
                $rid_one,
                $flavour_definition
            )
        );
        $this->assertTrue(
            $flavour_builder->has(
                $rid_two,
                $flavour_definition
            )
        );
    }

    public function testNewFlavour(): void
    {
        // Data
        $rid_one = new ResourceIdentification('1');
        $flavour_builder = new FlavourBuilder(
            $this->flavour_repo,
            $this->machine_factory,
            $this->resource_builder,
            $this->storage_handler_factory,
            $this->stream_access
        );

        // Expectations
        $flavour_definition = $this->createMock(FlavourDefinition::class);
        $flavour_definition->expects($this->any())
            ->method('getVariantName')
            ->willReturn('short');

        $this->flavour_repo->expects($this->once())
            ->method('has')
            ->with($rid_one, 0, $flavour_definition)
            ->willReturn(false);

        $this->resource_builder->expects($this->exactly(1))
            ->method('has')
            ->with($rid_one)
            ->willReturn(true);

        $revision = $this->createMock(FileRevision::class);

        $revision->expects($this->once())
            ->method('getInformation')
            ->willReturn($this->createMock(FileInformation::class));

        $stream = Streams::ofString('test');

        $resource = $this->createMock(StorableResource::class);

        $this->resource_builder->expects($this->once())
            ->method('get')
            ->with($rid_one)
            ->willReturn($resource);

        $resource->expects($this->any())
            ->method('getCurrentRevision')
            ->willReturn($revision);


        $this->resource_builder->expects($this->exactly(1))
            ->method('extractStream')
            ->with($revision)
            ->willReturn($stream);


        // Assertions
        $flavour = $flavour_builder->get(
            $rid_one,
            $flavour_definition
        );
        $this->assertInstanceOf(Flavour::class, $flavour);
        $tokens = $flavour->getAccessTokens();
        $this->assertCount(1, $tokens);
        $first_token = $tokens[0];
        $this->assertInstanceOf(Token::class, $first_token);
        $this->assertFalse($first_token->hasStreamableStream());
        $resolved_stream = $first_token->resolveStream();
        $this->assertInstanceOf(TokenStream::class, $resolved_stream);
        $this->assertEquals('empty', (string)$resolved_stream);
    }
}
