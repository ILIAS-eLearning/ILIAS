<?php declare(strict_types=1);

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Card;

use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Symbol\Icon\Icon;

/**
 * Interface Custom
 * @package ILIAS\UI\Component\Card
 */
interface RepositoryObject extends Card
{
    /**
     * Get a RepositoryObject card like this, but with an additional UI Icon representing the repository object type.
     */
    public function withObjectIcon(Icon $icon) : RepositoryObject;

    /**
     * Returns an UI Icon which represents the repository object type
     */
    public function getObjectIcon() : ?Icon;

    /**
     * Get a RepositoryObject card like this, but with an additional UI ProgressMeter object
     */
    public function withProgress(ProgressMeter $progress_meter) : RepositoryObject;

    /**
     * Get the ProgressMeter of the card
     */
    public function getProgress() : ?ProgressMeter;

    /**
     * Get a RepositoryObject card like this, but with an additional certificate outlined icon
     */
    public function withCertificateIcon(bool $certificate_icon) : RepositoryObject;

    /**
     * Get the certificate icon
     */
    public function getCertificateIcon() : ?bool;

    /**
     * Get a RepositoryObject card like this, but with an additional UI Dropdown object
     */
    public function withActions(Dropdown $dropdown) : RepositoryObject;

    /**
     * get the dropdown actions
     */
    public function getActions() : ?Dropdown;
}
