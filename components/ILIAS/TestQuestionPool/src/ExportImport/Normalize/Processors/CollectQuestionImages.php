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

namespace TestQuestionPool\ExportImport\Normalize\Processors;

use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\DenormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Processors\NormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Queue\Processor;
use ILIAS\TestQuestionPool\ExportImport\Normalize\Envelopes\QuestionImage;
use ILIAS\TestQuestionPool\Questions\Files\QuestionFiles;

/**
 * Enriches question images with UUIDs and records their source-to-target file mappings.
 */
class CollectQuestionImages implements Processor
{
    private readonly QuestionFiles $question_files;

    /**
     * @var list<array{from: string, to: string}> $files
     */
    private array $files = [];

    /**
     * @var array<string, QuestionImage> $envelopes
     */
    private array $envelopes = [];

    public function __construct(
        private readonly Factory $uuid_factory,
        private readonly ObjectId $pool_id,
    ) {
        $this->question_files = new QuestionFiles();
    }

    /**
     * @inheritDoc
     */
    public function process(object $carry): void
    {
        if ($carry instanceof NormalizeCarry && $carry->value() instanceof QuestionImage) {
            $this->handleNormalization($carry->value());
        }

        if (
            $carry instanceof DenormalizeCarry
            && $carry->expected() === QuestionImage::class
            && $carry->hasResult()
        ) {
            $this->handleDenormalization($carry);
        }
    }

    private function handleNormalization(QuestionImage $envelope): void
    {
        $pool_id = $this->pool_id->toInt();

        $base_dir = $envelope->getType() === QuestionImage::TYPE_ANSWER
            ? $this->question_files->buildImagePath($envelope->getQuestionId(), $pool_id)
            : $this->question_files->buildSolutionPath($envelope->getQuestionId(), $pool_id);

        $source_path = "{$base_dir}{$envelope->getFilename()}";

        // Generate a unique ID for the image and set it on the envelope and the relative target path
        $id = $this->uuid_factory->uuid4();
        $envelope->setId($id->toString());

        $extension = pathinfo($envelope->getFilename(), PATHINFO_EXTENSION);
        $target_path = "{$id->toString()}.{$extension}";

        $this->files[] = ['from' => $source_path, 'to' => $target_path];
    }

    private function handleDenormalization(DenormalizeCarry $passable): void
    {
        $envelope = $passable->result();
        if ($envelope === null) {
            return;
        }
        if (!$envelope instanceof QuestionImage) {
            throw new NormalizingException('Expected question image envelope, got ' . get_debug_type($envelope));
        }

        $path = "{$envelope->getId()}." . pathinfo($envelope->getFilename(), PATHINFO_EXTENSION);

        $this->envelopes[$path] = $envelope;
    }

    /**
     * @return list<array{from: string, to: string}>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array<string, QuestionImage>
     */
    public function getEnvelopes(): array
    {
        return $this->envelopes;
    }
}
