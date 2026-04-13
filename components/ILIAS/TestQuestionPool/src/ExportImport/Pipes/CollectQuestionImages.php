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

namespace ILIAS\TestQuestionPool\ExportImport\Pipes;

use ILIAS\Data\ObjectId;
use ILIAS\Data\UUID\Factory;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\DenormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizeCarry;
use ILIAS\TestQuestionPool\ExportImport\Envelopes\QuestionImage;
use ILIAS\TestQuestionPool\Questions\Files\QuestionFiles;

/**
 * Pipe that enriches QuestionImage envelopes with UUID-based IDs and records source-to-target file mappings during
 * normalization.
 */
class CollectQuestionImages implements Pipe
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
    public function handle(mixed $passable, \Closure $next): mixed
    {
        if ($passable instanceof NormalizeCarry && $passable->value instanceof QuestionImage) {
            $this->handleNormalization($passable->value);
        }

        if ($passable instanceof DenormalizeCarry && $passable->expected === QuestionImage::class) {
            $this->handleDenormalization($passable);
        }

        return $next($passable);
    }

    private function handleNormalization(QuestionImage $envelope): void
    {
        $pool_id = $this->pool_id->toInt();

        $base_dir = $envelope->getType() === QuestionImage::TYPE_ANSWER
            ? $this->question_files->buildImagePath($envelope->getQuestionId(), $pool_id)
            : $this->question_files->buildSolutionPath($envelope->getQuestionId(), $pool_id);

        $source_path = $base_dir . $envelope->getFilename();

        // Generate a unique ID for the image and set it on the envelope and the relative target path
        $id = $this->uuid_factory->uuid4();
        $envelope->setId($id->toString());

        $extension = pathinfo($envelope->getFilename(), PATHINFO_EXTENSION);
        $target_path = $id->toString() . '.' . $extension;

        $this->files[] = ['from' => $source_path, 'to' => $target_path];
    }

    private function handleDenormalization(DenormalizeCarry $passable): void
    {
        $envelope = $passable->result();
        if (!$envelope instanceof QuestionImage) {
            throw new NormalizingException('Expected question image envelope, got ' . get_debug_type($envelope));
        }

        $extension = pathinfo($envelope->getFilename(), PATHINFO_EXTENSION);
        $path = $envelope->getId() . '.' . $extension;

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
