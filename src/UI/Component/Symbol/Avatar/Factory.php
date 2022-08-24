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

namespace ILIAS\UI\Component\Symbol\Avatar;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Symbol\Avatar
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Picture Avatar is used to represent a specific user whenever an
     *     user-uploaded picture is available or a deputy-picture is used.
     *   rivals:
     *      Letter Avatar: The Letter Avatar represents the user with two letters.
     * rules:
     *   usage:
     *     1: >
     *        whenever a specific user is represented with a graphical item, a.
     *        Picture Avatar MUST be used.
     * ---
     * @param string $path_to_user_picture
     * @param string $username
     * @return    \ILIAS\UI\Component\Symbol\Avatar\Picture
     */
    public function picture(string $path_to_user_picture, string $username): Picture;


    /**
     * ---
     * description:
     *   purpose: >
     *     The Letter Avatar is used to represent a specific user whenever no
     *     picture is available.
     *   rivals:
     *      Picture Avatar: The Picture Avatar represents the user with a picture.
     *   composition: >
     *     The abbreviation is displayed with two letters in white color.
     *     the background is colored in one of
     * rules:
     *   usage:
     *     1: >
     *         whenever a specific user is represented with a graphical item and
     *         no specific picture can be used, a Letter Avatar MUST be used.
     *   wording:
     *     1: The abbreviation MUST consist of two letters.
     * ---
     * @param string $username
     * @return    \ILIAS\UI\Component\Symbol\Avatar\Letter
     */
    public function letter(string $username): Letter;
}
