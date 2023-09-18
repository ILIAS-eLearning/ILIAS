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
    public function getContent(): array
    {
        return $this->content;
    }
}
