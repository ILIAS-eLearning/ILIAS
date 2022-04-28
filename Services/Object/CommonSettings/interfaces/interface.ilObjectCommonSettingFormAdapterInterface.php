<?php declare(strict_types=1);

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
 
/**
 * Common settings form adapter. Helps to add and save common object settings for repository objects.
 *
 * @author killing@leifos.de
 * @ingroup ServicesObject
 */
interface ilObjectCommonSettingFormAdapterInterface
{
    /**
     * Add icon setting to form
     */
    public function addIcon() : ?ilPropertyFormGUI;

    /**
     * Save icon setting from form
     */
    public function saveIcon() : void;

    /**
     * Add tile image setting to form
     */
    public function addTileImage() : ?ilPropertyFormGUI;

    /**
     * Save tile image setting from form
     */
    public function saveTileImage() : void;

    /**
     * Add title icon visibility setting to form
     */
    public function addTitleIconVisibility() : ilPropertyFormGUI;

    /**
     * Save title icon visibility setting from form
     */
    public function saveTitleIconVisibility() : void;

    /**
     * Add top actions visibility setting to form
     */
    public function addTopActionsVisibility() : ilPropertyFormGUI;

    /**
     * Save top actions visibility setting from form
     */
    public function saveTopActionsVisibility() : void;
}
