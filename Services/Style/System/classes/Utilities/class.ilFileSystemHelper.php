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
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * File System Helper, to reduce deps. to ilUtil or wrap them properly. Should be replaced by src/Filesystem as soon
 * as templates/default is accessible by src/Filesystem
 */
class ilFileSystemHelper
{
    /**
     * Used to stack messages to be displayed to the user (mostly reports for failed actions)
     */
    protected ilSystemStyleMessageStack $message_stack;
    protected ilLanguage $lng;

    public function __construct(ilLanguage $lng, ilSystemStyleMessageStack $message_stack)
    {
        $this->setMessageStack($message_stack);
        $this->lng = $lng;
    }

    /**
     * Used to move a complete directory of a skin
     */
    public function move(string $from, string $to) : void
    {
        rename($from, $to);
    }

    public function delete(string $file_path) : void
    {
        unlink($file_path);
    }

    /**
     * Deletes a given file in the container
     */
    public function saveDeleteFile(string $file_path) : void
    {
        if (file_exists($file_path)) {
            unlink($file_path);
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('file_deleted') . ' ' . $file_path,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
    }

    /**
     * Recursive delete of a folder
     */
    public function recursiveRemoveDir(string $dir) : void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir . '/' . $object)) {
                        $this->recursiveRemoveDir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Deletes a resource directory
     */
    public function removeResourceDirectory(string $skin_dir, string $dir, bool $is_linked)
    {
        $absolut_dir = $skin_dir . $dir;

        if (file_exists($absolut_dir)) {
            if (!$is_linked) {
                self::recursiveRemoveDir($skin_dir . $dir);
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt('dir_deleted') . ' ' . $dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            } else {
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt('dir_preserved_linked') . ' ' . $dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            }
        }
    }

    /**
     * Creates a resource directory (sound, images or fonts) by copying from the source (mostly delos)
     * @throws ilSystemStyleException
     */
    public function createResourceDirectory(string $source, string $target) : void
    {
        mkdir($target, 0775, true);

        if ($source != '') {
            $this->recursiveCopy($source, $target);
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('dir_created') . $target,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
    }

    /**
     * Alters the name/path of a resource directory
     * @throws ilSystemStyleException
     */
    public function changeResourceDirectory(string $skin_dir, string $new_dir, string $old_dir, bool $has_references) : void
    {
        $absolut_new_dir = $skin_dir . $new_dir;
        $absolut_old_dir = $skin_dir . $old_dir;

        if (file_exists($absolut_new_dir)) {
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('dir_changed_to') . ' ' . $absolut_new_dir,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('dir_preserved_backup') . ' ' . $absolut_old_dir,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        } else {
            mkdir($absolut_new_dir, 0775, true);
            $this->recursiveCopy($absolut_old_dir, $absolut_new_dir);
            $this->getMessageStack()->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt('dir_copied_from') . ' ' . $absolut_old_dir . ' ' . $this->lng->txt('sty_copy_to') . ' ' . $absolut_new_dir,
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
            if (!$has_references) {
                $this->recursiveRemoveDir($skin_dir . $old_dir);
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt('dir_deleted') . ' ' . $absolut_old_dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            } else {
                $this->getMessageStack()->addMessage(
                    new ilSystemStyleMessage(
                        $this->lng->txt('dir_preserved_linked') . ' ' . $absolut_old_dir,
                        ilSystemStyleMessage::TYPE_SUCCESS
                    )
                );
            }
        }
    }

    /**
     * Recursive copy of a folder
     * @throws ilSystemStyleException
     */
    public function recursiveCopy(string $src, string $dest) : void
    {
        foreach (scandir($src) as $file) {
            $src_file = rtrim($src, '/') . '/' . $file;
            $dest_file = rtrim($dest, '/') . '/' . $file;
            if (!is_readable($src_file)) {
                throw new ilSystemStyleException(ilSystemStyleException::FILE_OPENING_FAILED, $src_file);
            }
            if (substr($file, 0, 1) != '.') {
                if (is_dir($src_file)) {
                    if (!file_exists($dest_file)) {
                        try {
                            mkdir($dest_file);
                        } catch (Exception $e) {
                            throw new ilSystemStyleException(
                                ilSystemStyleException::FOLDER_CREATION_FAILED,
                                'Copy ' . $src_file . ' to ' . $dest_file . ' Error: ' . $e
                            );
                        }
                    }
                    $this->recursiveCopy($src_file, $dest_file);
                } else {
                    try {
                        copy($src_file, $dest_file);
                    } catch (Exception $e) {
                        throw new ilSystemStyleException(
                            ilSystemStyleException::FILE_CREATION_FAILED,
                            'Copy ' . $src_file . ' to ' . $dest_file . ' Error: ' . $e
                        );
                    }
                }
            }
        }
    }



    public function getMessageStack() : ilSystemStyleMessageStack
    {
        return $this->message_stack;
    }

    public function setMessageStack(ilSystemStyleMessageStack $message_stack) : void
    {
        $this->message_stack = $message_stack;
    }
}
