<?php
namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Modal\LightboxPage;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Lightbox extends Modal implements Component\Modal\Lightbox
{

    /**
     * @var LightboxPage[]
     */
    protected $pages;


    /**
     * @param LightboxPage|LightboxPage[] $pages
     * @param SignalGeneratorInterface $signal_generator
     */
    public function __construct($pages, SignalGeneratorInterface $signal_generator)
    {
        parent::__construct($signal_generator);
        $pages = $this->toArray($pages);
        $types = array(LightboxPage::class);
        $this->checkArgListElements('pages', $pages, $types);
        $this->pages = $pages;
    }

    /**
     * @inheritdoc
     */
    public function getPages()
    {
        return $this->pages;
    }
}
