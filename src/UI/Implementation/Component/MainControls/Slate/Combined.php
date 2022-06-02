<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Button\Bulky as IBulkyButton;
use ILIAS\UI\Component\Link\Bulky as IBulkyLink;
use ILIAS\UI\Component\Signal;

/**
 * Combined Slate
 */
class Combined extends Slate implements ISlate\Combined
{
    public const ENTRY_ACTION_TRIGGER = 'trigger';

    /**
     * @var array<Slate|IBulkyButton|IBulkyLink>
     */
    protected array $contents = [];

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry($entry) : ISlate\Combined
    {
        $classes = [
            IBulkyButton::class,
            IBulkyLink::class,
            ISlate\Slate::class,
            Horizontal::class
        ];
        $check = [$entry];
        $this->checkArgListElements("Slate, Bulky -Button or -Link", $check, $classes);

        $clone = clone $this;
        $clone->contents[] = $entry;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getContents() : array
    {
        return $this->contents;
    }

    public function getTriggerSignal(string $entry_id) : Signal
    {
        $signal = $this->signal_generator->create();
        $signal->addOption('entry_id', $entry_id);
        $signal->addOption('action', self::ENTRY_ACTION_TRIGGER);
        return $signal;
    }

    public function withMappedSubNodes(callable $f) : ISlate\Combined
    {
        $clone = clone $this;
        $new_contents = [];
        foreach ($clone->getContents() as $k => $v) {
            $new_contents[$k] = $f($k, $v);
        }
        $clone->contents = $new_contents;
        return $clone;
    }
}
