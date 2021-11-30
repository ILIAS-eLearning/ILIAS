<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Contribution;

use DateTimeImmutable;

/**
 * This is the factory for Contributions.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     Quotes are contributions with textual content.
     *   composition: >
     *     Quotes specify their content to a textual representation in addition to the general contribution composition.
     *     Further the Quote can have a close button and a lead icon.
     *   effect: >
     *     An interaction with the close button may remove the Quote permanently.
     * rules:
     *   interaction:
     *     1: >
     *        Clicking on the Close Button MUST remove the Contribution Item permanently.
     *   accessibility:
     *     1: >
     *       All interactions offered by a Contribution Item MUST be accessible by only using the keyboard.
     *     2: >
     *       The main quote of the contribution MUST NOT be part of any interaction.
     * ---
     *
     * @param string      $quote
     * @param ?string      $contributor
     * @param ?\DateTimeImmutable $createDatetime
     * @return \ILIAS\UI\Component\Contribution\Quote
     */
    public function quote(
        string $quote,
        ?string $contributor = null,
        ?DateTimeImmutable $createDatetime = null
    ) : Quote;
}
