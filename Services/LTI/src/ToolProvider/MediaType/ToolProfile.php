<?php

namespace ILIAS\LTI\ToolProvider\MediaType;

use ILIAS\LTI\ToolProvider\Tool;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class to represent an LTI Tool Profile
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ToolProfile
{

    /**
     * Product instance object.
     *
     * @var object $product_instance
     */
    public object $product_instance;

    /**
     * Class constructor.
     * @param Tool $tool Tool object
     */
    public function __construct(Tool $tool)
    {
        $this->lti_version = 'LTI-2p0';

        if (!empty($tool->product)) {
            $this->product_instance = new \stdClass;
        }
        if (!empty($tool->product->id)) {
            $this->product_instance->guid = $tool->product->id;
        }
        if (!empty($tool->product->name)) {
            $this->product_instance->product_info = new \stdClass;
            $this->product_instance->product_info->product_name = new \stdClass;
            $this->product_instance->product_info->product_name->default_value = $tool->product->name;
            $this->product_instance->product_info->product_name->key = 'tool.name';
        }
        if (!empty($tool->product->description)) {
            $this->product_instance->product_info->description = new \stdClass;
            $this->product_instance->product_info->description->default_value = $tool->product->description;
            $this->product_instance->product_info->description->key = 'tool.description';
        }
        if (!empty($tool->product->url)) {
            $this->product_instance->guid = $tool->product->url;
        }
        if (!empty($tool->product->version)) {
            $this->product_instance->product_info->product_version = $tool->product->version;
        }
        if (!empty($tool->vendor)) {
            $this->product_instance->product_info->product_family = new \stdClass;
            $this->product_instance->product_info->product_family->vendor = new \stdClass;
            if (!empty($tool->product->id)) {
                $this->product_instance->product_info->product_family->code = $tool->product->id;
            }
        }
        if (!empty($tool->vendor->id)) {
            $this->product_instance->product_info->product_family->vendor->code = $tool->vendor->id;
        }
        if (!empty($tool->vendor->name)) {
            $this->product_instance->product_info->product_family->vendor->vendor_name = new \stdClass;
            $this->product_instance->product_info->product_family->vendor->vendor_name->default_value = $tool->vendor->name;
            $this->product_instance->product_info->product_family->vendor->vendor_name->key = 'tool.vendor.name';
        }
        if (!empty($tool->vendor->description)) {
            $this->product_instance->product_info->product_family->vendor->description = new \stdClass;
            $this->product_instance->product_info->product_family->vendor->description->default_value = $tool->vendor->description;
            $this->product_instance->product_info->product_family->vendor->description->key = 'tool.vendor.description';
        }
        if (!empty($tool->vendor->url)) {
            $this->product_instance->product_info->product_family->vendor->website = $tool->vendor->url;
        }
        if (!empty($tool->vendor->timestamp)) {
            $this->product_instance->product_info->product_family->vendor->timestamp = date(
                'Y-m-d\TH:i:sP',
                $tool->vendor->timestamp
            );
        }

        $this->resource_handler = array();
        foreach ($tool->resourceHandlers as $resourceHandler) {
            $this->resource_handler[] = new ResourceHandler($tool, $resourceHandler);
        }
        if (!empty($tool->baseUrl)) {
            $this->base_url_choice = array();
            $this->base_url_choice[] = new \stdClass;
            $this->base_url_choice[0]->default_base_url = $tool->baseUrl;
        }
    }
}
