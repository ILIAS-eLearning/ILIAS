<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage;

/**
 * Page linker
 *
 * @author killing@leifos.de
 */
interface PageLinker
{
    /**
     * @param bool $offline
     * @return mixed
     */
    public function setOffline($offline = true);

    /**
     * @return array
     */
    public function getLayoutLinkTargets(): array;

    /**
     * @param $int_links
     * @return string
     */
    function getLinkXML($int_links): string;

    /**
     * @return string
     */
    function getFullscreenLink(): string;

}