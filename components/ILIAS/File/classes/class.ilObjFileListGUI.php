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

use ILIAS\File\Capabilities\CapabilityCollection;
use ILIAS\File\Icon\IconDatabaseRepository;
use ILIAS\ResourceStorage\Services;
use ILIAS\components\WOPI\Discovery\ActionDBRepository;
use ILIAS\File\Capabilities\Capabilities;
use ILIAS\File\Capabilities\CapabilityBuilder;
use ILIAS\File\Capabilities\Context;

/**
 * Class ilObjFileListGUI
 * @author        Alex Killing <alex.killing@gmx.de>
 * @author        Stefan Born <stefan.born@phzh.ch>
 * @author        Fabian Schmid <fs@studer-raimann.ch>
 * @author        Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilObjFileListGUI extends ilObjectListGUI
{
    use ilObjFileSecureString;

    private ilObjFileInfoRepository $file_info;
    private CapabilityBuilder $capability_builder;
    private ?CapabilityCollection $capabilities = null;
    private Context $capability_context;
    protected string $title;
    private IconDatabaseRepository $icon_repo;
    private Services $irss;

    public function __construct(int $context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;
        $this->capability_context = new Context(
            0,
            0,
            ($context === self::CONTEXT_REPOSITORY) ? Context::CONTEXT_REPO : Context::CONTEXT_WORKSPACE
        );

        parent::__construct($context);


        $DIC->language()->loadLanguageModule('wopi');
        $this->file_info = new ilObjFileInfoRepository();
        $this->capability_builder = new CapabilityBuilder(
            $this->file_info,
            $this->access,
            $this->ctrl,
            new ActionDBRepository($DIC->database()),
            $DIC->http(),
            $DIC['static_url.uri_builder']
        );


    }

    protected function updateContext(): void
    {
        $this->capability_context = $this->capability_context
            ->withCallingId($this->ref_id ?? 0)
            ->withObjectId($this->obj_id ?? 0);
    }

    /**
     * @description This methods seems to be called by ItemRenderer
     * @deprecated
     */
    public function insertCommands(): void
    {
    }
    /**
     * initialisation
     */
    #[\Override]
    public function init(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = false;
        $this->type = ilObjFile::OBJECT_TYPE;
        $this->gui_class_name = ilObjFileGUI::class;
        $this->icon_repo = new IconDatabaseRepository();

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        $this->commands = ilObjFileAccess::_getCommands();
        $this->updateContext();
    }

    #[\Override]
    public function getCommands(): array
    {
        $this->updateContext();
        $this->capabilities = $this->capability_builder->get($this->capability_context);

        $best = $this->capabilities->getBest();

        foreach ($this->commands as $key => $command) {
            if ($command['cmd'] === $best->getCapability()->value) {
                $default_set = true;
                $this->commands[$key]['default'] = true;
            }
        }

        return parent::getCommands();
    }

    #[\Override]
    public function getCommandLink(string $cmd): string
    {
        $this->updateContext();
        $info = $this->file_info->getByObjectId($this->obj_id);
        $this->capabilities = $this->capability_builder->get($this->capability_context);

        $needed_capability = Capabilities::fromCommand($cmd);
        $capability = $this->capabilities->get($needed_capability);
        if ($capability === false || !$capability->isUnlocked()) {
            return '';
        }

        switch ($this->context) {
            case self::CONTEXT_REPOSITORY:
                return (string) $capability->getURI();
            case self::CONTEXT_WORKSPACE:
                $this->ctrl->setParameterByClass(ilObjFileGUI::class, 'wsp_id', $this->ref_id);
                if ($cmd === "sendfile" && !ilObjFileAccess::_shouldDownloadDirectly($this->obj_id)) {
                    return $this->ctrl->getLinkTargetByClass(
                        ilObjFileGUI::class,
                        Capabilities::INFO_PAGE->value
                    );
                }
                break;

        }

        return parent::getCommandLink($cmd);
    }



    #[\Override]
    public function getTitle(): string
    {
        return $this->file_info->getByObjectId($this->obj_id)->getListTitle();
    }

    public function stripTitleOfFileExtension(string $a_title): string
    {
        return $this->secure(preg_replace('/\.[^.]*$/', '', $a_title));
    }

    #[\Override]
    public function getCommandFrame(string $cmd): string
    {
        $this->updateContext();
        $info = $this->file_info->getByObjectId($this->obj_id);

        if ($cmd === Capabilities::DOWNLOAD->value) {
            return $info->shouldDeliverInline() ? '_blank' : '';
        }

        return '';
    }

    /**
     * Returns the icon image type.
     * For most objects, this is same as the object type, e.g. 'cat','fold'.
     * We can return here other values, to express a specific state of an object,
     * e.g. 'crs_offline', and/or to express a specific kind of object, e.g.
     * 'file_inline'.
     */
    #[\Override]
    public function getIconImageType(): string
    {
        return $this->file_info->getByObjectId($this->obj_id)->shouldDeliverInline()
            ? $this->type . '_inline'
            : $this->type;
    }


    /**
     * Get item properties
     * @return    array        array of property arrays:
     *                        "alert" (boolean) => display as an alert property (usually in red)
     *                        "property" (string) => property name
     *                        "value" (string) => property value
     */
    #[\Override]
    public function getProperties(): array
    {
        global $DIC;

        $this->capabilities = $this->capability_builder->get($this->capability_context);

        $props = parent::getProperties();

        $info = $this->file_info->getByObjectId($this->obj_id);

        $revision = $info->getVersion();

        $props[] = [
            "alert" => false,
            "property" => $DIC->language()->txt("type"),
            "value" => $info->getSuffix(),
            'propertyNameVisible' => false,
        ];

        $props[] = [
            "alert" => false,
            "property" => $DIC->language()->txt("size"),
            "value" => (string) $info->getFileSize(),
            'propertyNameVisible' => false,
        ];

        $version = $info->getVersion();
        if ($version > 1) {

            // add versions link
            if ($this->capabilities->get(Capabilities::MANAGE_VERSIONS)->isUnlocked()) {
                $link = $this->getCommandLink("versions");
                $value = "<a href=\"$link\">" . $DIC->language()->txt("version") . ": $version</a>";
            } else {
                $value = $DIC->language()->txt("version") . ": $version";
            }
            $props[] = [
                "alert" => false,
                "property" => $DIC->language()->txt("version"),
                "value" => $value,
                "propertyNameVisible" => false
            ];
        }

        $props[] = [
            "alert" => false,
            "property" => $DIC->language()->txt("last_update"),
            "value" => ilDatePresentation::formatDate(
                new ilDateTime($info->getCreationDate()->format('U'), IL_CAL_UNIX)
            ),
            'propertyNameVisible' => false,
        ];

        if ($info->getPageCount() !== null && $info->getPageCount() > 0) {
            $props[] = [
                "alert" => false,
                "property" => $DIC->language()->txt("page_count"),
                "value" => $info->getPageCount(),
                'propertyNameVisible' => true,
            ];
        }

        return $props;
    }

    /**
     * Get command icon image
     */
    #[\Override]
    public function getCommandImage($a_cmd): string
    {
        return "";
    }

    #[\Override]
    public function checkCommandAccess(
        string $permission,
        string $cmd,
        int $ref_id,
        string $type,
        ?int $obj_id = null
    ): bool {
        $this->updateContext();

        $this->capability_context = $this->capability_context
            ->withCallingId($ref_id)
            ->withObjectId($obj_id ?? $this->capability_context->getObjectId());

        // LP settings only in repository
        if ($this->context !== self::CONTEXT_REPOSITORY && $permission === "edit_learning_progress") {
            return false;
        }

        $this->capabilities = $this->capability_builder->get($this->capability_context);

        $capability = Capabilities::fromCommand($cmd);
        $additional_check = $this->capabilities->get($capability)->isUnlocked();

        return $additional_check && parent::checkCommandAccess(
            $permission,
            $cmd,
            $ref_id,
            $type,
            $obj_id
        );
    }


}
