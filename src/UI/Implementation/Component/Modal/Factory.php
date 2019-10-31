<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Implementation of factory for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements Component\Modal\Factory
{

    /**
     * @var SignalGeneratorInterface
     */
    protected $signal_generator;

    /**
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct(SignalGeneratorInterface $signal_generator)
    {
        $this->signal_generator = $signal_generator;
    }


    /**
     * @inheritdoc
     */
    public function interruptive($title, $message, $form_action)
    {
        return new Interruptive($title, $message, $form_action, $this->signal_generator);
    }


    /**
     * @inheritdoc
     */
    public function interruptiveItem($id, $title, Image $icon = null, $description = '')
    {
        return new InterruptiveItem($id, $title, $icon, $description);
    }


    /**
     * @inheritdoc
     */
    public function roundtrip($title, $content)
    {
        return new RoundTrip($title, $content, $this->signal_generator);
    }


    /**
     * @inheritdoc
     */
    public function lightbox($pages)
    {
        return new Lightbox($pages, $this->signal_generator);
    }


    /**
     * @inheritdoc
     */
    public function lightboxImagePage(Image $image, $title, $description = '')
    {
        return new LightboxImagePage($image, $title, $description);
    }

    /**
     * @inheritdoc
     */
    public function lightboxTextPage(string $text, string $title)
    {
        return new LightboxTextPage($text, $title);
    }
}
