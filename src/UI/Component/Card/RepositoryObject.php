<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Card;

use ILIAS\UI\Component\Chart\ProgressMeter\ProgressMeter;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Icon\Icon;

/**
 * Interface Custom
 * @package ILIAS\UI\Component\Card
 */
interface RepositoryObject extends Card
{

    /**
     * Get a RepositoryObject card like this, but with an additional UI Icon representing the repository object type.
     * @param Icon $icon
     * @return RepositoryObject
     */
    public function withObjectIcon(Icon $icon);

    /**
     * Returns an UI Icon which represents the repository object type
     * @return string|null
     */
    public function getObjectIcon();

    /**
     * Get a RepositoryObject card like this, but with an additional UI Progressmeter object
     * @param ProgressMeter $progressmeter
     * @return RepositoryObject
     */
    public function withProgress(ProgressMeter $progressmeter);

    /**
     * Get the progressmeter of the card
     * @return ProgressMeter
     */
    public function getProgress();

    /**
     * Get a RepositoryObject card like this, but with an additional certificate outlined icon
     * @param bool $certificate_icon
     * @return RepositoryObject
     */
    public function withCertificateIcon($certificate_icon);

    /**
     * Get the certificate icon
     * @return bool
     */
    public function getCertificateIcon();

    /**
     * Get a RepositoryObject card like this, but with an additional UI Dropdown object
     * @param $dropdown Dropdown
     * @return RepositoryObject
     */
    public function withActions($dropdown);

    /**
     * get the dropdown actions
     * @return Dropdown
     */
    public function getActions();
}
