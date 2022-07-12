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

/**
 * class for DOM utilities
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDOMUtil
{
    /**
     * searches for an element $a_node_name within the childs of $parent_node
     * if no node is found, a new is created before the childs with names of
     * $a_successors. the content of the node is set to $a_content and the
     * attributes to $a_attributes
     */
    public static function setFirstOptionalElement(
        php4DOMDocument $doc,
        php4DOMElement $parent_node,
        string $a_node_name,
        array $a_successors,
        string $a_content,
        array $a_attributes,
        bool $a_remove_childs = true
    ) : void {
        $search = $a_successors;
        $search[] = $a_node_name;
        $child_name = "";
        $child = null;

        $childs = $parent_node->child_nodes();
        $found = false;
        foreach ($childs as $child) {
            $child_name = $child->node_name();
            //echo "B$child_name";
            if (in_array($child_name, $search)) {
                //echo "C";
                $found = true;
                break;
            }
        }
        // didn't find element
        if (!$found) {
            $new_node = $doc->create_element($a_node_name);
            $new_node = $parent_node->append_child($new_node);
            if ($a_content != "") {
                $new_node->set_content($a_content);
            }
            ilDOMUtil::set_attributes($new_node, $a_attributes);
        } else {
            if ($child_name == $a_node_name) {
                if ($a_remove_childs) {
                    $childs2 = $child->child_nodes();
                    for ($i = 0; $i < count($childs2); $i++) {
                        $child->remove_child($childs2[$i]);
                    }
                }
                if ($a_content != "") {
                    $child->set_content($a_content);
                }
                ilDOMUtil::set_attributes($child, $a_attributes);
            } else {
                $new_node = $doc->create_element($a_node_name);
                $new_node = $child->insert_before($new_node, $child);
                if ($a_content != "") {
                    $new_node->set_content($a_content);
                }
                ilDOMUtil::set_attributes($new_node, $a_attributes);
            }
        }
    }

    /**
     * set attributes of a node
     * @param	array	$a_attributes	attributes array (attribute_name => attribute_value pairs)
     */
    public static function set_attributes(
        php4DOMElement $a_node,
        array $a_attributes
    ) : void {
        foreach ($a_attributes as $attribute => $value) {
            if ($value != "") {
                $a_node->set_attribute($attribute, $value);
            } else {
                if ($a_node->has_attribute($attribute)) {
                    $a_node->remove_attribute($attribute);
                }
            }
        }
    }

    /**
     * delete all childs of a node by names in $a_node_names
     */
    public static function deleteAllChildsByName(
        php4DOMElement $a_parent,
        array $a_node_names
    ) : void {
        $childs = $a_parent->child_nodes();
        foreach ($childs as $child) {
            $child_name = $child->node_name();
            if (in_array($child_name, $a_node_names)) {
                $child->unlink_node();
            }
        }
    }

    /**
     * Places a new node $a_node_name directly before nodes with names of
     * $a_successors. The content of the node is set to $a_content and the
     * attributes to $a_attributes
     */
    public static function addElementToList(
        php4DOMDocument $doc,
        php4DOMElement $parent_node,
        string $a_node_name,
        array $a_successors,
        string $a_content,
        array $a_attributes
    ) : php4DOMElement {
        $search = $a_successors;
        $child = null;
        $childs = $parent_node->child_nodes();
        $cnt_childs = count($childs);
        $found = false;
        foreach ($childs as $child) {
            $child_name = $child->node_name();
            if (in_array($child_name, $search)) {
                $found = true;
                break;
            }
        }
        // didn't successors -> append at the end
        $new_node = $doc->create_element($a_node_name);
        if (!$found) {
            $new_node = $parent_node->append_child($new_node);
        } else {
            $new_node = $child->insert_before($new_node, $child);
        }
        if ($a_content != "") {
            $new_node->set_content($a_content);
        }
        ilDOMUtil::set_attributes($new_node, $a_attributes);

        return $new_node;
    }
} // END class.ilDOMUtil
