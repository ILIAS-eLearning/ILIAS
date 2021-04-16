<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */


/**
 * class for DOM utilities
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
        $doc,
        $parent_node,
        $a_node_name,
        $a_successors,
        $a_content,
        $a_attributes,
        $a_remove_childs = true
    ) {
        $search = $a_successors;
        $search[] = $a_node_name;

        $childs = $parent_node->child_nodes();
        $cnt_childs = count($childs);
        $found = false;
        //echo "A";
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
            $new_node = &$doc->create_element($a_node_name);
            $new_node = &$parent_node->append_child($new_node);
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
                $new_node = &$doc->create_element($a_node_name);
                $new_node = &$child->insert_before($new_node, $child);
                if ($a_content != "") {
                    $new_node->set_content($a_content);
                }
                ilDOMUtil::set_attributes($new_node, $a_attributes);
            }
        }
    }

    /**
    * set attributes of a node
    *
    * @param	object	$a_node			node
    * @param	array	$a_attributes	attributes array (attribute_name => attribute_value pairs)
    */
    public static function set_attributes($a_node, $a_attributes)
    {
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
    public static function deleteAllChildsByName($a_parent, $a_node_names)
    {
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
        $doc,
        $parent_node,
        $a_node_name,
        $a_successors,
        $a_content,
        $a_attributes
    ) {
        $search = $a_successors;

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
        if (!$found) {
            $new_node = $doc->create_element($a_node_name);
            $new_node = $parent_node->append_child($new_node);
            if ($a_content != "") {
                $new_node->set_content($a_content);
            }
            ilDOMUtil::set_attributes($new_node, $a_attributes);
        } else {
            $new_node = $doc->create_element($a_node_name);
            $new_node = $child->insert_before($new_node, $child);
            if ($a_content != "") {
                $new_node->set_content($a_content);
            }
            ilDOMUtil::set_attributes($new_node, $a_attributes);
        }
        
        return $new_node;
    }
} // END class.ilDOMUtil
