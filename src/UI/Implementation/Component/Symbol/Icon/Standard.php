<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component as C;

class Standard extends Icon implements C\Symbol\Icon\Standard
{
    private static array $standard_icons = [
        self::GRP,
        self::CAT,
        self::CRS,
        self::MOB,
        self::MAIL,
        self::SAHS,
        self::ADM,
        self::USRF,
        self::ROLF,
        self::USR,
        self::ROLT,
        self::LNGF,
        self::LNG,
        self::ROLE,
        self::GLO,
        self::ROOT,
        self::LM,
        self::FRM,
        self::EXC,
        self::AUTH,
        self::FOLD,
        self::FILE,
        self::TST,
        self::QPL,
        self::RECF,
        self::MEP,
        self::HTLM,
        self::SVY,
        self::SPL,
        self::CALS,
        self::TRAC,
        self::ASSF,
        self::STYS,
        self::WEBR,
        self::SEAS,
        self::EXTT,
        self::ADVE,
        self::PS,
        self::NWSS,
        self::FEED,
        self::MCST,
        self::RCRS,
        self::MDS,
        self::CMPS,
        self::FACS,
        self::SVYF,
        self::SESS,
        self::MCTS,
        self::WIKI,
        self::CRSR,
        self::CATR,
        self::TAGS,
        self::CERT,
        self::LRSS,
        self::ACCS,
        self::MOBS,
        self::FRMA,
        self::BOOK,
        self::SKMG,
        self::BLGA,
        self::PRFA,
        self::CHTR,
        self::CHTA,
        self::OTPL,
        self::BLOG,
        self::DCL,
        self::POLL,
        self::HLPS,
        self::ITGR,
        self::RCAT,
        self::RWIK,
        self::RLM,
        self::RGLO,
        self::RFIL,
        self::RGRP,
        self::RTST,
        self::ECSS,
        self::TOS,
        self::BIBL,
        self::SYSC,
        self::CLD,
        self::REPS,
        self::CRSS,
        self::GRPS,
        self::WBDV,
        self::WBRS,
        self::PRTT,
        self::ORGU,
        self::WIKS,
        self::EXCS,
        self::TAXS,
        self::BIBS,
        self::AWRA,
        self::LOGS,
        self::PRG,
        self::PRGS,
        self::CADM,
        self::GRPR,
        self::BDGA,
        self::WFE,
        self::IASS,
        self::COPA,
        self::CPAD,
        self::BGTK,
        self::MME,
        self::PDFG,
        self::DSHS,
        self::PRSS,
        self::NOTS,
        self::LHTS,
        self::COMS,
        self::LTIS,
        self::CMIS,
        self::TASK,
        self::REP,
        self::PEAC,
        self::PEADL,
        self::PEADT,
        self::PECD,
        self::PECH,
        self::PECL,
        self::PECLP,
        self::PECOM,
        self::PECRS,
        self::PECRT,
        self::PECS,
        self::PEDT,
        self::PEFL,
        self::PEIM,
        self::PELH,
        self::PEMED,
        self::PEMP,
        self::PEPD,
        self::PEPE,
        self::PEPL,
        self::PEPLH,
        self::PEQU,
        self::PERL,
        self::PESC,
        self::PETMP,
        self::PEUSR,
        self::LSO,
        self::LSOS,
        self::ADN,
        self::NOTA,
        self::GCON,
        self::CON
    ];

    public function __construct(string $name, string $label, string $size, bool $is_disabled)
    {
        $this->checkArgIsElement(
            "size",
            $size,
            self::$possible_sizes,
            implode('/', self::$possible_sizes)
        );

        $this->name = $name;
        $this->label = $label;
        $this->size = $size;
        $this->is_disabled = $is_disabled;
    }

    /**
    * get all defined constants
     * @return string[]
    */
    public function getAllStandardHandles(): array
    {
        return self::$standard_icons;
    }
}
