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

namespace ILIAS\COPage\Dom;

/**
 * DOM 2 util
 * @author Alexander Killing <killing@leifos.de>
 */
class DomUtil
{
    // change node name
    public function changeName(
        \DOMNode $node,
        string $name,
        bool $keep_attributes = true
    ): \DOMNode {
        $newnode = $node->ownerDocument->createElement($name);

        foreach ($node->childNodes as $child) {
            $child2 = $child->cloneNode(true);
            $newnode->appendChild($child2);
        }
        if ($keep_attributes) {
            foreach ($node->attributes as $attrName => $attrNode) {
                $newnode->setAttribute($attrName, $attrNode);
            }
        }
        $node->parentNode->replaceChild($newnode, $node);

        return $newnode;
    }

    // Add parent
    public function addParent(
        \DOMNode $node,
        string $name
    ): \DOMNode {
        $newnode = $node->ownerDocument->createElement($name);
        $par = $node->parentNode;
        if ($next_sib = $node->nextSibling) {
            $newnode = $par->insertBefore($newnode, $next_sib);
        } else {
            $newnode = $par->appendChild($newnode);
        }

        $node = $par->removeChild($node);
        $newnode->appendChild($node);
        return $newnode;
    }

    // Replace a node by its child
    public function replaceByChilds(\DOMNode $node): void
    {
        foreach ($node->childNodes as $child) {
            $child2 = $child->cloneNode(true);
            $node->parentNode->insertBefore($child2, $node);
        }
        $node->parentNode->removeChild($node);
    }

    // delete all childs of a node by names in $node_names
    public function deleteAllChildsByName(
        \DOMNode $parent,
        array $node_names
    ): void {
        foreach ($parent->childNodes as $child) {
            if (in_array($child->nodeName, $node_names)) {
                $parent->removeChild($child);
            }
        }
    }

    /**
     * set attributes of a node
     */
    public function setAttributes(
        \DOMNode $node,
        array $attributes
    ): void {
        foreach ($attributes as $attribute => $value) {
            if (!is_null($value) && $value !== "") {
                $node->setAttribute($attribute, $value);
            } elseif ($node->hasAttribute($attribute)) {
                $node->removeAttribute($attribute);
            }
        }
    }

    /**
     * searches for an element $node_name within the childs of $parent_node
     * if no node is found, a new is created before the childs with names of
     * $successors. the content of the node is set to $content and the
     * attributes to $attributes
     */
    public function setFirstOptionalElement(
        \DOMNode $parent_node,
        string $node_name,
        array $successors,
        string $content,
        array $attributes,
        bool $remove_childs = true
    ): void {
        $doc = $parent_node->ownerDocument;
        $search = $successors;
        $search[] = $node_name;
        $child_name = "";
        $child = null;

        $found = false;
        foreach ($parent_node->childNodes as $child) {
            $child_name = $child->nodeName;
            if (in_array($child_name, $search)) {
                $found = true;
                break;
            }
        }
        // didn't find element
        if (!$found) {
            $new_node = $doc->createElement($node_name);
            $new_node = $parent_node->appendChild($new_node);
            if ($content != "") {
                $new_node->nodeValue = $content;
            }
            $this->setAttributes($new_node, $attributes);
        } else {
            if ($child_name == $node_name) {
                if ($remove_childs) {
                    foreach ($child->childNodes as $child2) {
                        $child->removeChild($child2);
                    }
                }
                if ($content != "") {
                    $child->setContent($content);
                }
                $this->setAttributes($child, $attributes);
            } else {
                $new_node = $doc->createElement($node_name);
                $new_node = $child->insertBefore($new_node, $child);
                if ($content != "") {
                    $new_node->set_content($content);
                }
                $this->setAttributes($new_node, $attributes);
            }
        }
    }
}
