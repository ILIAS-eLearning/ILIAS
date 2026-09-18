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

namespace ILIAS\Style\Content;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICGUIServices;
use ILIAS\Style\Content\Characteristic\CharacteristicTableBuilder;
use ILIAS\Style\Content\Color\ColorTableBuilder;
use ILIAS\Style\Content\MediaQuery\MediaQueryTableBuilder;
use ILIAS\Style\Content\Template\TemplateTableBuilder;
use ilObjectContentStyleSettingsGUI;

/**
 * Content style internal ui factory
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalGUIService
{
    use GlobalDICGUIServices;

    protected InternalDataService $data_service;
    protected InternalDomainService $domain_service;

    protected CharacteristicUIFactory $characteristic;
    protected ImageUIFactory $image;

    public function __construct(
        Container $DIC,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->data_service = $data_service;
        $this->domain_service = $domain_service;
        $this->initGUIServices($DIC);
        $this->characteristic = new CharacteristicUIFactory(
            $this->domain_service,
            $this
        );
        $this->image = new ImageUIFactory(
            $this->domain_service,
            $this
        );
    }

    public function characteristic(
    ): CharacteristicUIFactory {
        return $this->characteristic;
    }

    public function image(
    ): ImageUIFactory {
        return $this->image;
    }

    public function characteristicTableBuilder(
        string $super_type,
        CharacteristicManager $manager,
        Access\StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ): CharacteristicTableBuilder {
        return new CharacteristicTableBuilder(
            $this->domain_service,
            $super_type,
            $manager,
            $access_manager,
            $parent_gui,
            $parent_cmd
        );
    }

    public function colorTableBuilder(
        \ilObjStyleSheet $style_obj,
        Access\StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ): ColorTableBuilder {
        return new ColorTableBuilder(
            $this->domain_service,
            $style_obj,
            $access_manager,
            $parent_gui,
            $parent_cmd
        );
    }

    public function templateTableBuilder(
        \ilObjStyleSheet $style_obj,
        string $temp_type,
        Access\StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ): TemplateTableBuilder {
        return new TemplateTableBuilder(
            $this->domain_service,
            $style_obj,
            $temp_type,
            $access_manager,
            $parent_gui,
            $parent_cmd
        );
    }

    public function mediaQueryTableBuilder(
        \ilObjStyleSheet $style_obj,
        Access\StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ): MediaQueryTableBuilder {
        return new MediaQueryTableBuilder(
            $this->domain_service,
            $style_obj,
            $access_manager,
            $parent_gui,
            $parent_cmd
        );
    }

    public function contentStylesTableBuilder(
        array $data,
        int $default_style,
        int $fixed_style,
        Access\StyleAccessManager $access_manager,
        object $parent_gui,
        string $parent_cmd
    ): ContentStylesTableBuilder {
        return new ContentStylesTableBuilder(
            $this->domain_service,
            $this,
            $data,
            $default_style,
            $fixed_style,
            $access_manager,
            $parent_gui,
            $parent_cmd
        );
    }

    public function standardRequest(
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ): StandardGUIRequest {
        return new StandardGUIRequest(
            $this->http(),
            $this->domain_service->refinery(),
            $passed_query_params,
            $passed_post_data
        );
    }

    // get class name of object settings gui class
    public function objectSettingsClass(bool $lower = true): string
    {
        $class = ilObjectContentStyleSettingsGUI::class;
        if ($lower) {
            $class = strtolower($class);
        }
        return $class;
    }

    // get instance of objecgt settings gui class
    public function objectSettingsGUI(
        ?int $selected_style_id,
        int $ref_id,
        int $obj_id = 0
    ): ilObjectContentStyleSettingsGUI {
        return new ilObjectContentStyleSettingsGUI(
            $this->domain_service,
            $this,
            $selected_style_id,
            $ref_id,
            $obj_id
        );
    }
}
