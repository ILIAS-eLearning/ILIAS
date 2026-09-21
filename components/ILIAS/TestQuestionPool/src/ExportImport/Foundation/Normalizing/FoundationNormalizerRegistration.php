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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing;

use ILIAS\Data\UUID\Uuid;
use ILIAS\DI\Container;
use ILIAS\Refinery\Transformation;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\DateTimeNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\EnvelopeNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\IlObjectNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\Legacy11UUIDNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\Registry;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\ResourceNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\TransformationNormalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer\UUIDNormalizer;
use InitResourceStorage;

final class FoundationNormalizerRegistration
{
    public function __construct(private readonly Container $dic) {
    }

    public function register(Registry $registry, Transformations $transformations): void
    {
        $date_time = new DateTimeNormalizer();
        $registry->register(\DateTime::class, $date_time);
        $registry->register(\DateTimeImmutable::class, $date_time);

        $uuid = new UUIDNormalizer();
        $registry->register(Uuid::class, $uuid);
        $registry->register(Uuid::class, new Legacy11UUIDNormalizer(), '11');

        $registry->register(Envelope::class, new EnvelopeNormalizer($transformations));
        $registry->register(Transformation::class, new TransformationNormalizer($this->dic->refinery()));

        if (isset($this->dic[InitResourceStorage::D_REPOSITORIES])) {
            $resource = new ResourceNormalizer(
                $transformations,
                $this->dic[InitResourceStorage::D_REPOSITORIES]->getResourceRepository()
            );
            $registry->register(ResourceIdentification::class, $resource);
            $registry->register(StorableResource::class, $resource);
        }

        $registry->register(\ilObject::class, new IlObjectNormalizer($transformations));
    }
}
