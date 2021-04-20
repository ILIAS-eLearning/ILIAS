<?php

declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MainControls\Slate;

use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\MainControls\Slate as ISlate;
use ILIAS\UI\Component\Button\Bulky as IBulkyButton;
use ILIAS\UI\Component\Link\Bulky as IBulkyLink;
use ILIAS\UI\Component\Signal;
use InvalidArgumentException;

/**
 * Combined Slate
 */
class Combined extends Slate implements ISlate\Combined
{
    const ENTRY_ACTION_TRIGGER = 'trigger';

    /**
     * @var array<Slate|IBulkyButton|IBulkyLink>
     */
    protected $contents = [];

    /**
     * @inheritdoc
     */
    public function withAdditionalEntry($entry, ?string $id = null) : ISlate\Combined
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
        if ($id) {
            if (key_exists($id, $clone->contents)) {
                throw new InvalidArgumentException(
                    "Identifier $id already exists in component " . $clone->getName())
                ;
            }
            $clone->contents[$id] = $entry;
        } else {
            $clone->contents[] = $entry;
        }
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
        $this->trigger_signals[] = $signal;
        return $signal;
    }

    public function withMappedSubNodes(callable $f)
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
