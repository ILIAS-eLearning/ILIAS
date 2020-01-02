<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Wraps $item arrays from magpie
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesFeeds
*/
class ilExternalFeedItem
{
    public function __construct()
    {
    }
    
    /**
    * Set Magpie Item and read it into internal variables
    */
    public function setMagpieItem($a_item)
    {
        $this->magpie_item = $a_item;

        //var_dump($a_item);
        
        // title
        $this->setTitle(
            $this->secureString($a_item["title"])
        );
        
        // link
        if (isset($a_item["link_"])) {
            $this->setLink(
                ilUtil::secureUrl(ilUtil::secureLink($this->secureString($a_item["link_"])))
            );
        } else {
            if (isset($a_item["link"])) {
                $this->setLink(
                    ilUtil::secureUrl(ilUtil::secureLink($this->secureString($a_item["link"])))
                );
            }
        }
        // summary
        if (isset($a_item["atom_content"])) {
            $this->setSummary(
                $this->secureString($a_item["atom_content"])
            );
        } elseif (isset($a_item["summary"])) {
            $this->setSummary(
                $this->secureString($a_item["summary"])
            );
        } elseif (isset($a_item["description"])) {
            $this->setSummary(
                $this->secureString($a_item["description"])
            );
        }
        
        // date
        if (isset($a_item["pubdate"])) {
            $this->setDate(
                $this->secureString($a_item["pubdate"])
            );
        } elseif (isset($a_item["updated"])) {
            $this->setDate(
                $this->secureString($a_item["updated"])
            );
        }

        // Author
        if (isset($a_item["dc"]["creator"])) {
            $this->setAuthor(
                $this->secureString($a_item["dc"]["creator"])
            );
        }

        // id
        $this->setId(md5($this->getTitle() . $this->getSummary()));
    }
    
    public function secureString($a_str)
    {
        $a_str = ilUtil::secureString($a_str, true, "<b><i><em><strong><br><ol><li><ul><a><img>");

        // set target to blank for all links
        $a_str = preg_replace_callback(
            '/<a[^>]*?href=["\']([^"\']*)["\'][^>]*?>/i',
            function ($matches) {
                return sprintf(
                    '<a href="%s" target="_blank" rel="noopener">',
                    \ilUtil::secureUrl($matches[1])
                );
            },
            $a_str
        );

        return $a_str;
    }
    
    /**
    * Get Magpie Item
    *
    * @return	object	Magpie Item
    */
    public function getMagpieItem()
    {
        return $this->magpie_item;
    }

    /**
    * Set Title.
    *
    * @param	string	$a_title	Title
    */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }

    /**
    * Get Title.
    *
    * @return	string	Title
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * Set Link.
    *
    * @param	string	$a_link	Link
    */
    public function setLink($a_link)
    {
        $this->link = $a_link;
    }

    /**
    * Get Link.
    *
    * @return	string	Link
    */
    public function getLink()
    {
        return $this->link;
    }

    /**
    * Set Summary.
    *
    * @param	string	$a_summary	Summary
    */
    public function setSummary($a_summary)
    {
        $this->summary = $a_summary;
    }

    /**
    * Get Summary.
    *
    * @return	string	Summary
    */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
    * Set Date.
    *
    * @param	string	$a_date	Date
    */
    public function setDate($a_date)
    {
        $this->date = $a_date;
    }

    /**
    * Get Date.
    *
    * @return	string	Date
    */
    public function getDate()
    {
        return $this->date;
    }

    /**
    * Set Id.
    *
    * @param	string	$a_id	Id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
    * Get Id.
    *
    * @return	string	Id
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Set Author.
    *
    * @param	string	$a_author	Author
    */
    public function setAuthor($a_author)
    {
        $this->author = $a_author;
    }

    /**
    * Get Author.
    *
    * @return	string	Author
    */
    public function getAuthor()
    {
        return $this->author;
    }
}
