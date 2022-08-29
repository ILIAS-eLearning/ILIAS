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

namespace ILIAS\MetaData\Editor\Presenter;

class Presenter implements PresenterInterface
{
    protected UtilitiesInterface $utilities;
    protected DataInterface $data;
    protected ElementsInterface $elements;

    public function __construct(
        UtilitiesInterface $utilities,
        DataInterface $data,
        ElementsInterface $elements
    ) {
        $this->utilities = $utilities;
        $this->data = $data;
        $this->elements = $elements;
    }

    public function utilities(): UtilitiesInterface
    {
        return $this->utilities;
    }

    public function data(): DataInterface
    {
        return $this->data;
    }

    public function elements(): ElementsInterface
    {
        return $this->elements;
    }
}
