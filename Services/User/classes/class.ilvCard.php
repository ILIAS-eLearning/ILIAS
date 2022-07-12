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

// Address values for the ADR type
const ADR_TYPE_NONE = 0;
const ADR_TYPE_DOM = 1;
const ADR_TYPE_INTL = 2;
const ADR_TYPE_POSTAL = 4;
const ADR_TYPE_PARCEL = 8;
const ADR_TYPE_HOME = 16;
const ADR_TYPE_WORK = 32;
const ADR_TYPE_PREF = 64;

// Communication values for the TEL type
const TEL_TYPE_NONE = 0;
const TEL_TYPE_HOME = 1;
const TEL_TYPE_MSG = 2;
const TEL_TYPE_WORK = 4;
const TEL_TYPE_PREF = 8;
const TEL_TYPE_VOICE = 16;
const TEL_TYPE_FAX = 32;
const TEL_TYPE_CELL = 64;
const TEL_TYPE_VIDEO = 128;
const TEL_TYPE_PAGER = 256;
const TEL_TYPE_BBS = 512;
const TEL_TYPE_MODEM = 1024;
const TEL_TYPE_CAR = 2048;
const TEL_TYPE_ISDN = 4096;
const TEL_TYPE_PCS = 8192;

// Communication values for the EMAIL type
const EMAIL_TYPE_NONE = 0;
const EMAIL_TYPE_INTERNET = 1;
const EMAIL_TYPE_x400 = 2;
const EMAIL_TYPE_PREF = 4;

/**
 * RFC 2426 vCard MIME Directory Profile 3.0 class
 * @author Helmut Schottmüller <hschottm@tzi.de>
 */
class ilvCard
{
    /**
     * An array containing the vCard types
     * @var array<string,mixed>
     */
    public array $types;

    // The filename of the vCard used when saving the vCard
    public string $filename;

    public function __construct(string $version = "3.0")
    {
        $this->types = array(
            "FN" => "",
            "N" => "",
            "NICKNAME" => "",
            "PHOTO" => array(),
            "BDAY" => "",
            "ADR" => array(),
            "LABEL" => array(),
            "TEL" => array(),
            "EMAIL" => array(),
            "MAILER" => "",
            "TZ" => "",
            "GEO" => "",
            "TITLE" => "",
            "ROLE" => "",
            "LOGO" => array(),
            "AGENT" => "",
            "ORG" => "",
            "CATEGORIES" => "",
            "NOTE" => "",
            "PRODID" => "",
            "REV" => "",
            "SORT-STRING" => "",
            "SOUND" => array(),
            "UID" => "",
            "URL" => "",
            "CLASS" => "",
            "KEY" => array()
        );
        $this->types["VERSION"] = $version;
    }

    /**
     * Encode data with "b" type encoding according to RFC 2045
     */
    public function encode(string $string) : string
    {
        return $this->escape(quoted_printable_encode($string));
    }

    /**
     * Fold a string according to RFC 2425
     */
    public function fold(string $string = "") : string
    {
        $folded_string = "";
        preg_match_all("/(.{1,74})/", $string, $matches);
        for ($i = 0, $iMax = count($matches[1]); $i < $iMax; $i++) {
            if ($i < (count($matches[1]) - 1)) {
                $matches[1][$i] .= "\n";
            }
            if ($i > 0) {
                $matches[1][$i] = " " . $matches[1][$i];
            }
            $folded_string .= $matches[1][$i];
        }
        return $folded_string;
    }

    /**
     * Escapes a string according to RFC 2426
     */
    public function escape(string $string) : string
    {
        $string = preg_replace("/(?<!\\\\)(\\\\)([^;,n\\\\])/", "\${1}\${1}\${2}", $string);
        $string = preg_replace("/(?<!\\\\);/", "\\;", $string);
        $string = preg_replace("/(?<!\\\\),/", "\\,", $string);
        $string = preg_replace("/\n/", "\\n", $string);
        return $string;
    }

    /**
     * Splits a variable into an array using a separator and escapes every value
     * @return array<string,string>
     */
    public function explodeVar(string $variable, string $separator = ",") : array
    {
        $exploded = explode($separator, $variable);
        foreach ($exploded as $index => $var) {
            $exploded[$index] = $this->escape($var);
        }
        return $exploded;
    }

