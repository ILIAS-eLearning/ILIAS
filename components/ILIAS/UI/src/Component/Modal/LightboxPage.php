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

namespace ILIAS\UI\Component\Modal;

use ILIAS\UI\Component\Component;

/**
 * Interface LightboxPage
 *
 * A lightbox page represents a page displaying a media element, such as image, video or text.
 */
interface LightboxPage
{
    /**
     * Get the title of this page, displayed as title in the lightbox modal.
     */
    public function getTitle(): string;

    /**
     * Get the component representing the media item to be displayed in the modals
     * content section, e.g. an image.
     */
    public function getComponent(): Component;
}
