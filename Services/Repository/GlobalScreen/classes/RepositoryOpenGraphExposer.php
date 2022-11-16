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
 */

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
class RepositoryOpenGraphExposer extends AbstractModificationProvider
{
    public function isInterestedInContexts(): ContextCollection
    {
        // the exposer is interested in any context, BUT the repository context
        // will be handled differently.
        return $this->context_collection
            ->internal()
            ->external();
    }

    public function getContentModification(CalledContexts $screen_context_stack): ?ContentModification
    {
        if ($screen_context_stack->current() instanceof ContextRepository &&
            null !== ($object = $this->getObjectOfContext($screen_context_stack->current())) &&
            $this->dic->access()->checkAccess('visible', '', $object->getRefId())
        ) {
            $this->exposeObjectOpenGraphMetaData($object);
        } else {
            $this->exposeDefaultOpenGraphMetaData();
        }

        return null;
    }

    protected function exposeObjectOpenGraphMetaData(\ilObject $object): void
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

        $uri = $this->data->uri(\ilLink::_getLink($object->getRefId(), $object->getType()));

        $this->globalScreen()->layout()->meta()->addOpenGraphMetaDatum(
            $this->data->openGraphMetadata()->website(
                $uri,
                $this->getDefaultImage(),
                $object->getTitle(),
                $uri->getHost(),
                $object->getDescription(),
                $object_translation->getDefaultLanguage(),
                (1 < $additional_locale_count) ? array_slice($additional_locales, 1) : []
            )
        );
    }

    protected function exposeDefaultOpenGraphMetaData(): void
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
                        'HeaderIconResponsive.svg'
                    ),
                    '.'
                )
            ),
            'image/svg+xml'
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
}
