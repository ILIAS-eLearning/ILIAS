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
 */

declare(strict_types=1);

namespace ILIAS\Data;

/**
 * This enum provides options to categorise the purpose of an image, which
 * will be used to determine whether an alternate text is necessary or not,
 * and possibly how it should be structured (in the future). More information
 * about accessible images can be found here:
 *
 * @see https://www.w3.org/WAI/tutorials/images/
 */
enum ImagePurpose
{
    /**
     * The image grahically conveys concepts and/or information to the context it
     * is embedded in. An alternate text MUST be present.
     */
    case INFORMATIVE;

    /**
     * The image visually decorates the current page. It does not convey important
     * information and an alternate text MUST NOT be present.
     */
    case DECORATIVE;

    /**
     * The image purpose is specified by the user and falls into one of the options
     * of this enum. This option exists for cases where ILIAS does not know the
     * image purpose, like e.g. in the page editor. An alternate text MAY be present.
     */
    case USER_DEFINED;
}
