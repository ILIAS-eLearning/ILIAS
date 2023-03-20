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

use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\Input\FormInputNameSource;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;

/**
 * Implementation of factory for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements M\Factory
{
    protected SignalGeneratorInterface $signal_generator;
    protected FieldFactory $field_factory;

    public function __construct(SignalGeneratorInterface $signal_generator, FieldFactory $field_factory)
    {
        $this->signal_generator = $signal_generator;
        $this->field_factory = $field_factory;
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
    public function interruptiveItem(
        string $id,
        string $title,
        Image $icon = null,
        string $description = ''
    ): M\InterruptiveItem {
        return new InterruptiveItem($id, $title, $icon, $description);
    }

    /**
     * @inheritdoc
     */
    public function roundtrip(string $title, $content, array $inputs = [], string $post_url = null): M\RoundTrip
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
