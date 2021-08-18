<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * A FeedItem represents an item in a News Feed.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilFeedItem
{
    private $about;
    private $title;
    private $link;
    private $description;

    /**
    * Set About.
    *
    * @param	string	$a_About
    */
    public function setAbout($a_About)
    {
        $this->about = $a_About;
    }

    /**
    * Get About.
    *
    * @return	string
    */
    public function getAbout()
    {
        return $this->about;
    }

    /**
    * Set Title.
    *
    * @param	string	$a_Title
    */
    public function setTitle($a_Title)
    {
        $this->title = $a_Title;
    }

    /**
    * Get Title.
    *
    * @return	string
    */
    public function getTitle()
    {
        return $this->title;
    }

    /**
    * Set Link.
    *
    * @param	string	$a_Link
    */
    public function setLink($a_Link)
    {
        $this->link = $a_Link;
    }

    /**
    * Get Link.
    *
    * @return	string
    */
    public function getLink()
    {
        return $this->link;
    }

    /**
    * Set Description.
    *
    * @param	string	$a_Description
    */
    public function setDescription($a_Description)
    {
        $this->description = $a_Description;
    }

    /**
    * Get Description.
    *
    * @return	string
    */
    public function getDescription()
    {
        return $this->description;
    }

    /**
    * Set Enclosure URL.
    *
    * @param	string	$a_enclosureurl	Enclosure URL
    */
    public function setEnclosureUrl($a_enclosureurl)
    {
        $this->enclosureurl = $a_enclosureurl;
    }

    /**
    * Get Enclosure URL.
    *
    * @return	string	Enclosure URL
    */
    public function getEnclosureUrl()
    {
        return $this->enclosureurl;
    }

    /**
    * Set Enclosure Type.
    *
    * @param	string	$a_enclosuretype	Enclosure Type
    */
    public function setEnclosureType($a_enclosuretype)
    {
        $this->enclosuretype = $a_enclosuretype;
    }

    /**
    * Get Enclosure Type.
    *
    * @return	string	Enclosure Type
    */
    public function getEnclosureType()
    {
        return $this->enclosuretype;
    }

    /**
    * Set Enclosure Length.
    *
    * @param	int	$a_enclosurelength	Enclosure Length
    */
    public function setEnclosureLength($a_enclosurelength)
    {
        $this->enclosurelength = $a_enclosurelength;
    }

    /**
    * Get Enclosure Length.
    *
    * @return	int	Enclosure Length
    */
    public function getEnclosureLength()
    {
        return $this->enclosurelength;
    }

    /**
    * Set Date.
    *
    * @param	string	$a_date	Date (yyyy-mm-dd hh:mm:ss)
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
}
