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
 
namespace ILIAS\UI\Implementation\Component\Breadcrumbs;

use ILIAS\UI\Component\Breadcrumbs as B;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Link\Standard;

class Breadcrumbs implements B\Breadcrumbs
{
    use ComponentHelper;

    /**
     * @var Standard[]     list of links
     */
    protected array $crumbs;

    /**
     * @param \ILIAS\UI\Component\Link\Standard[] $crumbs
     */
    public function __construct(array $crumbs)
    {
        $types = array(Standard::class);
        $this->checkArgListElements("crumbs", $crumbs, $types);
        $this->crumbs = $crumbs;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->crumbs;
    }

    /**
     * @inheritdoc
     */
    public function withAppendedItem(Standard $crumb) : B\Breadcrumbs
    {
        $clone = clone $this;
        $clone->crumbs[] = $crumb;
        return $clone;
    }
}
