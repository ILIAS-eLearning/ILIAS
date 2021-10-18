<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component\Modal as M;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Implementation of factory for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements M\Factory
{
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }

    /**
     * @inheritdoc
     */
    public function interruptive(string $title, string $message, string $form_action) : M\Interruptive
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
    ) : M\InterruptiveItem {
        return new InterruptiveItem($id, $title, $icon, $description);
    }

    /**
     * @inheritdoc
     */
    public function roundtrip(string $title, $content) : M\RoundTrip
    {
        return new RoundTrip($title, $content, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function lightbox($pages) : M\Lightbox
    {
        return new Lightbox($pages, $this->signal_generator);
    }

    /**
     * @inheritdoc
     */
    public function lightboxImagePage(Image $image, string $title, string $description = '') : M\LightboxImagePage
    {
        return new LightboxImagePage($image, $title, $description);
    }

    /**
     * @inheritdoc
     */
    public function lightboxTextPage(string $text, string $title) : M\LightboxTextPage
    {
        return new LightboxTextPage($text, $title);
    }
}
