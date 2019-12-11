<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Feeds/classes/class.ilFeedItem.php");

/** @defgroup ServicesFeeds Services/Feeds
 */

/**
* Feed writer class.
*
* how to make it "secure"
* alternative 1:
* - hash for all objects
* - feature "mail me rss link"
* - link includes ref id, user id, combined hash (kind of password)
* - combined hash = hash(user hash + object hash)
* - ilias checks whether ref id / user id / combined hash match
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesFeeds
*/
class ilFeedWriter
{
    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public $encoding = "UTF-8";
    public $ch_about = "";
    public $ch_title = "";
    public $ch_link = "";
    public $ch_description = "";
    public $items = array();

    /**
     * ilFeedWriter constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
    }
    
    /**
    * Set feed encoding. Default is UTF-8.
    */
    public function setEncoding($a_enc)
    {
        $this->encoding = $a_enc;
    }
    
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
    * Unique URI that defines the channel
    */
    public function setChannelAbout($a_ab)
    {
        $this->ch_about = $a_ab;
    }
    
    public function getChannelAbout()
    {
        return $this->ch_about;
    }

    /**
    * Channel Title
    */
    public function setChannelTitle($a_title)
    {
        $this->ch_title = $a_title;
    }
    
    public function getChannelTitle()
    {
        return $this->ch_title;
    }

    /**
    * Channel Link
    * URL to which an HTML rendering of the channel title will link
    */
    public function setChannelLink($a_link)
    {
        $this->ch_link = $a_link;
    }
    
    public function getChannelLink()
    {
        return $this->ch_link;
    }

    /**
    * Channel Description
    */
    public function setChannelDescription($a_desc)
    {
        $this->ch_desc = $a_desc;
    }
    
    public function getChannelDescription()
    {
        return $this->ch_desc;
    }

    /**
    * Add Item
    * Item is an object of type ilFeedItem
    */
    public function addItem($a_item)
    {
        $this->items[] = $a_item;
    }
    
    public function getItems()
    {
        return $this->items;
    }

    public function prepareStr($a_str)
    {
        $a_str = str_replace("&", "&amp;", $a_str);
        $a_str = str_replace("<", "&lt;", $a_str);
        $a_str = str_replace(">", "&gt;", $a_str);
        return $a_str;
    }

    /**
    * get feed xml
    */
    public function getFeed()
    {
        include_once("./Services/UICore/classes/class.ilTemplate.php");
        $this->tpl = new ilTemplate("tpl.rss_2_0.xml", true, true, "Services/Feeds");
        
        $this->tpl->setVariable("XML", "xml");
        $this->tpl->setVariable("CONTENT_ENCODING", $this->getEncoding());
        $this->tpl->setVariable("CHANNEL_ABOUT", $this->getChannelAbout());
        $this->tpl->setVariable("CHANNEL_TITLE", $this->getChannelTitle());
        $this->tpl->setVariable("CHANNEL_LINK", $this->getChannelLink());
        $this->tpl->setVariable("CHANNEL_DESCRIPTION", $this->getChannelDescription());
        
        foreach ($this->items as $item) {
            $this->tpl->setCurrentBlock("rdf_seq");
            $this->tpl->setVariable("RESOURCE", $item->getAbout());
            $this->tpl->parseCurrentBlock();
            
            // Date
            if ($item->getDate() != "") {
                $this->tpl->setCurrentBlock("date");
                $d = $item->getDate();
                $yyyy = substr($d, 0, 4);
                $mm = substr($d, 5, 2);
                $dd = substr($d, 8, 2);
                $h = substr($d, 11, 2);
                $m = substr($d, 14, 2);
                $s = substr($d, 17, 2);
                $this->tpl->setVariable(
                    "ITEM_DATE",
                    date("r", mktime($h, $m, $s, $mm, $dd, $yyyy))
                );
                $this->tpl->parseCurrentBlock();
            }
            
            // Enclosure
            if ($item->getEnclosureUrl() != "") {
                $this->tpl->setCurrentBlock("enclosure");
                $this->tpl->setVariable("ENC_URL", $item->getEnclosureUrl());
                $this->tpl->setVariable("ENC_LENGTH", $item->getEnclosureLength());
                $this->tpl->setVariable("ENC_TYPE", $item->getEnclosureType());
                $this->tpl->parseCurrentBlock();
            }
            
            $this->tpl->setCurrentBlock("item");
            $this->tpl->setVariable("ITEM_ABOUT", $item->getAbout());
            $this->tpl->setVariable("ITEM_TITLE", $item->getTitle());
            $this->tpl->setVariable("ITEM_DESCRIPTION", $item->getDescription());
            $this->tpl->setVariable("ITEM_LINK", $item->getLink());
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->parseCurrentBlock();
        return $this->tpl->get();
    }
    
    public function showFeed()
    {
        header("Content-Type: text/xml; charset=UTF-8;");
        echo $this->getFeed();
    }

    public function getContextPath($a_ref_id)
    {
        $tree = $this->tree;
        $lng = $this->lng;

        $items = array();

        if ($a_ref_id > 0) {
            $path = $tree->getPathFull($a_ref_id);

            // we want to show the full path, from the major container to the item
            // (folders are not! treated as containers here), at least one parent item
            $r_path = array_reverse($path);
            $first = "";
            $omit = array();
            $do_omit = false;
            foreach ($r_path as $key => $row) {
                if ($first == "") {
                    if (in_array($row["type"], array("root", "cat", "grp", "crs"))) {
                        $first = $row["child"];
                    }
                }
                $omit[$row["child"]] = $do_omit;
            }

            $add_it = false;
            foreach ($path as $key => $row) {
                if ($first == $row["child"]) {
                    $add_it = true;
                }

                if ($add_it && !$omit[$row["child"]] &&
                    (($row["child"] != $a_ref_id))) {
                    if ($row["title"] == "ILIAS" && $row["type"] == "root") {
                        $row["title"] = $lng->txt("repository");
                    }
                    $items[] = $row["title"];
                }
            }
        }

        if (count($items) > 0) {
            return "[" . implode(" > ", $items) . "]";
        }
        return "";
    }
}