    /**
     * Builds a vCard string out of the attributes of this object
     */
    public function buildVCard() : string
    {
        $fn = $n = $nickname = $photo = $bday = $adr = $label = $tel = $email = $mailer =
        $tz = $geo = $title = $role = $logo = $agent = $org = $categories = $note = $prodid =
        $rev = $sortstring = $sound = $uid = $url = $class = $key = 0;

        $vcard = "BEGIN:VCARD\n";
        $vcard .= "VERSION:" . $this->types["VERSION"] . "\n";
        foreach ($this->types as $type => $var) {
            ilLoggerFactory::getLogger('user')->debug(print_r($this->types, true));

            switch ($type) {
                case "FN":
                    if (strcmp($this->types["FN"], "") != 0) {
                        $fn = $this->fold("FN:" . $this->types["FN"]) . "\n";
                    } else {
                        $fn = "";
                    }
                    break;
                case "N":
                    if (strcmp($this->types["N"], "") != 0) {
                        $n = $this->fold("N:" . $this->types["N"]) . "\n";
                    } else {
                        $n = "";
                    }
                    break;
                case "NICKNAME":
                    if (strcmp($this->types["NICKNAME"], "") != 0) {
                        $nickname = $this->fold("NICKNAME:" . $this->types["NICKNAME"]) . "\n";
                    } else {
                        $nickname = "";
                    }
                    break;
                case "PHOTO":
                    $photo = "";
                    if (isset($this->types["PHOTO"])) {
                        if (strcmp(($this->types["PHOTO"]["VALUE"] ?? ""), "") != 0) {
                            $photo = $this->fold("PHOTO;VALUE=uri:" . $this->types["PHOTO"]["VALUE"]) . "\n";
                        } elseif (strcmp(($this->types["PHOTO"]["ENCODING"] ?? ""), "") != 0) {
                            $photo = "PHOTO;ENCODING=" . $this->types["PHOTO"]["ENCODING"];
                            if (strcmp($this->types["PHOTO"]["TYPE"], "") != 0) {
                                $photo .= ";TYPE=" . $this->types["PHOTO"]["TYPE"];
                            }
                            $photo .= ":" . $this->types["PHOTO"]["PHOTO"];
                            $photo = $this->fold($photo) . "\n";
                        }
                    }
                    break;
                case "BDAY":
                    if (strcmp($this->types["BDAY"], "") != 0) {
                        $bday = $this->fold("BDAY:" . $this->types["BDAY"]) . "\n";
                    } else {
                        $bday = "";
                    }
                    break;
                case "ADR":
                    if (count($this->types["ADR"])) {
                        $addresses = "";
                        foreach ($this->types["ADR"] as $key => $address) {
                            $test = implode('', $address);
                            if (strcmp($test, "") != 0) {
                                $adr = "ADR";
                                $adr_types = array();
                                if ($address["TYPE"] > 0) {
                                    if (($address["TYPE"] & ADR_TYPE_DOM) > 0) {
                                        $adr_types[] = "dom";
                                    }
                                    if (($address["TYPE"] & ADR_TYPE_INTL) > 0) {
                                        $adr_types[] = "intl";
                                    }
                                    if (($address["TYPE"] & ADR_TYPE_POSTAL) > 0) {
                                        $adr_types[] = "postal";
                                    }
                                    if (($address["TYPE"] & ADR_TYPE_PARCEL) > 0) {
                                        $adr_types[] = "parcel";
                                    }
                                    if (($address["TYPE"] & ADR_TYPE_HOME) > 0) {
                                        $adr_types[] = "home";
                                    }
                                    if (($address["TYPE"] & ADR_TYPE_WORK) > 0) {
                                        $adr_types[] = "work";
                                    }
                                    if (($address["TYPE"] & ADR_TYPE_PREF) > 0) {
                                        $adr_types[] = "pref";
                                    }
                                    $adr .= ";TYPE=" . implode(",", $adr_types);
                                }
                                $adr .= ":" . $address["POBOX"] . ";" . $address["EXTENDED_ADDRESS"] .
                                    ";" . $address["STREET_ADDRESS"] . ";" . $address["LOCALITY"] .
                                    ";" . $address["REGION"] . ";" . $address["POSTAL_CODE"] .
                                    ";" . $address["COUNTRY"];
                                $adr = $this->fold($adr) . "\n";
                                $addresses .= $adr;
                            }
                        }
                        $adr = $addresses;
                    } else {
                        $adr = "";
                    }
                    break;
                case "LABEL":
                    $label = "";
                    if (isset($this->types["LABEL"])) {
                        if (strcmp(($this->types["LABEL"]["LABEL"] ?? ""), "") != 0) {
                            $label = "LABEL";
                            $adr_types = array();
                            if ($this->types["LABEL"]["TYPE"] > 0) {
                                if (($this->types["LABEL"]["TYPE"] & ADR_TYPE_DOM) > 0) {
                                    $adr_types[] = "dom";
                                }
                                if (($this->types["LABEL"]["TYPE"] & ADR_TYPE_INTL) > 0) {
                                    $adr_types[] = "intl";
                                }
                                if (($this->types["LABEL"]["TYPE"] & ADR_TYPE_POSTAL) > 0) {
                                    $adr_types[] = "postal";
                                }
                                if (($this->types["LABEL"]["TYPE"] & ADR_TYPE_PARCEL) > 0) {
                                    $adr_types[] = "parcel";
                                }
                                if (($this->types["LABEL"]["TYPE"] & ADR_TYPE_HOME) > 0) {
                                    $adr_types[] = "home";
                                }
                                if (($this->types["LABEL"]["TYPE"] & ADR_TYPE_WORK) > 0) {
                                    $adr_types[] = "work";
                                }
                                if (($this->types["LABEL"]["TYPE"] & ADR_TYPE_PREF) > 0) {
                                    $adr_types[] = "pref";
                                }
                                $label .= ";TYPE=" . implode(",", $adr_types);
                            }
                            $label .= ":" . $this->types["LABEL"]["LABEL"];
                            $label = $this->fold($label) . "\n";
                        }
                    }
                    break;
                case "TEL":
                    if (count($this->types["TEL"])) {
                        $phonenumbers = "";
                        foreach ($this->types["TEL"] as $key => $phone) {
                            if (strcmp($phone["TEL"], "") != 0) {
                                $tel = "TEL";
                                $tel_types = array();
                                if ($phone["TYPE"] > 0) {
                                    if (($phone["TYPE"] & TEL_TYPE_HOME) > 0) {
                                        $tel_types[] = "home";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_MSG) > 0) {
                                        $tel_types[] = "msg";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_WORK) > 0) {
                                        $tel_types[] = "work";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_PREF) > 0) {
                                        $tel_types[] = "pref";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_VOICE) > 0) {
                                        $tel_types[] = "voice";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_FAX) > 0) {
                                        $tel_types[] = "fax";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_CELL) > 0) {
                                        $tel_types[] = "cell";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_VIDEO) > 0) {
                                        $tel_types[] = "video";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_PAGER) > 0) {
                                        $tel_types[] = "pager";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_BBS) > 0) {
                                        $tel_types[] = "bbs";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_MODEM) > 0) {
                                        $tel_types[] = "modem";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_CAR) > 0) {
                                        $tel_types[] = "car";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_ISDN) > 0) {
                                        $tel_types[] = "isdn";
                                    }
                                    if (($phone["TYPE"] & TEL_TYPE_PCS) > 0) {
                                        $tel_types[] = "pcs";
                                    }
                                    $tel .= ";TYPE=" . implode(",", $tel_types);
                                }
                                $tel .= ":" . $phone["TEL"];
                                $tel = $this->fold($tel) . "\n";
                                $phonenumbers .= $tel;
                            }
                        }
                        $tel = $phonenumbers;
                    } else {
                        $tel = "";
                    }
                    break;
                case "EMAIL":
                    if (count($this->types["EMAIL"])) {
                        $emails = "";
                        foreach ($this->types["EMAIL"] as $key => $mail) {
                            if (strcmp($mail["EMAIL"], "") != 0) {
                                $email = "EMAIL";
                                $adr_types = array();
                                if ($mail["TYPE"] > 0) {
                                    if (($mail["TYPE"] & EMAIL_TYPE_INTERNET) > 0) {
                                        $adr_types[] = "internet";
                                    }
                                    if (($mail["TYPE"] & EMAIL_TYPE_x400) > 0) {
                                        $adr_types[] = "x400";
                                    }
                                    if (($mail["TYPE"] & EMAIL_TYPE_PREF) > 0) {
                                        $adr_types[] = "pref";
                                    }
                                    $email .= ";TYPE=" . implode(",", $adr_types);
                                }
                                $email .= ":" . $mail["EMAIL"];
                                $email = $this->fold($email) . "\n";
                                $emails .= $email;
                            }
                        }
                        $email = $emails;
                    } else {
                        $email = "";
                    }
                    break;
                case "MAILER":
                    if (strcmp(($this->types["MAILER"] ?? ""), "") != 0) {
                        $mailer = $this->fold("MAILER:" . $this->types["MAILER"]) . "\n";
                    } else {
                        $mailer = "";
                    }
                    break;
                case "TZ":
                    if (strcmp(($this->types["TZ"] ?? ""), "") != 0) {
                        $tz = $this->fold("TZ:" . $this->types["TZ"]) . "\n";
                    } else {
                        $tz = "";
                    }
                    break;
                case "GEO":
                    if (isset($this->types["GEO"]) and
                        (strcmp(($this->types["GEO"]["LAT"] ?? ""), "") != 0) and
                        (strcmp(($this->types["GEO"]["LON"] ?? ""), "") != 0)) {
                        $geo = $this->fold(
                            "GEO:" . $this->types["GEO"]["LAT"] . ";" . $this->types["GEO"]["LON"]
                        ) . "\n";
                    } else {
                        $geo = "";
                    }
                    break;
                case "TITLE":
                    if (strcmp(($this->types["TITLE"] ?? ""), "") != 0) {
                        $title = $this->fold("TITLE:" . $this->types["TITLE"]) . "\n";
                    } else {
                        $title = "";
                    }
                    break;
                case "ROLE":
                    if (strcmp(($this->types["ROLE"] ?? ""), "") != 0) {
                        $role = $this->fold("ROLE:" . $this->types["ROLE"]) . "\n";
                    } else {
                        $role = "";
                    }
                    break;
                case "LOGO":
                    $logo = "";
                    if (isset($this->types["LOGO"])) {
                        if (strcmp(($this->types["LOGO"]["VALUE"] ?? ""), "") != 0) {
                            $logo = $this->fold("LOGO;VALUE=uri:" . $this->types["LOGO"]["VALUE"]) . "\n";
                        } elseif (strcmp(($this->types["LOGO"]["ENCODING"] ?? ""), "") != 0) {
                            $logo = "LOGO;ENCODING=" . $this->types["LOGO"]["ENCODING"];
                            if (strcmp($this->types["LOGO"]["TYPE"], "") != 0) {
                                $logo .= ";TYPE=" . $this->types["LOGO"]["TYPE"];
                            }
                            $logo .= ":" . $this->types["LOGO"]["LOGO"];
                            $logo = $this->fold($logo) . "\n";
                        }
                    }
                    break;
                case "AGENT":
                    if (strcmp(($this->types["AGENT"] ?? ""), "") != 0) {
                        $agent = $this->fold("AGENT:" . $this->types["AGENT"]) . "\n";
                    } else {
                        $agent = "";
                    }
                    break;
                case "ORG":
                    if (strcmp(($this->types["ORG"] ?? ""), "") != 0) {
                        $org = $this->fold("ORG:" . $this->types["ORG"]) . "\n";
                    } else {
                        $org = "";
                    }
                    break;
                case "CATEGORIES":
                    if (strcmp(($this->types["CATEGORIES"] ?? ""), "") != 0) {
                        $categories = $this->fold("CATEGORIES:" . $this->types["CATEGORIES"]) . "\n";
                    } else {
                        $categories = "";
                    }
                    break;
                case "NOTE":
                    if (strcmp(($this->types["NOTE"] ?? ""), "") != 0) {
                        $note = $this->fold("NOTE:" . $this->types["NOTE"]) . "\n";
                    } else {
                        $note = "";
                    }
                    break;
                case "PRODID":
                    if (strcmp(($this->types["PRODID"] ?? ""), "") != 0) {
                        $prodid = $this->fold("PRODID:" . $this->types["PRODID"]) . "\n";
                    } else {
                        $prodid = "";
                    }
                    break;
                case "REV":
                    if (strcmp(($this->types["REV"] ?? ""), "") != 0) {
                        $rev = $this->fold("REV:" . $this->types["REV"]) . "\n";
                    } else {
                        $rev = "";
                    }
                    break;
                case "SORT-STRING":
                    if (strcmp(($this->types["SORT-STRING"] ?? ""), "") != 0) {
                        $sortstring = $this->fold("SORT-STRING:" . $this->types["SORT-STRING"]) . "\n";
                    } else {
                        $sortstring = "";
                    }
                    break;
                case "SOUND":
                    $sound = "";
                    if (isset($this->types["SOUND"])) {
                        if (strcmp(($this->types["SOUND"]["VALUE"] ?? ""), "") != 0) {
                            $sound = $this->fold("SOUND;VALUE=uri:" . $this->types["SOUND"]["VALUE"]) . "\n";
                        } elseif (strcmp(($this->types["SOUND"]["ENCODING"] ?? ""), "") != 0) {
                            $sound = "SOUND;ENCODING=" . $this->types["SOUND"]["ENCODING"];
                            if (strcmp($this->types["SOUND"]["TYPE"], "") != 0) {
                                $sound .= ";TYPE=" . $this->types["SOUND"]["TYPE"];
                            }
                            $sound .= ":" . $this->types["SOUND"]["SOUND"];
                            $sound = $this->fold($sound) . "\n";
                        }
                    }
                    break;
                case "UID":
                    $uid = "";
                    if (isset($this->types["UID"])) {
                        if (strcmp(($this->types["UID"]["UID"] ?? ""), "") != 0) {
                            $uid = "UID";
                            if (strcmp($this->types["UID"]["TYPE"], "") != 0) {
                                $uid .= ";TYPE=" . $this->types["UID"]["TYPE"];
                            }
                            $uid .= ":" . $this->types["UID"]["UID"];
                            $uid = $this->fold($uid) . "\n";
                        }
                    }
                    break;
                case "URL":
                    if (strcmp(($this->types["URL"] ?? ""), "") != 0) {
                        $url = $this->fold("URL:" . $this->types["URL"]) . "\n";
                    } else {
                        $url = "";
                    }
                    break;
                case "KEY":
                    $key = "";
                    if (isset($this->types["KEY"])) {
                        if (strcmp(($this->types["KEY"]["KEY"] ?? ""), "") != 0) {
                            $key = "KEY";
                            if (strcmp($this->types["KEY"]["TYPE"], "") != 0) {
                                $key .= ";TYPE=" . $this->types["KEY"]["TYPE"];
                            }
                            if (strcmp($this->types["KEY"]["ENCODING"], "") != 0) {
                                $key .= ";ENCODING=" . $this->types["KEY"]["ENCODING"];
                            }
                            $key .= ":" . $this->types["KEY"]["KEY"];
                            $key = $this->fold($key) . "\n";
                        }
                    }
                    break;
                case "CLASS":
                    if (strcmp(($this->types["CLASS"] ?? ""), "") != 0) {
                        $class = $this->fold("CLASS:" . $this->types["CLASS"]) . "\n";
                    } else {
                        $class = "";
                    }
                    break;
            }
        }
        $vcard .= $fn . $n . $nickname . $photo . $bday . $adr . $label . $tel . $email . $mailer .
            $tz . $geo . $title . $role . $logo . $agent . $org . $categories . $note . $prodid .
            $rev . $sortstring . $sound . $uid . $url . $class . $key;
        $vcard .= "END:vCard\n";
        return $vcard;
    }

    /**
     * Creates a quoted printable encoded string according to RFC 2045
     */
    public function quoted_printable_encode(string $input, int $line_max = 76) : string
    {
        $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
        $lines = preg_split("/(\r\n|\r|\n)/", $input);
        $eol = "\r\n";
        $linebreak = "=0D=0A";
        $escape = "=";
        $output = "";

        for ($j = 0, $jMax = count($lines); $j < $jMax; $j++) {
            $line = $lines[$j];
            $linlen = strlen($line);
            $newline = "";
            for ($i = 0; $i < $linlen; $i++) {
                $c = substr($line, $i, 1);
                $dec = ord($c);
                if (($dec == 32) && ($i == ($linlen - 1))) { // convert space at eol only
                    $c = "=20";
                } elseif (($dec == 61) || ($dec < 32) || ($dec > 126)) { // always encode "\t", which is *not* required
                    $h2 = floor($dec / 16);
                    $h1 = floor($dec % 16);
                    $c = $escape . $hex[(string) $h2] . $hex[(string) $h1];
                }
                if ((strlen($newline) + strlen($c)) >= $line_max) { // CRLF is not counted
                    $output .= $newline . $escape . $eol; // soft line break; " =\r\n" is okay
                    $newline = "    ";
                }
                $newline .= $c;
            } // end of for
            $output .= $newline;
            if ($j < count($lines) - 1) {
                $output .= $linebreak;
            }
        }
        return trim($output);
    }

    // Identification Types
    // These types are used in the vCard profile to capture information
    // associated with the identification and naming of the person or
    // resource associated with the vCard.

    /**
     * Sets the value for the vCard FN type.
     * Sets the value for the vCard FN type to specify
     * the formatted text corresponding to the name
     * of the object the vCard represents.
     * Type example:
     * FN:Mr. John Q. Public\, Esq.
     */
    public function setFormattedName(string $formatted_name) : void
    {
        $this->types["FN"] = $this->escape($formatted_name);
    }

    /**
     * Sets the value for the vCard N type.
     * Sets the value for the vCard N type to specify
     * the components of the name of the object the vCard represents.
     * The N type MUST be present in the vCard object.
     * Type example:
     * N:Public;John;Quinlan;Mr.;Esq.
     * N:Stevenson;John;Philip,Paul;Dr.;Jr.,M.D.,A.C.P.
     * Type special note: The structured type value corresponds, in
     * sequence, to the Family Name, Given Name, Additional Names, Honorific
     * Prefixes, and Honorific Suffixes. The text components are separated
     * by the SEMI-COLON character (ASCII decimal 59). Individual text
     * components can include multiple text values (e.g., multiple
     * Additional Names) separated by the COMMA character (ASCII decimal
     * 44). This type is based on the semantics of the X.520 individual name
     * attributes. The property MUST be present in the vCard object.
     * @param string $family_name        The family name
     * @param string $given_name         The given name
     * @param string $additional_names   Additional names
     * @param string $honorific_prefixes Honorific prefixes
     * @param string $honorific_suffixes Honorific suffixes
     */
    public function setName(
        string $family_name,
        string $given_name = "",
        string $additional_names = "",
        string $honorific_prefixes = "",
        string $honorific_suffixes = ""
    ) : void {
        $familynames = $this->explodeVar($family_name);
        $givennames = $this->explodeVar($given_name);
        $addnames = $this->explodeVar($additional_names);
        $prefixes = $this->explodeVar($honorific_prefixes);
        $suffixes = $this->explodeVar($honorific_suffixes);

        $this->types["N"] =
            implode(",", $familynames) .
            ";" .
            implode(",", $givennames) .
            ";" .
            implode(",", $addnames) .
            ";" .
            implode(",", $prefixes) .
            ";" .
            implode(",", $suffixes);

        $this->filename = $given_name . "_" . $family_name . ".vcf";
        if (strcmp($this->types["FN"], "") === 0) {
            $fn = trim("$honorific_prefixes $given_name $additional_names $family_name $honorific_suffixes");
            $fn = preg_replace("/\s{2,10}/", " ", $fn);
            $this->setFormattedName($fn);
        }
    }

    /**
     * Sets the value for the vCard NICKNAME type.
     * Sets the value for the vCard NICKNAME type to specify
     * the text corresponding to the nickname of the object
     * the vCard represents.
     * Type example:
     * NICKNAME:Robbie
     * NICKNAME:Jim,Jimmie
     * Type special note: The nickname is the descriptive name given instead
     * of or in addition to the one belonging to a person, place, or thing.
     * It can also be used to specify a familiar form of a proper name
     * specified by the FN or N types.
     */
    public function setNickname(string $nickname) : void
    {
        $nicknames = $this->explodeVar($nickname);
        $this->types["NICKNAME"] = implode(",", $nicknames);
    }

    /**
     * Sets the value for the vCard PHOTO type.
     * Sets the value for the vCard PHOTO type to specify
     * an image or photograph information that annotates
     * some aspect of the object the vCard represents.
     * Type example:
     * PHOTO;VALUE=uri:http://www.abc.com/pub/photos
     *   /jqpublic.gif
     * PHOTO;ENCODING=b;TYPE=JPEG:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcN
     *   AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
     *   ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
     * <...remainder of "B" encoded binary data...>
     * Type encoding: The encoding MUST be reset to "b" using the ENCODING
     * parameter in order to specify inline, encoded binary data. If the
     * value is referenced by a URI value, then the default encoding of 8bit
     * is used and no explicit ENCODING parameter is needed.
     * Type value: A single value. The default is binary value. It can also
     * be reset to uri value. The uri value can be used to specify a value
     * outside of this MIME entity.
     * Type special notes: The type can include the type parameter "TYPE" to
     * specify the graphic image format type. The TYPE parameter values MUST
     * be one of the IANA registered image formats or a non-standard image
     * format.
     * @param string $photo A binary string containing the photo or an uri
     * @param string $type  The IANA type of the image format
     */
    public function setPhoto(
        string $photo,
        string $type = ""
    ) : void {
        $value = "";
        $encoding = "";
        if (preg_match("/^http/", $photo)) {
            $value = $this->encode($photo);
        } else {
            $encoding = "b";
            $photo = base64_encode($photo);
        }
        $this->types["PHOTO"] = array(
            "VALUE" => $value,
            "TYPE" => $type,
            "ENCODING" => $encoding,
            "PHOTO" => $photo
        );
    }

    /**
     * Sets the value for the vCard BDAY type.
     * Sets the value for the vCard BDAY type to specify
     * the birth date of the object the vCard represents.
     * Type example:
     * BDAY:1996-04-15
     * BDAY:1953-10-15T23:10:00Z
     * BDAY:1987-09-27T08:30:00-06:00
     * Type value: The default is a single date value.
     * It can also be reset to a single date-time value.
     * @param int $year  The year of the birthday
     * @param int $month The month of the birthday
     * @param int $day   The day of the birthday
     */
    public function setBirthday(int $year, int $month, int $day) : void
    {
        if (($year < 1) or ($day < 1) or ($month < 1)) {
            $this->types["BDAY"] = "";
        } else {
            $this->types["BDAY"] = sprintf("%04d-%02d-%02d", $year, $month, $day);
        }
    }

    // Delivery Addressing Types
    // These types are concerned with information related to the delivery
    // addressing or label for the vCard object.

    /**
     * Sets the value for the vCard ADR type.
     * Sets the value for the vCard ADR type to specify
     * the components of the delivery address for the vCard object.
     * Type example:
     * ADR;TYPE=dom,home,postal,parcel:;;123 Main
     *   Street;Any Town;CA;91921-1234
     * Type special notes: The structured type value consists of a sequence
     * of address components. The component values MUST be specified in
     * their corresponding position. The structured type value corresponds,
     * in sequence, to the post office box; the extended address; the street
     * address; the locality (e.g., city); the region (e.g., state or
     * province); the postal code; the country name. When a component value
     * is missing, the associated component separator MUST still be
     * specified.
     * The text components are separated by the SEMI-COLON character (ASCII
     * decimal 59). Where it makes semantic sense, individual text
     * components can include multiple text values (e.g., a "street"
     * component with multiple lines) separated by the COMMA character
     * (ASCII decimal 44).
     * The type can include the type parameter "TYPE" to specify the
     * delivery address type. The TYPE parameter values can include "dom" to
     * indicate a domestic delivery address; "intl" to indicate an
     * international delivery address; "postal" to indicate a postal
     * delivery address; "parcel" to indicate a parcel delivery address;
     * "home" to indicate a delivery address for a residence; "work" to
     * indicate delivery address for a place of work; and "pref" to indicate
     * the preferred delivery address when more than one address is
     * specified. These type parameter values can be specified as a
     * parameter list (i.e., "TYPE=dom;TYPE=postal") or as a value list
     * (i.e., "TYPE=dom,postal"). This type is based on semantics of the
     * X.520 geographical and postal addressing attributes. The default is
     * "TYPE=intl,postal,parcel,work". The default can be overridden to some
     * other set of values by specifying one or more alternate values. For
     * example, the default can be reset to "TYPE=dom,postal,work,home" to
     * specify a domestic delivery address for postal delivery to a
     * residence that is also used for work.
     * @param string $po_box           Post office box
     * @param string $extended_address Extended address
     * @param string $street_address   Street address
     * @param string $locality         Locality (e.g. city)
     * @param string $region           Region (e.g. state or province)
     * @param string $postal_code      Postal code
     * @param string $country          Country
     * @param int    $type             The address type (can be combined with the + operator)
     * @access    public
     */
    public function setAddress(
        string $po_box = "",
        string $extended_address = "",
        string $street_address = "",
        string $locality = "",
        string $region = "",
        string $postal_code = "",
        string $country = "",
        int $type = ADR_TYPE_NONE
    ) : void {
        if ($type == ADR_TYPE_NONE) {
            $type = ADR_TYPE_INTL + ADR_TYPE_POSTAL + ADR_TYPE_PARCEL + ADR_TYPE_WORK;
        }
        $po_box = implode(",", $this->explodeVar($po_box));
        $extended_address = implode(",", $this->explodeVar($extended_address));
        $street_address = implode(",", $this->explodeVar($street_address));
        $locality = implode(",", $this->explodeVar($locality));
        $region = implode(",", $this->explodeVar($region));
        $postal_code = implode(",", $this->explodeVar($postal_code));
        $country = implode(",", $this->explodeVar($country));
        $this->types["ADR"][] = array(
            "POBOX" => $po_box,
            "EXTENDED_ADDRESS" => $extended_address,
            "STREET_ADDRESS" => $street_address,
            "LOCALITY" => $locality,
            "REGION" => $region,
            "POSTAL_CODE" => $postal_code,
            "COUNTRY" => $country,
            "TYPE" => $type
        );
    }

    /**
     * Sets the value for the vCard LABEL type.
     * Sets the value for the vCard LABEL type to specify
     * the formatted text corresponding to delivery
     * address of the object the vCard represents
     * Type example: A multi-line address label.
     * LABEL;TYPE=dom,home,postal,parcel:Mr.John Q. Public\, Esq.\n
     *   Mail Drop: TNE QB\n123 Main Street\nAny Town\, CA  91921-1234
     *   \nU.S.A.
     * Type special notes: The type value is formatted text that can be used
     * to present a delivery address label for the vCard object. The type
     * can include the type parameter "TYPE" to specify delivery label type.
     * The TYPE parameter values can include "dom" to indicate a domestic
     * delivery label; "intl" to indicate an international delivery label;
     * "postal" to indicate a postal delivery label; "parcel" to indicate a
     * parcel delivery label; "home" to indicate a delivery label for a
     * residence; "work" to indicate delivery label for a place of work; and
     * "pref" to indicate the preferred delivery label when more than one
     * label is specified. These type parameter values can be specified as a
     * parameter list (i.e., "TYPE=dom;TYPE=postal") or as a value list
     * (i.e., "TYPE=dom,postal"). This type is based on semantics of the
     * X.520 geographical and postal addressing attributes. The default is
     * "TYPE=intl,postal,parcel,work". The default can be overridden to some
     * other set of values by specifying one or more alternate values. For
     * example, the default can be reset to "TYPE=intl,post,parcel,home" to
     * specify an international delivery label for both postal and parcel
     * delivery to a residential location.
     * @param string $label The address label
     * @param int    $type  The address type (can be combined with the + operator)
     */
    public function setLabel(
        string $label = "",
        int $type = ADR_TYPE_NONE
    ) : void {
        if ($type == ADR_TYPE_NONE) {
            $type = ADR_TYPE_INTL + ADR_TYPE_POSTAL + ADR_TYPE_PARCEL + ADR_TYPE_WORK;
        }
        $this->types["LABEL"] = array(
            "LABEL" => $this->escape($label),
            "TYPE" => $type
        );
    }

    // Telecommunications Addressing Types
    // These types are concerned with information associated with the
    // telecommunications addressing of the object the vCard represents.

    /**
     * Sets the value for the vCard TEL type.
     * Sets the value for the vCard TEL type to specify
     * the telephone number for telephony communication
     * with the object the vCard represents.
     * Type example:
     * TEL;TYPE=work,voice,pref,msg:+1-213-555-1234
     * Type special notes: The value of this type is specified in a
     * canonical form in order to specify an unambiguous representation of
     * the globally unique telephone endpoint. This type is based on the
     * X.500 Telephone Number attribute.
     * The type can include the type parameter "TYPE" to specify intended
     * use for the telephone number. The TYPE parameter values can include:
     * "home" to indicate a telephone number associated with a residence,
     * "msg" to indicate the telephone number has voice messaging support,
     * "work" to indicate a telephone number associated with a place of
     * vwork, "pref" to indicate a preferred-use telephone number, "voice" to
     * indicate a voice telephone number, "fax" to indicate a facsimile
     * telephone number, "cell" to indicate a cellular telephone number,
     * "video" to indicate a video conferencing telephone number, "pager" to
     * indicate a paging device telephone number, "bbs" to indicate a
     * bulletin board system telephone number, "modem" to indicate a MODEM
     * connected telephone number, "car" to indicate a car-phone telephone
     * number, "isdn" to indicate an ISDN service telephone number, "pcs" to
     * indicate a personal communication services telephone number. The
     * default type is "voice". These type parameter values can be specified
     * as a parameter list (i.e., "TYPE=work;TYPE=voice") or as a value list
     * (i.e., "TYPE=work,voice"). The default can be overridden to another
     * set of values by specifying one or more alternate values. For
     * example, the default TYPE of "voice" can be reset to a WORK and HOME,
     * VOICE and FAX telephone number by the value list
     * "TYPE=work,home,voice,fax".
     * @param string $number The phone number
     * @param int    $type   The address type (can be combined with the + operator)
     */
    public function setPhone(
        string $number = "",
        int $type = TEL_TYPE_VOICE
    ) : void {
        $this->types["TEL"][] = array(
            "TEL" => $this->escape($number),
            "TYPE" => $type
        );
    }

    /**
     * Sets the value for the vCard EMAIL type.
     * Sets the value for the vCard EMAIL type to specify
     * the electronic mail address for communication with
     * the object the vCard represents.
     * Type example:
     * EMAIL;TYPE=internet:jqpublic@xyz.dom1.com
     * EMAIL;TYPE=internet:jdoe@isp.net
     * EMAIL;TYPE=internet,pref:jane_doe@abc.com
     * Type special notes: The type can include the type parameter "TYPE" to
     * specify the format or preference of the electronic mail address. The
     * TYPE parameter values can include: "internet" to indicate an Internet
     * addressing type, "x400" to indicate a X.400 addressing type or "pref"
     * to indicate a preferred-use email address when more than one is
     * specified. Another IANA registered address type can also be
     * specified. The default email type is "internet". A non-standard value
     * can also be specified.
     * @param string $address The email address
     * @param int    $type    The address type (can be combined with the + operator)
     */
    public function setEmail(
        string $address = "",
        int $type = EMAIL_TYPE_INTERNET
    ) : void {
        $this->types["EMAIL"][] = array(
            "EMAIL" => $this->escape($address),
            "TYPE" => $type
        );
    }

    /**
     * Sets the value for the vCard MAILER type.
     * Sets the value for the vCard MAILER type to specify
     * the type of electronic mail software that is used by
     * the individual associated with the vCard.
     * Type example:
     * MAILER:PigeonMail 2.1
     * Type special notes: This information can provide assistance to a
     * correspondent regarding the type of data representation which can be
     * used, and how they can be packaged. This property is based on the
     * private MIME type X-Mailer that is generally implemented by MIME user
     * agent products.
     * @param string $name The mailer name
     */
    public function setMailer(string $name = "") : void
    {
        $this->types["MAILER"] = $this->escape($name);
    }

    // Geographical Types
    // These types are concerned with information associated with
    // geographical positions or regions associated with the object the
    // vCard represents.

    /**
     * Sets the value for the vCard TZ type.
     * Sets the value for the vCard TZ type to specify
     * information related to the time zone of the
     * object the vCard represents.
     * Type example:
     * TZ:-05:00
     * TZ;VALUE=text:-05:00; EST; Raleigh/North America
     * Type special notes: The type value consists of a single value.
     * @param string $zone The timezone as utc-offset value
     */
    public function setTimezone(string $zone = "") : void
    {
        $this->types["TZ"] = $this->escape($zone);
    }

    /**
     * Sets the value for the vCard GEO type.
     * Sets the value for the vCard GEO type to specify
     * information related to the global positioning of
     * the object the vCard represents.
     * Type example:
     * GEO:37.386013;-122.082932
     * Type value: A single structured value consisting of two float values
     * separated by the SEMI-COLON character (ASCII decimal 59).
     * Type special notes: This type specifies information related to the
     * global position of the object associated with the vCard. The value
     * specifies latitude and longitude, in that order (i.e., "LAT LON"
     * ordering). The longitude represents the location east and west of the
     * prime meridian as a positive or negative real number, respectively.
     * The latitude represents the location north and south of the equator
     * as a positive or negative real number, respectively. The longitude
     * and latitude values MUST be specified as decimal degrees and should
     * be specified to six decimal places. This will allow for granularity
     * within a meter of the geographical position. The text components are
     * separated by the SEMI-COLON character (ASCII decimal 59). The simple
     * formula for converting degrees-minutes-seconds into decimal degrees
     * is:
     *        decimal = degrees + minutes/60 + seconds/3600.
     * @param string $latitude  The latitude of the position
     * @param string $longitude The longitude of the position
     */
    public function setPosition(string $latitude = "", string $longitude = "") : void
    {
        $this->types["GEO"] = array(
            "LAT" => $latitude,
            "LON" => $longitude
        );
    }

    // Organizational Types
    // These types are concerned with information associated with
    // characteristics of the organization or organizational units of the
    // object the vCard represents.

    /**
     * Sets the value for the vCard TITLE type.
     * Sets the value for the vCard TITLE type to specify
     * the job title, functional position or function of
     * the object the vCard represents.
     * Type example:
     * TITLE:Director\, Research and Development
     * Type special notes: This type is based on the X.520 Title attribute.
     * @param string $title Job title
     */
    public function setTitle(string $title = "") : void
    {
        $this->types["TITLE"] = $this->escape($title);
    }

    /**
     * Sets the value for the vCard ROLE type.
     * Sets the value for the vCard ROLE type to specify
     * information concerning the role, occupation, or business
     * category of the object the vCard represents.
     * Type example:
     * ROLE:Programmer
     * Type special notes: This type is based on the X.520 Business Category
     * explanatory attribute. This property is included as an organizational
     * type to avoid confusion with the semantics of the TITLE type and
     * incorrect usage of that type when the semantics of this type is
     * intended.
     * @param string $role Role title
     */
    public function setRole(string $role = "") : void
    {
        $this->types["ROLE"] = $this->escape($role);
    }

    /**
     * Sets the value for the vCard LOGO type.
     * Sets the value for the vCard LOGO type to specify
     * a graphic image of a logo associated with the object
     * the vCard represents.
     * Type example:
     * LOGO;VALUE=uri:http://www.abc.com/pub/logos/abccorp.jpg
     * LOGO;ENCODING=b;TYPE=JPEG:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcN
     *   AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
     *   ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
     *   <...the remainder of "B" encoded binary data...>
     * Type encoding: The encoding MUST be reset to "b" using the ENCODING
     * parameter in order to specify inline, encoded binary data. If the
     * value is referenced by a URI value, then the default encoding of 8bit
     * is used and no explicit ENCODING parameter is needed.
     * Type value: A single value. The default is binary value. It can also
     * be reset to uri value. The uri value can be used to specify a value
     * outside of this MIME entity.
     * Type special notes: The type can include the type parameter "TYPE" to
     * specify the graphic image format type. The TYPE parameter values MUST
     * be one of the IANA registered image formats or a non-standard image
     * format.
     * @param string $logo A binary string containing the logo or an uri
     * @param string $type The IANA type of the image format
     */
    public function setLogo(string $logo, string $type = "") : void
    {
        $value = "";
        $encoding = "";
        if (preg_match("/^http/", $logo)) {
            $value = $this->encode($logo);
        } else {
            $encoding = "b";
            $logo = base64_encode($logo);
        }
        $this->types["LOGO"] = array(
            "VALUE" => $value,
            "TYPE" => $type,
            "ENCODING" => $encoding,
            "LOGO" => $logo
        );
    }

    /**
     * Sets the value for the vCard AGENT type.
     * Sets the value for the vCard AGENT type to specify
     * information about another person who will act on behalf
     * of the individual or resource associated with the vCard.
     * Type example:
     * AGENT;VALUE=uri:
     *   CID:JQPUBLIC.part3.960129T083020.xyzMail@host3.com
     * AGENT:BEGIN:VCARD\nFN:Susan Thomas\nTEL:+1-919-555-
     *   1234\nEMAIL\;INTERNET:sthomas@host.com\nEND:VCARD\n
     * Type value: The default is a single vcard value. It can also be reset
     * to either a single text or uri value. The text value can be used to
     * specify textual information. The uri value can be used to specify
     * information outside of this MIME entity.
     * Type special notes: This type typically is used to specify an area
     * administrator, assistant, or secretary for the individual associated
     * with the vCard. A key characteristic of the Agent type is that it
     * represents somebody or something that is separately addressable.
     * @param string $agent Agent type
     */
    public function setAgent(string $agent = "") : void
    {
        $this->types["AGENT"] = $this->escape($agent);
    }

    /**
     * Sets the value for the vCard ORG type.
     * Sets the value for the vCard ORG type to specify
     * the organizational name and units associated with the vCard.
     * Type example:
     * ORG:ABC\, Inc.;North American Division;Marketing
     * Type value: A single structured text value consisting of components
     * separated the SEMI-COLON character (ASCII decimal 59).
     * Type special notes: The type is based on the X.520 Organization Name
     * and Organization Unit attributes. The type value is a structured type
     * consisting of the organization name, followed by one or more levels
     * of organizational unit names.
     * @param string $organization Organization description
     */
    public function setOrganization(string $organization = "") : void
    {
        $organization = implode(";", $this->explodeVar($organization, ";"));
        $this->types["ORG"] = $organization;
    }

    // Explanatory Types
    // These types are concerned with additional explanations, such as that
    // related to informational notes or revisions specific to the vCard.

    /**
     * Sets the value for the vCard CATEGORIES type.
     * Sets the value for the vCard CATEGORIES type to specify
     * application category information about the vCard.
     * Type example:
     * CATEGORIES:TRAVEL AGENT
     * CATEGORIES:INTERNET,IETF,INDUSTRY,INFORMATION TECHNOLOGY
     * Type value: One or more text values separated by a COMMA character
     * (ASCII decimal 44).
     * @access    public
     */
    public function setCategories(string $categories) : void
    {
        $categories = implode(",", $this->explodeVar($categories));
        $this->types["CATEGORIES"] = $categories;
    }

    /**
     * Sets the value for the vCard NOTE type.
     * Sets the value for the vCard NOTE type to specify
     * supplemental information or a comment that is associated with the vCard.
     * Type example:
     * NOTE:This fax number is operational 0800 to 1715
     *   EST\, Mon-Fri.
     * Type value: A single text value.
     * Type special notes: The type is based on the X.520 Description
     * attribute.
     * @param string $note A note or comment
     */
    public function setNote(string $note = "") : void
    {
        $this->types["NOTE"] = $this->escape($note);
    }

    /**
     * Sets the value for the vCard PRODID type.
     * Sets the value for the vCard PRODID type to specify
     * the identifier for the product that created the vCard object.
     * Type example:
     * PRODID:-//ONLINE DIRECTORY//NONSGML Version 1//EN
     * Type value: A single text value.
     * Type special notes: Implementations SHOULD use a method such as that
     * specified for Formal Public Identifiers in ISO 9070 to assure that
     * the text value is unique.
     * @param string $product_id Product identifier
     */
    public function setProductId(string $product_id = "") : void
    {
        $this->types["PRODID"] = $this->escape($product_id);
    }

    /**
     * Sets the value for the vCard REV type.
     * Sets the value for the vCard REV type to specify
     * revision information about the current vCard.
     * Type example:
     * REV:1995-10-31T22:27:10Z
     * REV:1997-11-15
     * Type value: The default is a single date-time value. Can also be
     * reset to a single date value.
     * Type special notes: The value distinguishes the current revision of
     * the information in this vCard for other renditions of the
     * information.
     * @param string $revision_date Revision date
     */
    public function setRevision(string $revision_date = "") : void
    {
        $this->types["REV"] = $this->escape($revision_date);
    }

    /**
     * Sets the value for the vCard SORT-STRING type.
     * Sets the value for the vCard SORT-STRING type to specify
     * the family name or given name text to be used for
     * national-language-specific sorting of the FN and N types.
     * Type examples: For the case of family name sorting, the following
     * examples define common sort string usage with the FN and N types.
     *   FN:Rene van der Harten
     *   N:van der Harten;Rene;J.;Sir;R.D.O.N.
     *   SORT-STRING:Harten
     *   FN:Robert Pau Shou Chang
     *   N:Pau;Shou Chang;Robert
     *   SORT-STRING:Pau
     *   FN:Osamu Koura
     *   N:Koura;Osamu
     *   SORT-STRING:Koura
     *   FN:Oscar del Pozo
     *   N:del Pozo Triscon;Oscar
     *   SORT-STRING:Pozo
     *   FN:Chistine d'Aboville
     *   N:d'Aboville;Christine
     *   SORT-STRING:Aboville
     * Type value: A single text value.
     * Type special notes: The sort string is used to provide family name or
     * given name text that is to be used in locale- or national-language-
     * specific sorting of the formatted name and structured name types.
     * Without this information, sorting algorithms could incorrectly sort
     * this vCard within a sequence of sorted vCards.  When this type is
     * present in a vCard, then this family name or given name value is used
     * for sorting the vCard.
     * @param string $string Sort string
     */
    public function setSortString(string $string = "") : void
    {
        $this->types["SORT-STRING"] = $this->escape($string);
    }

    /**
     * Sets the value for the vCard SOUND type.
     * Sets the value for the vCard SOUND type to specify
     * a digital sound content information that annotates some
     * aspect of the vCard. By default this type is used to specify
     * the proper pronunciation of the name type value of the vCard.
     * Type example:
     * SOUND;TYPE=BASIC;VALUE=uri:CID:JOHNQPUBLIC.part8.
     *   19960229T080000.xyzMail@host1.com
     * SOUND;TYPE=BASIC;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcN
     *   AQEEBQAwdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENvbW11bm
     *   ljYXRpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
     *   <...the remainder of "B" encoded binary data...>
     * Type encoding: The encoding MUST be reset to "b" using the ENCODING
     * parameter in order to specify inline, encoded binary data. If the
     * value is referenced by a URI value, then the default encoding of 8bit
     * is used and no explicit ENCODING parameter is needed.
     * Type value: A single value. The default is binary value. It can also
     * be reset to uri value. The uri value can be used to specify a value
     * outside of this MIME entity.
     * Type special notes: The type can include the type parameter "TYPE" to
     * specify the audio format type. The TYPE parameter values MUST be one
     * of the IANA registered audio formats or a non-standard audio format.
     * @param string $sound Binary string containing the sound
     * @param string $type  The IANA registered sound type
     */
    public function setSound(string $sound = "", string $type = "") : void
    {
        $value = "";
        $encoding = "";
        if (preg_match("/^http/", $sound)) {
            $value = $this->encode($sound);
        } else {
            $encoding = "b";
            $sound = base64_encode($sound);
        }
        $this->types["SOUND"] = array(
            "VALUE" => $value,
            "TYPE" => $type,
            "ENCODING" => $encoding,
            "SOUND" => $sound
        );
    }

    /**
     * Sets the value for the vCard UID type.
     * Sets the value for the vCard UID type to specify
     * a value that represents a globally unique identifier
     * corresponding to the individual or resource associated
     * with the vCard.
     * Type example:
     * UID:19950401-080045-40000F192713-0052
     * Type value: A single text value.
     * Type special notes: The type is used to uniquely identify the object
     * that the vCard represents.
     * The type can include the type parameter "TYPE" to specify the format
     * of the identifier. The TYPE parameter value should be an IANA
     * registered identifier format. The value can also be a non-standard
     * format.
     * @param string $uid  Globally unique identifier
     * @param string $type IANA registered identifier format
     */
    public function setUID(string $uid = "", string $type = "") : void
    {
        $this->types["UID"] = array(
            "UID" => $this->escape($uid),
            "TYPE" => $type
        );
    }

    /**
     * Sets the value for the vCard URL type.
     * Sets the value for the vCard URL type to specify
     * a uniform resource locator associated with the object that the
     * vCard refers to.
     * Type example:
     * URL:http://www.ilias.de/index.html
     * Type value: A single text value.
     * @param string $uri URL
     */
    public function setURL(string $uri = "") : void
    {
        $this->types["URL"] = $this->escape($uri);
    }

    /**
     * Sets the value for the vCard VERSION type.
     * Sets the value for the vCard VERSION type to specify
     * the version of the vCard specification used
     * Type example:
     * VERSION:3.0
     * Type special notes: The property MUST be present in the vCard object.
     * The value MUST be "3.0" if the vCard corresponds to the vCard 3.0 specification.
     * @param string $version Version string
     */
    public function setVersion(string $version = "3.0") : void
    {
        $this->types["VERSION"] = $version;
    }

    // Security Types
    // These types are concerned with the security of communication pathways
    // or access to the vCard.

    /**
     * Sets the value for the vCard CLASS type.
     * Sets the value for the vCard CLASS type to specify
     * the access classification for a vCard object.
     * Type example:
     * CLASS:PUBLIC
     * CLASS:PRIVATE
     * CLASS:CONFIDENTIAL
     * Type value: A single text value.
     * Type special notes: An access classification is only one component of
     * the general security model for a directory service. The
     * classification attribute provides a method of capturing the intent of
     * the owner for general access to information described by the vCard
     * object.
     * @param string $classification Classification string
     */
    public function setClassification(string $classification = "") : void
    {
        $this->types["CLASS"] = $this->escape($classification);
    }

    /**
     * Sets the value for the vCard KEY type.
     * Sets the value for the vCard KEY type to specify
     * a public key or authentication certificate associated
     * with the object that the vCard represents.
     * Type example:
     * KEY;ENCODING=b:MIICajCCAdOgAwIBAgICBEUwDQYJKoZIhvcNAQEEBQA
     *   wdzELMAkGA1UEBhMCVVMxLDAqBgNVBAoTI05ldHNjYXBlIENbW11bmljYX
     *   Rpb25zIENvcnBvcmF0aW9uMRwwGgYDVQQLExNJbmZvcm1hdGlvbiBTeXN0
     *   ZW1zMRwwGgYDVQQDExNyb290Y2EubmV0c2NhcGUuY29tMB4XDTk3MDYwNj
     *   E5NDc1OVoXDTk3MTIwMzE5NDc1OVowgYkxCzAJBgNVBAYTAlVTMSYwJAYD
     *   VQQKEx1OZXRzY2FwZSBDb21tdW5pY2F0aW9ucyBDb3JwLjEYMBYGA1UEAx
     *   MPVGltb3RoeSBBIEhvd2VzMSEwHwYJKoZIhvcNAQkBFhJob3dlc0BuZXRz
     *   Y2FwZS5jb20xFTATBgoJkiaJk/IsZAEBEwVob3dlczBcMA0GCSqGSIb3DQ
     *   EBAQUAA0sAMEgCQQC0JZf6wkg8pLMXHHCUvMfL5H6zjSk4vTTXZpYyrdN2
     *   dXcoX49LKiOmgeJSzoiFKHtLOIboyludF90CgqcxtwKnAgMBAAGjNjA0MB
     *   EGCWCGSAGG+EIBAQQEAwIAoDAfBgNVHSMEGDAWgBT84FToB/GV3jr3mcau
     *   +hUMbsQukjANBgkqhkiG9w0BAQQFAAOBgQBexv7o7mi3PLXadkmNP9LcIP
     *   mx93HGp0Kgyx1jIVMyNgsemeAwBM+MSlhMfcpbTrONwNjZYW8vJDSoi//y
     *   rZlVt9bJbs7MNYZVsyF1unsqaln4/vy6Uawfg8VUMk1U7jt8LYpo4YULU7
     *   UZHPYVUaSgVttImOHZIKi4hlPXBOhcUQ==
     * Type encoding: The encoding MUST be reset to "b" using the ENCODING
     * parameter in order to specify inline, encoded binary data. If the
     * value is a text value, then the default encoding of 8bit is used and
     * no explicit ENCODING parameter is needed.
     * Type value: A single value. The default is binary. It can also be
     * reset to text value. The text value can be used to specify a text
     * key.
     * Type special notes: The type can also include the type parameter TYPE
     * to specify the public key or authentication certificate format. The
     * parameter type should specify an IANA registered public key or
     * authentication certificate format. The parameter type can also
     * specify a non-standard format.
     * @param string $key  Public key
     * @param string $type IANA registered public key or authentication certificate format
     */
    public function setKey(string $key = "", string $type = "") : void
    {
        $encoding = "b";
        $key = base64_encode($key);
        $this->types["KEY"] = array(
            "KEY" => $key,
            "TYPE" => $type,
            "ENCODING" => $encoding
        );
    }

    public function getFilename() : string
    {
        if (strcmp($this->filename, "") == 0) {
            return "vcard.vcf";
        } else {
            return $this->filename;
        }
    }

    public function getMimetype() : string
    {
        return "text/x-vcard";
    }
}
