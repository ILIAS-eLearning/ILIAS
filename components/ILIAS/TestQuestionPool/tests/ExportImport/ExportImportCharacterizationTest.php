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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\UUID\Factory as UUIDFactory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Deserializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Builder;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportContext;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Importing\ImportSessionRepository;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\XmlDeserializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\XmlSerializer;
use ILIAS\TestQuestionPool\ExportImport\Pipes\CollectQuestionImages;
use ILIAS\TestQuestionPool\QuestionPoolDIC;

class ExportImportCharacterizationTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private array $session;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('CLIENT_WEB_DIR')) {
            define('CLIENT_WEB_DIR', '/tmp/ilias-characterization');
        }

        $this->session = $_SESSION ?? [];
        $_SESSION = [];
        $this->setGlobalVariable(
            'refinery',
            new RefineryFactory(new DataFactory(), $GLOBALS['DIC']['lng'])
        );
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->session;

        parent::tearDown();
    }

    public function testSingleChoiceRoundtripPreservesQuestionAndCollectsImages(): void
    {
        global $DIC;

        $question = new assSingleChoice(
            'Characterization question',
            'Question description',
            'Question author',
            17,
            'Which answer is correct?'
        );
        $question->setId(23);
        $question->setObjId(42);
        $question->setAnswers([
            new ASS_AnswerBinaryStateImage('Correct', 2.5, 0, true, 'answer.png', 5),
            new ASS_AnswerBinaryStateImage('Incorrect', 0.0, 1, false, null, 6),
        ]);

        $export_images = new CollectQuestionImages(
            new UUIDFactory(),
            (new DataFactory())->objId(42)
        );
        $export_transformations = (new Builder($DIC, QuestionPoolDIC::dic()))
            ->withAdditionalPipes([$export_images])
            ->create();

        $normalized = $export_transformations->normalize($question);

        $this->assertSame('Characterization question', $normalized['title']);
        $this->assertSame('Which answer is correct?', $normalized['question_text']);
        $this->assertNotSame('', $normalized['answers'][0]['image']['id']);
        $this->assertSame(
            [[
                'from' => CLIENT_WEB_DIR . '/assessment/42/23/images/answer.png',
                'to' => $normalized['answers'][0]['image']['id'] . '.png'
            ]],
            $export_images->getFiles()
        );

        $import_images = new CollectQuestionImages(
            new UUIDFactory(),
            (new DataFactory())->objId(0)
        );
        $import_transformations = (new Builder($DIC, QuestionPoolDIC::dic()))
            ->withAdditionalPipes(append: [$import_images])
            ->create();

        $restored = $import_transformations->denormalize($normalized, new assSingleChoice());

        $this->assertInstanceOf(assSingleChoice::class, $restored);
        $this->assertSame($question->getId(), $restored->getId());
        $this->assertSame($question->getObjId(), $restored->getObjId());
        $this->assertSame($question->getTitle(), $restored->getTitle());
        $this->assertSame($question->getAuthor(), $restored->getAuthor());
        $this->assertSame($question->getQuestion(), $restored->getQuestion());
        $this->assertCount(2, $restored->getAnswers());
        $this->assertSame('Correct', $restored->getAnswers()[0]->getAnswertext());
        $this->assertSame(2.5, $restored->getAnswers()[0]->getPoints());
        $this->assertSame('answer.png', $restored->getAnswers()[0]->getImage());
        $this->assertArrayHasKey(
            $normalized['answers'][0]['image']['id'] . '.png',
            $import_images->getEnvelopes()
        );
    }

    public function testXmlStringAndFileDeserializersProduceTheSameData(): void
    {
        $serializer = XmlSerializer::inMemory();
        $serializer->createDocument('Export/import characterization');
        $serializer->group('sample_group', function () use ($serializer): void {
            $serializer->append('sample', [
                'empty_list' => [],
                'enabled' => true,
                'count' => 3,
                'nothing' => null,
                'label' => 'A < B',
            ]);
        });
        $xml = $serializer->write();

        $path = tempnam(sys_get_temp_dir(), 'ilias-export-import-');
        $this->assertNotFalse($path);
        file_put_contents($path, $xml);

        try {
            $from_string = $this->deserializeGroup(
                XmlDeserializer::fromString($xml),
                'sample_group'
            );
            $from_file = $this->deserializeGroup(
                XmlDeserializer::fromFile($path),
                'sample_group'
            );
        } finally {
            unlink($path);
        }

        $expected = [[
            'empty_list' => [],
            'enabled' => '1',
            'count' => '3',
            'nothing' => null,
            'label' => 'A < B',
        ]];
        $this->assertSame($expected, $from_string);
        $this->assertSame($expected, $from_file);
    }

    public function testXmlMemoryDeserializerProcessesDocumentFragments(): void
    {
        $groups = [];
        $deserializer = XmlDeserializer::fromString(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<sample-group><sample><value>first</value></sample></sample-group>'
            . '<sample-group><sample><value>second</value></sample></sample-group>'
        );
        $deserializer->addHandler(
            'sample_group',
            static function (array $group_data) use (&$groups): void {
                $groups[] = $group_data;
            }
        );

        $deserializer->process();

        $this->assertSame(
            [
                [['value' => 'first']],
                [['value' => 'second']],
            ],
            $groups
        );
    }

    public function testXmlFileDeserializerRejectsMissingFiles(): void
    {
        $this->expectException(InvalidArgumentException::class);

        XmlDeserializer::fromFile(__DIR__ . '/missing.xml');
    }

    public function testXmlSerializerRejectsMismatchedGroups(): void
    {
        $serializer = XmlSerializer::inMemory();
        $serializer->startGroup('expected');

        $this->expectException(LogicException::class);

        $serializer->endGroup('actual');
    }

    public function testImportSessionRepositoryPersistsNamedContextAsJson(): void
    {
        $repository = new ImportSessionRepository('characterization');
        $original = new ImportContext();
        $context = $original
            ->withFileToImport('/tmp/import.zip')
            ->withComponentImportFile('/tmp/import/qpl_data.xml')
            ->withImportBaseDir('/tmp/import')
            ->withInstallId(123)
            ->withLegacyQtiFile('/tmp/import/qti.xml')
            ->withLegacyXmlFile('/tmp/import/qpl.xml')
            ->withSelectableQuestionIds([11, 12])
            ->withSelectedQuestionIds([12])
            ->withPoolObjId(45)
            ->withTestObjId(88)
            ->withTestRefId(99)
            ->withUserMappings(['identifier' => 'login', 'mapping' => ['alice' => 'alice']])
            ->withResourceMappings([['id' => 'rid', 'suffix' => 'png', 'title' => 'img']])
            ->withSkillAssignments([
                'failed' => [],
                'success' => [['skill_id' => 1, 'tref_id' => 0, 'title' => 'Skill', 'path' => '/']],
            ])
            ->withSkillThresholds([
                'failed' => [],
                'success' => [[
                    'skill_base_id' => 1,
                    'skill_tref_id' => 0,
                    'skill_level_id' => 4,
                    'threshold' => 50,
                ]],
            ]);

        $repository->setCurrentStageIndex(3);
        $repository->setContext($context);

        $this->assertFalse($original->hasFileToImport());
        $this->assertSame('/tmp/import.zip', $context->fileToImport());

        $payload = ilSession::get('import_stage_characterization_context');
        $this->assertIsString($payload);
        $decoded = json_decode($payload, true);
        $this->assertIsArray($decoded);
        $this->assertSame('/tmp/import.zip', $decoded['file_to_import']);
        $this->assertSame(123, $decoded['install_id']);

        $restored = $repository->getContext();
        $this->assertSame(3, $repository->getCurrentStageIndex());
        $this->assertSame('/tmp/import.zip', $restored->fileToImport());
        $this->assertSame('/tmp/import/qpl_data.xml', $restored->componentImportFile());
        $this->assertSame('/tmp/import', $restored->importBaseDir());
        $this->assertSame(123, $restored->installId());
        $this->assertSame('/tmp/import/qti.xml', $restored->legacyQtiFile());
        $this->assertSame('/tmp/import/qpl.xml', $restored->legacyXmlFile());
        $this->assertTrue($restored->isLegacyImport());
        $this->assertSame([11, 12], $restored->selectableQuestionIds());
        $this->assertSame([12], $restored->selectedQuestionIds());
        $this->assertSame(45, $restored->poolObjId());
        $this->assertSame(88, $restored->testObjId());
        $this->assertSame(99, $restored->testRefId());
        $this->assertSame(
            ['identifier' => 'login', 'mapping' => ['alice' => 'alice']],
            $restored->userMappings()
        );
        $this->assertSame(
            [['id' => 'rid', 'suffix' => 'png', 'title' => 'img']],
            $restored->resourceMappings()
        );
        $this->assertSame(
            [
                'failed' => [],
                'success' => [['skill_id' => 1, 'tref_id' => 0, 'title' => 'Skill', 'path' => '/']],
            ],
            $restored->skillAssignments()
        );
        $this->assertSame(
            [
                'failed' => [],
                'success' => [[
                    'skill_base_id' => 1,
                    'skill_tref_id' => 0,
                    'skill_level_id' => 4,
                    'threshold' => 50,
                ]],
            ],
            $restored->skillThresholds()
        );

        $repository->clear();

        $this->assertSame(0, $repository->getCurrentStageIndex());
        $empty = $repository->getContext();
        $this->assertFalse($empty->hasFileToImport());
        $this->assertSame([], $empty->selectedQuestionIds());
        $this->assertFalse($empty->isLegacyImport());
    }

    public function testImportSessionRepositoryIgnoresUnknownJsonKeys(): void
    {
        $repository = new ImportSessionRepository('characterization');
        ilSession::set(
            'import_stage_characterization_context',
            json_encode([
                'file_to_import' => '/tmp/import.zip',
                'bridge_tmp' => 'ignored',
            ], JSON_THROW_ON_ERROR)
        );

        $restored = $repository->getContext();
        $decoded = json_decode($restored->toJson(), true);

        $this->assertSame('/tmp/import.zip', $restored->fileToImport());
        $this->assertNull($decoded['component_import_file']);
        $this->assertArrayNotHasKey('bridge_tmp', $decoded);
    }

    public function testImportContextIsLegacyImportOnlyWhenBothLegacyPathsAreSet(): void
    {
        $repository = new ImportSessionRepository('characterization');
        $repository->setContext(
            (new ImportContext())->withLegacyQtiFile('/tmp/qti.xml')
        );

        $this->assertFalse($repository->getContext()->isLegacyImport());

        $repository->setContext(
            (new ImportContext())
                ->withLegacyQtiFile('/tmp/qti.xml')
                ->withLegacyXmlFile('/tmp/qpl.xml')
        );

        $this->assertTrue($repository->getContext()->isLegacyImport());
    }

    private function deserializeGroup(Deserializer $deserializer, string $group): array
    {
        $data = null;
        $deserializer->addHandler($group, static function (array $group_data) use (&$data): void {
            $data = $group_data;
        });
        $deserializer->process();

        $this->assertIsArray($data);
        return $data;
    }
}
