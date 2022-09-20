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
 * A FeedItem represents an item in a News Feed.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilFeedItem
{
    private string $about = "";
    private string $title = "";
    private string $link = "";
    private string $description = "";
    private string $enclosureurl = "";
    private string $enclosuretype = "";
    private int $enclosurelength = 0;
    private string $date = "";

    public function setAbout(string $a_About): void
    {
        $this->about = $a_About;
    }

    public function getAbout(): string
    {
        return $this->about;
    }

    public function setTitle(string $a_Title): void
    {
        $this->title = $a_Title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setLink(string $a_Link): void
    {
        $this->link = $a_Link;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setDescription(string $a_Description): void
    {
        $this->description = $a_Description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setEnclosureUrl(string $a_enclosureurl): void
    {
        $this->enclosureurl = $a_enclosureurl;
    }

    public function getEnclosureUrl(): string
    {
        return $this->enclosureurl;
    }

    public function setEnclosureType(string $a_enclosuretype): void
    {
        $this->enclosuretype = $a_enclosuretype;
    }

    public function getEnclosureType(): string
    {
        return $this->enclosuretype;
    }

    public function setEnclosureLength(int $a_enclosurelength): void
    {
        $this->enclosurelength = $a_enclosurelength;
    }

    public function getEnclosureLength(): int
    {
        return $this->enclosurelength;
    }

    /**
     * @param string $a_date Date (yyyy-mm-dd hh:mm:ss)
     */
    public function setDate(string $a_date): void
    {
        $this->date = $a_date;
    }

    public function getDate(): string
    {
        return $this->date;
    }
}
