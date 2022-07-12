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
 * DOM 2 util
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDOM2Util
{
    // change node name
    public static function changeName(
        DOMNode $node,
        string $name,
        bool $keep_attributes = true
    ) : DOMNode {
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
    public static function addParent(
        DOMNode $node,
        string $name
    ) : DOMNode {
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
    public static function replaceByChilds(DOMNode $node) : void
    {
        foreach ($node->childNodes as $child) {
            $child2 = $child->cloneNode(true);
            $node->parentNode->insertBefore($child2, $node);
        }
        $node->parentNode->removeChild($node);
    }
}
