<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Modal\InterruptiveItem\Factory as ItemFactory;

/**
 * Implementation of factory for modals
 */
class Factory implements M\Factory
{
    public function __construct(
        protected SignalGeneratorInterface $signal_generator,
        protected ItemFactory $item_factory,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function interruptive(string $title, string $message, string $form_action): M\Interruptive
    {
        return new Interruptive($title, $message, $form_action, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function interruptiveItem(): M\InterruptiveItem\Factory
    {
        return $this->item_factory;
    }

    /**
     * @inheritdoc
     */
    public function roundtrip(string $title, $content): M\RoundTrip
    {
        return new RoundTrip($title, $content, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function lightbox($pages): M\Lightbox
    {
        return new Lightbox($pages, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function lightboxImagePage(Image $image, string $title, string $description = ''): M\LightboxImagePage
    {
        return new LightboxImagePage($image, $title, $description);
    }

    /**
     * @inheritdoc
     */
    public function lightboxTextPage(string $text, string $title): M\LightboxTextPage
    {
        return new LightboxTextPage($text, $title);
    }
}
