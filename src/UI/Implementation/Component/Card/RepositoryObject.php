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
 
namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component\Card as C;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;

class RepositoryObject extends Card implements C\RepositoryObject
{
    protected ?Icon $object_icon = null;
    protected ?ProgressMeter $progress = null;
    protected ?bool $certificate = null;
    protected ?Dropdown $actions = null;

    public function withObjectIcon(Icon $icon) : C\RepositoryObject
    {
        $icon = $icon->withSize("medium");		// ensure same size
        $clone = clone $this;
        $clone->object_icon = $icon;
        return $clone;
    }

    public function getObjectIcon() : ?Icon
    {
        return $this->object_icon;
    }

    public function withProgress(ProgressMeter $progress_meter) : C\RepositoryObject
    {
        $clone = clone $this;
        $clone->progress = $progress_meter;
        return $clone;
    }

    /**
     * Get the ProgressMeter
     */
    public function getProgress() : ?ProgressMeter
    {
        return $this->progress;
    }

    public function withCertificateIcon(bool $certificate_icon) : C\RepositoryObject
    {
        $clone = clone $this;
        $clone->certificate = $certificate_icon;
        return $clone;
    }

    public function getCertificateIcon() : ?bool
    {
        return $this->certificate;
    }

    public function withActions(Dropdown $dropdown) : C\RepositoryObject
    {
        $clone = clone $this;
        $clone->actions = $dropdown;
        return $clone;
    }

    public function getActions() : ?Dropdown
    {
        return $this->actions;
    }
}
