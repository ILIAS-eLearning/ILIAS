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
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\SimpleXMLSerializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\XMLFileDeserializer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Serializing\XMLMemoryDeserializer;
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
        $serializer = (new SimpleXMLSerializer())->open('');
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
                (new XMLMemoryDeserializer())->open($xml),
                'sample_group'
            );
            $from_file = $this->deserializeGroup(
                (new XMLFileDeserializer())->open($path),
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
        $deserializer = (new XMLMemoryDeserializer())->open(
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

        (new XMLFileDeserializer())->open(__DIR__ . '/missing.xml');
    }

    public function testXmlSerializerRejectsMismatchedGroups(): void
    {
        $serializer = (new SimpleXMLSerializer())->open('');
        $serializer->startGroup('expected');

        $this->expectException(LogicException::class);

        $serializer->endGroup('actual');
    }

    public function testImportSessionRepositoryPreservesCurrentContextBehavior(): void
    {
        $repository = new ImportSessionRepository('characterization');
        $context = (new ImportContext())
            ->with('file_to_import', '/tmp/import.zip')
            ->with('selected_question_ids', ['12', '27'])
            ->with('unknown_field', 'preserved');

        $repository->setCurrentStageIndex(3);
        $repository->setContext($context);

        $restored = $repository->getContext();
        $this->assertSame(3, $repository->getCurrentStageIndex());
        $this->assertSame('/tmp/import.zip', $restored->get('file_to_import'));
        $this->assertSame(['12', '27'], $restored->get('selected_question_ids'));
        $this->assertSame('preserved', $restored->get('unknown_field'));
        $this->assertNull($restored->get('missing'));
        $this->assertSame('fallback', $restored->get('missing', 'fallback'));

        $repository->clear();

        $this->assertSame(0, $repository->getCurrentStageIndex());
        $this->assertFalse($repository->getContext()->has('file_to_import'));
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
