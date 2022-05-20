<?php declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Symbol\Icon;

/**
 * This describes the specific behavior of an ILIAS standard icon.
 */
interface Standard extends Icon
{
    // std. ILIAS icons:
    // SELECT distinct title, description from `object_data` where type='typ'
    public const GRP = 'grp';	//Group object
    public const CAT = 'cat';	//Category object
    public const CRS = 'crs';	//Course object
    public const MOB = 'mob';	//Multimedia object
    public const MAIL = 'mail';	//Mailmodule object
    public const SAHS = 'sahs';	//SCORM/AICC Learning Module
    public const ADM = 'adm';	//Administration Panel object
    public const USRF = 'usrf';	//User Folder object
    public const ROLF = 'rolf';	//Role Folder object
    public const OBJF = 'objf';	//Object-Type Folder object
    public const USR = 'usr';	//User object
    public const ROLT = 'rolt';	//Role template object
    public const LNGF = 'lngf';	//Language Folder object
    public const LNG = 'lng';	//Language object
    public const ROLE = 'role';	//Role Object
    public const DBK = 'dbk';	//Digilib Book
    public const GLO = 'glo';	//Glossary
    public const ROOT = 'root';	//Root Folder Object
    public const LM = 'lm';		//Learning module Object
    public const FRM = 'frm';	//Forum object
    public const EXC = 'exc';	//Exercise object
    public const AUTH = 'auth';	//Authentication settings
    public const FOLD = 'fold';	//Folder object
    public const FILE = 'file';	//File object
    public const TST = 'tst';	//Test object
    public const QPL = 'qpl';	//Question pool object
    public const RECF = 'recf';	//RecoveryFolder object
    public const MEP = 'mep';	//Media pool object
    public const HTLM = 'htlm';	//HTML LM object
    public const SVY = 'svy';	//Survey object
    public const SPL = 'spl';	//Question pool object (Survey)
    public const CALS = 'cals';	//Calendar Settings
    public const TRAC = 'trac';	//UserTracking object
    public const ASSF = 'assf';	//AssessmentFolder object
    public const STYS = 'stys';	//Style Settings
    public const CRSG = 'crsg';	//Course grouping object
    public const WEBR = 'webr';	//Link resource object
    public const SEAS = 'seas';	//Search settings
    public const EXTT = 'extt';	//external tools settings
    public const ADVE = 'adve';	//Advanced editing object
    public const PS = 'ps';		//Privacy security settings
    public const NWSS = 'nwss';	//News settings
    public const FEED = 'feed';	 //External Feed
    public const MCST = 'mcst';	//Media Cast
    public const RCRS = 'rcrs';	//Remote Course Object
    public const MDS = 'mds';	//Meta Data settings
    public const CMPS = 'cmps';	//Component settings / Plugin
    public const FACS = 'facs';	//File Access settings object
    public const SVYF = 'svyf';	//Survey Settings
    public const SESS = 'sess';	//Session object
    public const MCTS = 'mcts';	//Mediacast settings
    public const WIKI = 'wiki';	//Wiki
    public const CRSR = 'crsr';	//Course Reference Object
    public const CATR = 'catr';	//Category Reference Object
    public const TAGS = 'tags';	//Tagging settings
    public const CERT = 'cert';	//Certificate settings
    public const LRSS = 'lrss';	//Learning resources settings
    public const ACCS = 'accs';	//Accessibility settings
    public const MOBS = 'mobs';	//Media Object/Pool settings
    public const FRMA = 'frma';	//Forum administration
    public const BOOK = 'book';	//Booking Manager
    public const SKMG = 'skmg';	//Skill Management
    public const BLGA = 'blga';	//Blog administration
    public const PRFA = 'prfa';	//Portfolio administration
    public const CHTR = 'chtr';	//Chatroom Object
    public const CHTA = 'chta';	//Chatroom Administration Type
    public const OTPL = 'otpl';	//Object Template administration
    public const BLOG = 'blog';	//Blog Object
    public const DCL = 'dcl';	//Data Collection Object
    public const POLL = 'poll';	//Poll Object
    public const HLPS = 'hlps';	//Help Settings
    public const ITGR = 'itgr';	//Item Group
    public const RCAT = 'rcat';	//Remote Category Object
    public const RWIK = 'rwik';	//Remote Wiki Object
    public const RLM = 'rlm';	//Remote Learning Module Object
    public const RGLO = 'rglo';	//Remote Glossary Object
    public const RFIL = 'rfil';	//Remote File Object
    public const RGRP = 'rgrp';	//Remote Group Object
    public const RTST = 'rtst';	//Remote Test Object
    public const ECSS = 'ecss';	//ECS Administration
    public const TOS = 'tos';	//Terms of Service
    public const BIBL = 'bibl';	//Bibliographic Object
    public const SYSC = 'sysc';	//System Check
    public const CLD = 'cld';	//Cloud Folder
    public const REPS = 'reps';	//Repository Settings
    public const CRSS = 'crss';	//Course Settings
    public const GRPS = 'grps';	//Group Settings
    public const WBDV = 'wbdv';	//WebDAV Settings
    public const WBRS = 'wbrs';	//WebResource Settings
    public const PRTT = 'prtt';	//Portfolio Template Object
    public const ORGU = 'orgu';	//Organisational Unit
    public const WIKS = 'wiks';	//Wiki Settings
    public const EXCS = 'excs';	//Exercise Settings
    public const TAXS = 'taxs';	//Taxonomy Settings
    public const BIBS = 'bibs';	//BibliographicAdmin
    public const AWRA = 'awra';	//Awareness Tool Administration
    public const LOGS = 'logs';	//Logging Administration
    public const PRG = 'prg';	//StudyProgramme
    public const PRGS = 'prgs';	//StudyProgrammeAdmin
    public const CADM = 'cadm';	//Contact
    public const GRPR = 'grpr';	//Group Reference Object
    public const BDGA = 'bdga';	//Badge Settings
    public const WFE = 'wfe';	//WorkflowEngine
    public const IASS = 'iass';	//Individual Assessment
    public const COPA = 'copa';	//Content Page
    public const CPAD = 'cpad';	//Content Page Admnistration
    public const BGTK = 'bgtk';	//Background Task
    public const MME = 'mme';	//Main Menu
    public const PDFG = 'pdfg';	//PDF Generation
    public const DSHS = 'dshs';	//Dashboard
    public const PRSS = 'prss';	//Personal Ressources
    public const NOTS = 'nots';	//Notes
    public const LHTS = 'lhts';	//Learning History
    public const COMS = 'coms';	//Comments
    public const LTIS = 'ltis';	//LTI
    public const CMIS = 'cmis';	//xAPI/cmi5
    public const REP = 'rep';	//Repository
    public const TASK = 'task';   //Task
    public const PEAC = 'peac';    //Page Editor Accordion
    public const PEADL = 'peadl';   //Page Editor Advanced List
    public const PEADT = 'peadt';   //Page Editor Advanced Tables
    public const PECD = 'pecd';     //Page Editor Code
    public const PECH = 'pech';     //Page Editor Consultation Hour
    public const PECL = 'pecl';     //Page Editor Column Layout
    public const PECLP = 'peclp';   //Page Editor Clipboard
    public const PECOM = 'pecom';   //Page Editor Competence
    public const PECRS = 'pecrs';   //Page Editor Course
    public const PECRT = 'pecrt';   //Page Editor Certificate
    public const PECS = 'pecs';     //Page Editor Content Snippet
    public const PEDT = 'pedt';     //Page Editor Data Table
    public const PEFL = 'pefl';     //Page Editor File List
    public const PEIM = 'peim';     //Page Editor Interactive Media
    public const PELH = 'pelh';     //Page Editor Learning History
    public const PEMED = 'pemed';   //Page Editor Media
    public const PEMP = 'pemp';     //Page Editor Map
    public const PEPD = 'pepd';     //Page Editor Personal Data
    public const PEPE = 'pepe';     //Page Editor Plugin Element
    public const PEPL = 'pepl';     //Page Editor Page List
    public const PEPLH = 'peplh';   //Page Editor Placeholder
    public const PEQU = 'pequ';     //Page Editor Question
    public const PERL = 'perl';     //Page Editor Ressource List
    public const PESC = 'pesc';     //Page Editor Section
    public const PETMP = 'petmp';   //Page Editor Template
    public const PEUSR = 'peusr';   //Page Editor User
    public const LSO = 'lso';       //Learning Sequence
    public const LSOS = 'lsos';     //Learning Sequence Admin
    public const ADN = 'adn';       //Administrative Notification

    /**
     * Is this an outlined Icon?
     */
    public function isOutlined() : bool;

    /**
     * Get an icon like this, but marked as outlined.
     */
    public function withIsOutlined(bool $is_outlined) : Standard;
}
