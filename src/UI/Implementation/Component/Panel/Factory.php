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

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component\Panel as P;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements P\Factory
{
    protected P\Listing\Factory $listing_factory;

    public function __construct(P\Listing\Factory $listing_factory)
    {
        $this->listing_factory = $listing_factory;
    }

    /**
     * @inheritdoc
     */
    public function standard(string $title, $content): P\Standard
    {
        return new Standard($title, $content);
    }

    /**
     * @inheritdoc
     */
    public function sub(string $title, $content): P\Sub
    {
        return new Sub($title, $content);
    }

    /**
     * @inheritdoc
     */
    public function report(string $title, $sub_panels): P\Report
    {
        return new Report($title, $sub_panels);
    }

    /**
     * @inheritdoc
     */
    public function secondary(): P\Secondary\Factory
    {
        return new Secondary\Factory();
    }

    /**
     * @inheritdoc
     */
    public function listing(): P\Listing\Factory
    {
        return $this->listing_factory;
    }
}
