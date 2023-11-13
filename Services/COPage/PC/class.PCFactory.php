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

namespace ILIAS\COPage\PC;

use ILIAS\COPage\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCFactory
{
    protected ?\ilLogger $log;
    protected PCDefinition $pc_definition;

    public function __construct(PCDefinition $pc_definition)
    {
        global $DIC;

        $this->pc_definition = $pc_definition;
        $this->log = $DIC->copage()->internal()->domain()->log();
    }

    public function getByNode(
        ?\DOMNode $node,
        \ilPageObject $page_object
    ): ?\ilPageContent {
        if (is_null($node)) {
            return null;
        }
        $a_hier_id = $node->getAttribute("HierId");
        $a_pc_id = $node->getAttribute("PCID");
        $child_node = null;
        if (!is_object($node)) {
            $this->log->debug("No node passed.");
            return null;
        }
        $node_name = $node->nodeName;
        if (in_array($node_name, ["PageObject", "TableRow"])) {
            $this->log->debug("$node_name -> return.");
            return null;
        }
        if ($node_name == "PageContent") {
            $child_node = $node->firstChild;
            $node_name = $child_node->nodeName;
        }

        // table extra handling (@todo: get rid of it)
        if ($node_name == "Table") {
            if ($child_node->getAttribute("DataTable") == "y") {
                $tab = new \ilPCDataTable($page_object);
            } else {
                $tab = new \ilPCTable($page_object);
            }
            $tab->setDomNode($node);
            $tab->setHierId($a_hier_id);
            $tab->setPcId($a_pc_id);
            $this->log->debug("return table.");
            return $tab;
        }

        // media extra handling (@todo: get rid of it)
        if ($node_name == "MediaObject") {
            $mal_node = $child_node->firstChild;
            //echo "ilPageObject::getContentObject:nodename:".$mal_node->node_name().":<br>";
            $id_arr = explode("_", $mal_node->getAttribute("OriginId"));
            $mob_id = (int) $id_arr[count($id_arr) - 1];

            // see also #32331
            if (\ilObject::_lookupType($mob_id) !== "mob") {
                $mob_id = 0;
            }

            //$mob = new ilObjMediaObject($mob_id);
            $mob = new \ilPCMediaObject($page_object);
            $mob->readMediaObject($mob_id);

            //$mob->setDom($this->dom);
            $mob->setDomNode($node);
            $mob->setHierId($a_hier_id);
            $mob->setPcId($a_pc_id);
            $this->log->debug("return media.");
            return $mob;
        }

        //
        // generic procedure
        //

        $pc_def = $this->pc_definition->getPCDefinitionByName($node_name);

        // check if pc definition has been found
        if (!is_array($pc_def)) {
            throw new \ilCOPageUnknownPCTypeException('Unknown PC Name "' . $node_name . '".');
        }
        $pc_class = "ilPC" . $pc_def["name"];
        $pc_path = "./" . $pc_def["component"] . "/" . $pc_def["directory"] . "/class." . $pc_class . ".php";
        //require_once($pc_path);
        $pc = new ("\\" . $pc_class)($page_object);
        if (!in_array(
            $node->nodeName,
            ["PageContent", "TableData", "FileItem"]
        )) {
            $this->log->debug("returning null.");
            return null;
        }
        $pc->setDomNode($node);
        $pc->setHierId($a_hier_id);
        $pc->setPcId($a_pc_id);
        //$this->log->debug("returning $pc_class.");
        return $pc;
    }
}
