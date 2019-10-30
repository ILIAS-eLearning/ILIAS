<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Icon;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Standard extends Icon implements C\Icon\Standard
{

    /**
     * @var bool
     */
    protected $is_outlined = false;


    private static $standard_icons = array(
         self::GRP
        ,self::CAT
        ,self::CRS
        ,self::MOB
        ,self::MAIL
        ,self::SAHS
        ,self::ADM
        ,self::USRF
        ,self::ROLF
        ,self::OBJF
        ,self::USR
        ,self::ROLT
        ,self::LNGF
        ,self::LNG
        ,self::ROLE
        ,self::DBK
        ,self::GLO
        ,self::ROOT
        ,self::LM
        ,self::NOTF
        ,self::NOTE
        ,self::FRM
        ,self::EXC
        ,self::AUTH
        ,self::FOLD
        ,self::FILE
        ,self::TST
        ,self::QPL
        ,self::RECF
        ,self::MEP
        ,self::HTLM
        ,self::SVY
        ,self::SPL
        ,self::CALS
        ,self::TRAC
        ,self::ASSF
        ,self::STYS
        ,self::CRSG
        ,self::WEBR
        ,self::SEAS
        ,self::EXTT
        ,self::ADVE
        ,self::PS
        ,self::NWSS
        ,self::FEED
        ,self::MCST
        ,self::PDTS
        ,self::RCRS
        ,self::MDS
        ,self::CMPS
        ,self::FACS
        ,self::SVYF
        ,self::SESS
        ,self::MCTS
        ,self::WIKI
        ,self::CRSR
        ,self::CATR
        ,self::TAGS
        ,self::CERT
        ,self::LRSS
        ,self::ACCS
        ,self::MOBS
        ,self::FRMA
        ,self::BOOK
        ,self::SKMG
        ,self::BLGA
        ,self::PRFA
        ,self::CHTR
        ,self::CHTA
        ,self::OTPL
        ,self::BLOG
        ,self::DCL
        ,self::POLL
        ,self::HLPS
        ,self::ITGR
        ,self::RCAT
        ,self::RWIK
        ,self::RLM
        ,self::RGLO
        ,self::RFIL
        ,self::RGRP
        ,self::RTST
        ,self::ECSS
        ,self::TOS
        ,self::BIBL
        ,self::SYSC
        ,self::CLD
        ,self::REPS
        ,self::CRSS
        ,self::GRPS
        ,self::WBRS
        ,self::PRTT
        ,self::ORGU
        ,self::WIKS
        ,self::EXCS
        ,self::TAXS
        ,self::BIBS
        ,self::AWRA
        ,self::LOGS
        ,self::PRG
        ,self::PRGS
        ,self::CADM
        ,self::GRPR
        ,self::BDGA
        ,self::WFE
        ,self::IASS
        ,self::COPA
    );

    public function __construct($name, $aria_label, $size, $is_disabled)
    {
        $this->checkStringArg("name", $name);
        $this->checkStringArg("string", $aria_label);
        $this->checkArgIsElement(
            "size",
            $size,
            self::$possible_sizes,
            implode("/", self::$possible_sizes)
        );
        $this->checkBoolArg("is_disabled", $is_disabled);
        $this->name = $name;
        $this->aria_label = $aria_label;
        $this->size = $size;
        $this->is_disabled = $is_disabled;
    }

    /**
    * get all defined constants
    */
    public function getAllStandardHandles()
    {
        return self::$standard_icons;
    }

    /**
     * @return bool
     */
    public function isOutlined() : bool
    {
        return $this->is_outlined;
    }

    /**
     * @param bool $is_outlined
     * @return Icon
     */
    public function withIsOutlined(bool $is_outlined) : Icon
    {
        $clone = clone $this;
        $clone->is_outlined = $is_outlined;
        return $clone;
    }
}
