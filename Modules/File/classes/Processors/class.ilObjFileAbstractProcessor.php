<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;

/**
 * Class ilObjFileAbstractProcessorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
abstract class ilObjFileAbstractProcessor implements ilObjFileProcessorInterface
{
    /**
     * @var \ILIAS\ResourceStorage\Services
     */
    protected $storage;

    /**
     * @var ResourceStakeholder
     */
    protected $stakeholder;

    /**
     * @var ilAccess|ilPortfolioAccessHandler|ilWorkspaceAccessHandler
     */
    protected $access_handler;

    /**
     * @var ilObject2GUI
     */
    protected $gui_object;

    /**
     * @var ilWorkspaceTree|ilTree
     */
    protected $tree;

    /**
     * ilObjFileAbstractZipProcessor constructor.
     *
     * @param ResourceStakeholder                                        $stakeholder
     * @param ilObject2GUI                                               $gui_object
     * @param ilWorkspaceAccessHandler|ilPortfolioAccessHandler|ilAccess $access_handler
     * @param ilWorkspaceTree|ilTree                                     $tree
     */
    public function __construct(
        ResourceStakeholder $stakeholder,
        ilObject2GUI $gui_object,
        $access_handler,
        $tree
    ) {
        global $DIC;

        $this->storage = $DIC->resourceStorage();

        $this->stakeholder    = $stakeholder;
        $this->gui_object     = $gui_object;
        $this->access_handler = $access_handler;
        $this->tree           = $tree;
    }

    /**
     * Creates an ilObjFile instance for the provided information.
     * @param ResourceIdentification $rid
     * @param int                    $parent_id
     * @param array<string, mixed>   $options
     * @return ilObjFile
     *@see ilObjFileAbstractProcessorInterface::OPTIONS
     */
    protected function createFileObj(ResourceIdentification $rid, int $parent_id, array $options = []) : ilObjFile
    {
        $revision = $this->storage->manage()->getCurrentRevision($rid);
        $file_obj = new ilObjFile();
        $file_obj->setResourceId($rid);

        // set revision title as default, as it may be overwritten by $options
        $file_obj->setTitle($revision->getTitle());

        if (!empty($options)) {
            $this->applyOptions($file_obj, $options);
        }

        $file_obj->create();

        $this->gui_object->putObjectInTree($file_obj, $parent_id);

        return $file_obj;
    }

    /**
     * Apply provided options to the given object.
     *
     * @param ilObject $obj
     * @param array    $options
     */
    protected function applyOptions(ilObject $obj, array $options) : void
    {
        foreach ($options as $key => $option) {
            if (in_array($key, self::OPTIONS, true)) {
                if (!empty($option)) {
                    $setter = "set" . ucfirst($key);
                    $obj->{$setter}($option);
                }
            } else {
                throw new LogicException("Option '$key' is not declared in " . static::class . "::OPTIONS.");
            }
        }
    }
}