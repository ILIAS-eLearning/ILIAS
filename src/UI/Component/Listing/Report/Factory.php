<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\Report;

/**
 * This is the interface for a report factory.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     A Standard Report Listing is the usual choice when label-value doubles
     *     of textual-information are to be presented as a major part of the ui,
     *     representing e.g. the main content for any screen.
     *   composition: >
     *     Report Listings are composed of items containing a key labeling the value
     *     being displayed side by side.
     *   effect: >
     *     The items will be presented underneath, whereby each items' label and value
     *     will be presented side by side both left aligned an in columns using the half
     *     of the available space.
     *     A Standard Report Listing SHOULD be used with a divider,
     *     that will be shown between the items.
     * rules:
     *   usage:
     *       1: >
     *         The Standard Report Listing MUST NOT be used standalone. It MUST be used as content
     *         for a parent component such as a Panel or similar.
     *       2: >
     *         The Standard Report Listing SHOULD be used as a major ui part such as the main content.
     * ----
     * @param array $items string => Component | string
     *
     * @return \ILIAS\UI\Component\Listing\Report\Standard
     */
    public function standard(array $items);

    /**
     * ---
     * description:
     *   purpose: >
     *     A Mini Report Listing is the choice when label-value doubles of textual-information
     *     are to be presented as a marginal part in the ui, e.g. when used as a section
     *     for a card within a reporting panel.
     *   composition: >
     *     Report Listings are composed of items containing a key labeling the value
     *     being displayed side by side.
     *   effect: >
     *     The items will be presented underneath, whereby each items' label and value will be presented side by side.
     *     The width of columns for labels and values are not the same, the labels get more space.
     *     For a better optic the values are righ aligned.
     * rules:
     *   usage:
     *       1: >
     *         The Mini Report Listing MUST NOT be used standalone. It MUST be used as content
     *         for a parent component such as the Reporting Panel Card or similar.
     *       2: >
     *         The Mini Report Listing SHOULD NOT be used as a major ui part such as the main content.
     * ----
     *
     * @param array $items string => Component | string
     *
     * @return \ILIAS\UI\Component\Listing\Report\Mini
     */
    public function mini(array $items);
}
