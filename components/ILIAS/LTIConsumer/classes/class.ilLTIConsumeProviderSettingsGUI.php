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
 * Class ilLTIConsumeProviderSettingsGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package components\ILIAS/LTIConsumer
 */

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;

class ilLTIConsumeProviderSettingsGUI
{
    public const CMD_SHOW_SETTINGS = 'showSettings';
    public const CMD_SAVE_SETTINGS = 'saveSettings';

    /**
     * @var ilObjLTIConsumer
     */
    protected ilObjLTIConsumer $object;

    /**
     * @var ilLTIConsumerAccess
     */
    protected ilLTIConsumerAccess $access;

    protected LOMServices $lom_services;

    /**
     * ilLTIConsumerAccess constructor.
     */
    public function __construct(ilObjLTIConsumer $object, ilLTIConsumerAccess $access)
    {
        global $DIC;
        $this->object = $object;
        $this->access = $access;
        $this->lom_services = $DIC->learningObjectMetadata();
    }

    /**
     * Execute Command
     */
    public function executeCommand(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        switch ($DIC->ctrl()->getNextClass()) {
            default:

                $command = $DIC->ctrl()->getCmd(self::CMD_SHOW_SETTINGS) . 'Cmd';
                $this->{$command}();
        }
    }

    protected function showSettingsCmd(ilLTIConsumeProviderFormGUI $form = null): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        if ($form === null) {
            $form = $this->buildForm($this->object->getProvider());
        }

        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }

    protected function saveSettingsCmd(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $provider = $this->object->getProvider();
        $form = $this->buildForm($provider);
        if ($form->checkInput()) {
            $form->initProvider($provider);
            $this->object->getProvider()->save();
            $this->initMetadata($this->object);
            $DIC->ctrl()->redirect($this, self::CMD_SHOW_SETTINGS);
        }

        $this->showSettingsCmd($form);
    }


    /**
     * @throws ilMDServicesException
     */
    public function initMetadata(\ilObject $object): void
    {
        // create LOM set from scratch
        $this->lom_services->derive()
            ->fromBasicProperties($object->getTitle())
            ->forObject($object->getId(), $object->getId(), $object->getType());

        // in a second step, set the keywords
        $keywords = [];
        foreach ($object->getProvider()->getKeywordsArray() as $keyword) {
            if ($keyword !== '') {
                $keywords[] = $keyword;
            }
        }
        $this->lom_services->manipulate($object->getId(), $object->getId(), $object->getType())
            ->prepareCreateOrUpdate(
                $this->lom_services->paths()->keywords(),
                ...$keywords
            )->execute();
    }

    /**
     * @throws ilCtrlException
     */
    protected function buildForm(ilLTIConsumeProvider $provider): ilLTIConsumeProviderFormGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $form = new ilLTIConsumeProviderFormGUI($provider);
        $form->initForm(
            $DIC->ctrl()->getFormAction($this),
            self::CMD_SAVE_SETTINGS,
            self::CMD_SHOW_SETTINGS
        );

        return $form;
    }
}
