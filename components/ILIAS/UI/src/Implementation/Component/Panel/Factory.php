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

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component\Panel as P;

class Factory implements P\Factory
{
    public function __construct(
        protected Listing\Factory $listing_factory,
        protected Secondary\Factory $secondary_factory,
    ) {
    }

    public function standard(string $title, $content): Standard
    {
        return new Standard($title, $content);
    }

    public function sub(string $title, $content): Sub
    {
        return new Sub($title, $content);
    }

    public function report(string $title, $sub_panels): Report
    {
        return new Report($title, $sub_panels);
    }

    public function secondary(): Secondary\Factory
    {
        return $this->secondary_factory;
    }

    public function listing(): Listing\Factory
    {
        return $this->listing_factory;
    }
}
