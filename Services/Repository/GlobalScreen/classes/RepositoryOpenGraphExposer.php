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

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
final class RepositoryOpenGraphExposer extends AbstractModificationProvider
{
    private bool $fetch_tile_image = false;


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
        $general_meta_data = $this->getGeneralObjectMeta($object->getId());

        $additional_locale_count = 0;
        $additional_locales = [];

        if (null !== $general_meta_data) {
            foreach ($general_meta_data->getLanguageIds() as $language_id) {
                $language = $general_meta_data->getLanguage($language_id);
                if (null !== $language && $language->getLanguageCode() !== $object_translation->getDefaultLanguage()) {
                    $additional_locales[] = $language->getLanguageCode();
                    $additional_locale_count++;
                }
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

    protected function getGeneralObjectMeta(int $object_id): ?\ilMDGeneral
    {
        if (0 < ($meta_id = \ilMDGeneral::_getId($object_id, $object_id))) {
            $general = new \ilMDGeneral();
            $general->setMetaId($meta_id);

            return $general;
        }

        return null;
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
