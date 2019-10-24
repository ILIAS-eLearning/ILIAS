<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Icon;

/**
 * This describes the specific behavior of an ILIAS standard icon.
 */
interface Standard extends Icon
{

    // std. ILIAS icons:
    // SELECT distinct title, description from `object_data` where type='typ'
    const GRP  	= 'grp';	//Group object
    const CAT  	= 'cat';	//Category object
    const CRS  	= 'crs';	//Course object
    const MOB  	= 'mob';	//Multimedia object
    const MAIL  = 'mail';	//Mailmodule object
    const SAHS  = 'sahs';	//SCORM/AICC Learning Module
    const ADM   = 'adm';	//Administration Panel object
    const USRF  = 'usrf';	//User Folder object
    const ROLF  = 'rolf';	//Role Folder object
    const OBJF  = 'objf';	//Object-Type Folder object
    const USR  	= 'usr';	//User object
    const ROLT  = 'rolt';	//Role template object
    const LNGF  = 'lngf';	//Language Folder object
    const LNG 	= 'lng';	//Language object
    const ROLE  = 'role';	//Role Object
    const DBK  	= 'dbk';	//Digilib Book
    const GLO  	= 'glo';	//Glossary
    const ROOT  = 'root';	//Root Folder Object
    const LM  	= 'lm';		//Learning module Object
    const NOTF  = 'notf';	//Note Folder Object
    const NOTE  = 'note';	//Note Object
    const FRM  	= 'frm';	//Forum object
    const EXC  	= 'exc';	//Exercise object
    const AUTH  = 'auth';	//Authentication settings
    const FOLD  = 'fold';	//Folder object
    const FILE  = 'file';	//File object
    const TST  	= 'tst';	//Test object
    const QPL  	= 'qpl';	//Question pool object
    const RECF  = 'recf';	//RecoveryFolder object
    const MEP  	= 'mep';	//Media pool object
    const HTLM  = 'htlm';	//HTML LM object
    const SVY  	= 'svy';	//Survey object
    const SPL  	= 'spl';	//Question pool object (Survey)
    const CALS  = 'cals';	//Calendar Settings
    const TRAC  = 'trac';	//UserTracking object
    const ASSF  = 'assf';	//AssessmentFolder object
    const STYS  = 'stys';	//Style Settings
    const CRSG  = 'crsg';	//Course grouping object
    const WEBR  = 'webr';	//Link resource object
    const SEAS  = 'seas';	//Search settings
    const EXTT  = 'extt';	//external tools settings
    const ADVE  = 'adve';	//Advanced editing object
    const PS  	= 'ps';		//Privacy security settings
    const NWSS  = 'nwss';	//News settings
    const FEED  = 'feed';	 //External Feed
    const MCST  = 'mcst';	//Media Cast
    const PDTS  = 'pdts';	//Personal desktop settings
    const RCRS  = 'rcrs';	//Remote Course Object
    const MDS  	= 'mds';	//Meta Data settings
    const CMPS  = 'cmps';	//Component settings / Plugin
    const FACS  = 'facs';	//File Access settings object
    const SVYF  = 'svyf';	//Survey Settings
    const SESS  = 'sess';	//Session object
    const MCTS  = 'mcts';	//Mediacast settings
    const WIKI  = 'wiki';	//Wiki
    const CRSR  = 'crsr';	//Course Reference Object
    const CATR  = 'catr';	//Category Reference Object
    const TAGS  = 'tags';	//Tagging settings
    const CERT  = 'cert';	//Certificate settings
    const LRSS  = 'lrss';	//Learning resources settings
    const ACCS  = 'accs';	//Accessibility settings
    const MOBS  = 'mobs';	//Media Object/Pool settings
    const FRMA  = 'frma';	//Forum administration
    const BOOK  = 'book';	//Booking Manager
    const SKMG  = 'skmg';	//Skill Management
    const BLGA  = 'blga';	//Blog administration
    const PRFA  = 'prfa';	//Portfolio administration
    const CHTR  = 'chtr';	//Chatroom Object
    const CHTA  = 'chta';	//Chatroom Administration Type
    const OTPL  = 'otpl';	//Object Template administration
    const BLOG  = 'blog';	//Blog Object
    const DCL 	= 'dcl';	//Data Collection Object
    const POLL  = 'poll';	//Poll Object
    const HLPS  = 'hlps';	//Help Settings
    const ITGR  = 'itgr';	//Item Group
    const RCAT  = 'rcat';	//Remote Category Object
    const RWIK  = 'rwik';	//Remote Wiki Object
    const RLM  	= 'rlm';	//Remote Learning Module Object
    const RGLO  = 'rglo';	//Remote Glossary Object
    const RFIL  = 'rfil';	//Remote File Object
    const RGRP  = 'rgrp';	//Remote Group Object
    const RTST  = 'rtst';	//Remote Test Object
    const ECSS  = 'ecss';	//ECS Administration
    const TOS  	= 'tos';	//Terms of Service
    const BIBL  = 'bibl';	//Bibliographic Object
    const SYSC  = 'sysc';	//System Check
    const CLD  	= 'cld';	//Cloud Folder
    const REPS  = 'reps';	//Repository Settings
    const CRSS  = 'crss';	//Course Settings
    const GRPS  = 'grps';	//Group Settings
    const WBRS  = 'wbrs';	//WebResource Settings
    const PRTT  = 'prtt';	//Portfolio Template Object
    const ORGU  = 'orgu';	//Organisational Unit
    const WIKS  = 'wiks';	//Wiki Settings
    const EXCS  = 'excs';	//Exercise Settings
    const TAXS  = 'taxs';	//Taxonomy Settings
    const BIBS  = 'bibs';	//BibliographicAdmin
    const AWRA  = 'awra';	//Awareness Tool Administration
    const LOGS  = 'logs';	//Logging Administration
    const PRG  	= 'prg';	//StudyProgramme
    const PRGS  = 'prgs';	//StudyProgrammeAdmin
    const CADM  = 'cadm';	//Contact
    const GRPR  = 'grpr';	//Group Reference Object
    const BDGA  = 'bdga';	//Badge Settings
    const WFE  	= 'wfe';	//WorkflowEngine
    const IASS  = 'iass';	//Individual Assessment
    const COPA  = 'copa';	//Content Page


    /**
     * Is this an outlined Icon?
     */
    public function isOutlined();

    /**
     * Get an icon like this, but marked as outlined.
     *
     * @param bool $is_outlined
     * @return Standard
     */
    public function withIsOutlined(bool $is_outlined);
}
