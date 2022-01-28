<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Popover;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class StandardPopover
 *
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component\Popover
 */
class Standard extends Popover implements C\Popover\Standard
{
    /**
     * @var C\Component[]
     */
    protected array $content;

    /**
     * @param C\Component|C\Component[] $content
     */
    public function __construct($content, SignalGeneratorInterface $signal_generator)
    {
        parent::__construct($signal_generator);
        $content = $this->toArray($content);
        $types = array(C\Component::class );
        $this->checkArgListElements('content', $content, $types);
        $this->content = $content;
    }

    /**
     * @inheritdoc
     */
    public function getContent() : array
    {
        return $this->content;
    }
}
