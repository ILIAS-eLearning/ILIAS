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
 * Feed writer class.
 * how to make it "secure"
 * alternative 1:
 * - hash for all objects
 * - feature "mail me rss link"
 * - link includes ref id, user id, combined hash (kind of password)
 * - combined hash = hash(user hash + object hash)
 * - ilias checks whether ref id / user id / combined hash match
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFeedWriter
{
    private string $ch_desc = "";
    protected ilTree $tree;
    protected ilLanguage $lng;
    protected ilTemplate $tpl;
    public string $encoding = "UTF-8";
    public string $ch_about = "";
    public string $ch_title = "";
    public string $ch_link = "";
    public string $ch_description = "";
    public array $items = array();

    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->lng = $DIC->language();
    }

    public function setEncoding(string $a_enc) : void
    {
        $this->encoding = $a_enc;
    }

    public function getEncoding() : string
    {
        return $this->encoding;
    }

    public function setChannelAbout(string $a_ab) : void
    {
        $this->ch_about = $a_ab;
    }

    public function getChannelAbout() : string
    {
        return $this->ch_about;
    }

    public function setChannelTitle(string $a_title) : void
    {
        $this->ch_title = $a_title;
    }

    public function getChannelTitle() : string
    {
        return $this->ch_title;
    }

    public function setChannelLink(string $a_link) : void
    {
        $this->ch_link = $a_link;
    }

    public function getChannelLink() : string
    {
        return $this->ch_link;
    }

    public function setChannelDescription(string $a_desc) : void
    {
        $this->ch_desc = $a_desc;
    }

    public function getChannelDescription() : string
    {
        return $this->ch_desc;
    }

    public function addItem(ilFeedItem $a_item) : void
    {
        $this->items[] = $a_item;
    }

    public function getItems() : array
    {
        return $this->items;
    }

    public function prepareStr(string $a_str) : string
    {
        $a_str = str_replace(["&", "<", ">"], ["&amp;", "&lt;", "&gt;"], $a_str);
        return $a_str;
    }

    public function getFeed() : string
    {
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

    public function showFeed() : void
    {
        header("Content-Type: text/xml; charset=UTF-8;");
        echo $this->getFeed();
    }

    public function getContextPath(int $a_ref_id) : string
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
