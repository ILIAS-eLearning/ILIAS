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

namespace ILIAS\Repository\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\ContextRepository;
use ILIAS\GlobalScreen\ScreenContext\ScreenContext;
use ILIAS\Data\Meta\Html\OpenGraph\Image as OGImage;
use ILIAS\MetaData\Services\ServicesInterface as LOMServices;
use ILIAS\MetaData\Services\Reader\ReaderInterface as LOMReader;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class RepositoryOpenGraphExposer extends AbstractModificationProvider
{
    private LOMServices $lom_services;

    private bool $fetch_tile_image = false;


    public function __construct(\ILIAS\DI\Container $dic)
    {
        $this->lom_services = $dic->learningObjectMetadata();
        parent::__construct($dic);
    }

    public function isInterestedInContexts(): ContextCollection
    {
        // the exposer is interested in any context, BUT the repository context
        // will be handled differently.
        return $this->context_collection
            ->internal()
            ->external()
            ->repository();
    }

    public function getContentModification(CalledContexts $screen_context_stack): ?ContentModification
    {
        $current_context = $this->ensureRepoContext($screen_context_stack)->current();
        $ref_id = $current_context->getReferenceId()->toInt();

        if (
            $ref_id > 0
            && $this->dic->access()->checkAccess('visible', '', $ref_id)
            && null !== ($object = $this->getObjectOfContext($current_context))
        ) {
            $this->exposeObjectOpenGraphMetaData($object);
        } else {
            $this->exposeDefaultOpenGraphMetaData();
        }

        return null;
    }

    private function exposeObjectOpenGraphMetaData(\ilObject $object): void
    {
        $object_translation = \ilObjectTranslation::getInstance($object->getId());

        $additional_locale_count = 0;
        $additional_locales = [];

        foreach ($this->getLanguageCodesFromLOM($object->getId(), $object->getType()) as $language_code) {
            if ($language_code !== $object_translation->getDefaultLanguage()) {
                $additional_locales[] = $language_code;
                $additional_locale_count++;
            }
        }

        $uri = $this->data->uri(\ilLink::_getStaticLink($object->getRefId(), $object->getType()));

        $image = $this->getPresentationImage($object);

        $this->globalScreen()->layout()->meta()->addOpenGraphMetaDatum(
            $this->data->openGraphMetadata()->website(
                $uri,
                $image,
                $object->getPresentationTitle(),
                $uri->getHost(),
                $object->getLongDescription() . ' ', // we add a space to ensure the description is not cut off
                $object_translation->getDefaultLanguage(),
                (1 < $additional_locale_count) ? array_slice($additional_locales, 1) : []
            )
        );
    }

    private function exposeDefaultOpenGraphMetaData(): void
    {
        $uri = $this->data->uri(ILIAS_HTTP_PATH);

        $this->globalScreen()->layout()->meta()->addOpenGraphMetaDatum(
            $this->data->openGraphMetadata()->website(
                $uri,
                $this->getDefaultImage(),
                $this->dic->language()->txt('permission_denied'),
                $uri->getHost(),
            )
        );
    }

    protected function getObjectOfContext(ScreenContext $context): ?\ilObject
    {
        if (!$context->hasReferenceId()) {
            return null;
        }

        try {
            $current_object = \ilObjectFactory::getInstanceByRefId($context->getReferenceId()->toInt());
        } catch (\ilDatabaseException|\ilObjectNotFoundException) {
            $current_object = null;
        } finally {
            return $current_object;
        }
    }

    protected function getDefaultImage(): OGImage
    {
        $image_path_resolver = new \ilImagePathResolver();

        return $this->data->openGraphMetadata()->image(
            $this->data->uri(
                ILIAS_HTTP_PATH . ltrim(
                    $image_path_resolver->resolveImagePath(
                        'logo/Sharing.jpg'
                    ),
                    '.'
                )
            ),
            'image/jpg'
        );
    }

    /**
     * @return string[]
     */
    protected function getLanguageCodesFromLOM(int $object_id, string $object_type): \Generator
    {
        $languages_path = $this->lom_services->paths()->languages();
        $reader = $this->lom_services->read($object_id, 0, $object_type, $languages_path);
        foreach ($reader->allData($languages_path) as $lang_data) {
            yield $lang_data->value();
        }
    }

    protected function ensureRepoContext(CalledContexts $screen_context_stack): CalledContexts
    {
        $collection = new ContextCollection(
            $this->dic->globalScreen()->tool()->context()->availableContexts()
        );
        $collection = $collection->repository();

        if (!$screen_context_stack->hasMatch($collection)) {
            $screen_context_stack = $screen_context_stack->repository();
        }
        return $screen_context_stack;
    }

    protected function getPresentationImage(\ilObject $object): OGImage
    {
        $image_factory = $this->dic->ui()->factory()->image();
        $image = $this->getDefaultImage();
        if (!$this->fetch_tile_image) {
            return $image;
        }
        try {
            // Use the tile image if available
            $tile_image = $object->getObjectProperties()->getPropertyTileImage()->getTileImage();
            if ($tile_image !== null && $tile_image->getRid() !== null) {
                $uri_string = $tile_image->getImage($image_factory)->getAdditionalHighResSources()['960']
                    ?? $tile_image->getImage($image_factory)->getSource();

                $image = $this->data->openGraphMetadata()->image(
                    $this->data->uri($uri_string),
                    'image/jpg'
                );
            }
        } catch (\Throwable $e) {
        }
        return $image;
    }
}
