<?php

namespace ILIAS\UI\Implementation\Component\Popover;

use \ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class StandardPopover
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Standard extends Popover implements Component\Popover\Standard
{

    /**
     * @var Component\Component[]
     */
    protected $content;


    /**
     * @param Component\Component|Component\Component[] $content
     * @param SignalGeneratorInterface                  $signal_generator
     */
    public function __construct($content, SignalGeneratorInterface $signal_generator)
    {
        parent::__construct($signal_generator);
        $content = $this->toArray($content);
        $types = array( Component\Component::class );
        $this->checkArgListElements('content', $content, $types);
        $this->content = $content;
    }


    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }
}
