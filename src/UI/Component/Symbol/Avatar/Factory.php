<?php declare(strict_types=1);

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
     *     The Picture Avatar is used to represent a specific user whenever an user-uploaded image is available or a deputy-image is used.
     *   effect: >
     *     The Avatar itself has no own interaction but can be used in a context which triggers further actions (such as a Bulky Button in the Meta Bar).
     *   rivals:
     *     Letter Avatar: if the user has no self uploaded user image, the Letter Avatar MUST be used.
     * context:
     *   1: user slate in the Meta Bar
     *   2: members gallery in a course
     *   3: forum posts
     * rules:
     *   usage:
     *     1: whenever a specific user is represented with a graphical item.
     *   responsiveness:
     *     1: the avatar MUST adjust it's size to the parent container.
     *   accessibility:
     *     1: The aria-label MUST be the username of represented user.
     * ---
     * @param string $path_to_user_image
     * @param string $username
     * @return    \ILIAS\UI\Component\Symbol\Avatar\Picture
     */
    public function image(string $path_to_user_image, string $username) : Picture;


    /**
     * ---
     * description:
     *   purpose: >
     *     The Letter Avatar is used to represent a specific user whenever no image is available.
     *   effect: >
     *     The Avatar itself has no own interaction but can be used in a context which triggers further actions (such as a Bulky Button in the Meta Bar).
     *   rivals:
     *     Picture Avatar: if the user has no self uploaded user image, the Letter Avatar MUST be used.
     *   composition: >
     *     The abbreviation is displayed with two letters in white color.
     *     the background is colored in one of
     * context:
     *   1: user slate in the Meta Bar
     *   2: members gallery in a course
     *   3: forum posts
     * rules:
     *   usage:
     *     1: whenever a specific user is represented with a graphical item and no specific image can be used.
     *   responsiveness:
     *     1: the avatar MUST adjust it's size to the parent container.
     *   accessibility:
     *     1: The aria-label MUST be the username of the represented user.
     * ---
     * @param string $username
     * @return    \ILIAS\UI\Component\Symbol\Avatar\Letter
     */
    public function letter(string $username) : Letter;
}
