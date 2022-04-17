<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\FileUpload\MimeType;

/**
 * Stores all mediacast relevant settings.
 * @author Roland Küstermann <rkuestermann@mps.de>
 */
class ilMediaCastSettings
{
    private array $supported_suffixes = ["mp4", "mp3", "jpg", "jpeg", "png", "gif", "svg"];
    private array $supported_mime_types = [
        "video/mp4" => "video/mp4",
        "audio/mpeg" => "audio/mpeg",
        "image/jpeg" => "image/jpeg",
        "image/png" => "image/png",
        "image/gif" => "image/gif",
        "image/svg+xml" => "image/svg+xml"
    ];


    private static ?self $instance = null;
    private string $defaultAccess = "users";
    private array $purposeSuffixes = array();
    private array $mimeTypes = array();
    protected ilSetting $storage;
    protected int $video_threshold = 0;

    private function __construct()
    {
        $this->initStorage();
        $this->read();
    }
    
    public static function _getInstance() : self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilMediaCastSettings();
    }

    public function setPurposeSuffixes(array $purpose_filetypes) : void
    {
        $this->purposeSuffixes = $purpose_filetypes;
    }

    public function getPurposeSuffixes() : array
    {
        return $this->purposeSuffixes;
    }

    public function getDefaultAccess() : string
    {
        return $this->defaultAccess;
    }
    
    public function setDefaultAccess(string $value) : void
    {
        $this->defaultAccess = $value === "users" ? "users" : "public";
    }
    
    public function getMimeTypes() : array
    {
        return $this->mimeTypes;
    }
    
    public function setMimeTypes(array $mimeTypes) : void
    {
        $this->mimeTypes = $mimeTypes;
    }

    public function setVideoCompletionThreshold(int $a_val) : void
    {
        $this->video_threshold = $a_val;
    }

    public function getVideoCompletionThreshold() : int
    {
        return $this->video_threshold;
    }

    public function save() : void
    {
        foreach ($this->purposeSuffixes as $purpose => $filetypes) {
            $this->storage->set($purpose . "_types", implode(",", $filetypes));
        }
        $this->storage->set("defaultaccess", $this->defaultAccess);
        $this->storage->set("video_threshold", $this->video_threshold);
        $this->storage->set("mimetypes", implode(",", $this->getMimeTypes()));
    }

    private function read() : void
    {
        foreach ($this->purposeSuffixes as $purpose => $filetypes) {
            if ($this->storage->get($purpose . "_types") != false) {
                $sf = explode(",", $this->storage->get($purpose . "_types"));
                $sf = array_filter($sf, function ($c) {
                    return in_array($c, $this->supported_suffixes);
                });
                $this->purposeSuffixes[$purpose] = $sf;
            }
        }
        $this->setDefaultAccess((string) $this->storage->get("defaultaccess"));
        $this->setVideoCompletionThreshold((int) $this->storage->get("video_threshold"));
        if ($this->storage->get("mimetypes")) {
            $mt = explode(",", $this->storage->get("mimetypes"));
            $mt = array_filter($mt, function ($c) {
                return in_array($c, $this->supported_mime_types);
            });

            $this->setMimeTypes($mt);
        }
    }

    private function initStorage() : void
    {
        $this->storage = new ilSetting('mcst');
        $this->purposeSuffixes = array_flip(ilObjMediaCast::$purposes);
        
        $this->purposeSuffixes["Standard"] = $this->supported_suffixes;
        $this->setDefaultAccess("users");
        $mimeTypes = array_unique(array_values(MimeType::getExt2MimeMap()));
        sort($mimeTypes);
        $this->setMimeTypes($this->supported_mime_types);
    }
}
