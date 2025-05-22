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

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Modal\InterruptiveItem\Factory as ItemFactory;
use ILIAS\UI\Implementation\Component\Input\FormInputNameSource;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Card\Card;

class Factory implements M\Factory
{
    public function __construct(
        protected SignalGeneratorInterface $signal_generator,
        protected InterruptiveItem\Factory $item_factory,
        protected FieldFactory $field_factory,
    ) {
    }

    public function interruptive(string $title, string $message, string $form_action): Interruptive
    {
        return new Interruptive($title, $message, $form_action, $this->signal_generator);
    }

    public function interruptiveItem(): InterruptiveItem\Factory
    {
        return $this->item_factory;
    }

    public function roundtrip(string $title, Component\Component|array|null $content, array $inputs = [], ?string $post_url = null): RoundTrip
    {
        return new RoundTrip(
            $this->signal_generator,
            $this->field_factory,
            new FormInputNameSource(),
            $title,
            $content,
            $inputs,
            $post_url
        );
    }

    public function lightbox($pages): Lightbox
    {
        return new Lightbox($pages, $this->signal_generator);
    }

    public function lightboxImagePage(Image $image, string $title, string $description = ''): LightboxImagePage
    {
        return new LightboxImagePage($image, $title, $description);
    }

    public function lightboxTextPage(string $text, string $title): LightboxTextPage
    {
        return new LightboxTextPage($text, $title);
    }

    public function lightboxCardPage(Card $card): LightboxCardPage
    {
        return new LightboxCardPage($card);
    }
}
