<?php

namespace ILIAS\FileUpload;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Mime type determination.
 *
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 * @author     Alexander Killing <killing@leifos.de>
 *
 * @deprecated This should be moved to Data/Refinery
 */
class MimeType
{
    const APPLICATION__ACAD = 'application/acad';
    const APPLICATION__ARJ = 'application/arj';
    const APPLICATION__ASTOUND = 'application/astound';
    const APPLICATION__BASE64 = 'application/base64';
    const APPLICATION__BINHEX = 'application/binhex';
    const APPLICATION__BINHEX4 = 'application/binhex4';
    const APPLICATION__BOOK = 'application/book';
    const APPLICATION__CDF = 'application/cdf';
    const APPLICATION__CLARISCAD = 'application/clariscad';
    const APPLICATION__COMMONGROUND = 'application/commonground';
    const APPLICATION__DRAFTING = 'application/drafting';
    const APPLICATION__DSPTYPE = 'application/dsptype';
    const APPLICATION__DXF = 'application/dxf';
    const APPLICATION__ECMASCRIPT = 'application/ecmascript';
    const APPLICATION__ENVOY = 'application/envoy';
    const APPLICATION__EPUB = 'application/epub+zip';
    const APPLICATION__EXCEL = 'application/excel';
    const APPLICATION__FONT_WOFF = 'application/font-woff';
    const APPLICATION__FRACTALS = 'application/fractals';
    const APPLICATION__FREELOADER = 'application/freeloader';
    const APPLICATION__FUTURESPLASH = 'application/futuresplash';
    const APPLICATION__GNUTAR = 'application/gnutar';
    const APPLICATION__GROUPWISE = 'application/groupwise';
    const APPLICATION__HLP = 'application/hlp';
    const APPLICATION__HTA = 'application/hta';
    const APPLICATION__IGES = 'application/iges';
    const APPLICATION__INF = 'application/inf';
    const APPLICATION__I_DEAS = 'application/i-deas';
    const APPLICATION__JAVA = 'application/java';
    const APPLICATION__JAVASCRIPT = 'application/javascript';
    const APPLICATION__JAVA_BYTE_CODE = 'application/java-byte-code';
    const APPLICATION__JSON = 'application/json';
    const APPLICATION__LHA = 'application/lha';
    const APPLICATION__LZX = 'application/lzx';
    const APPLICATION__MACBINARY = 'application/macbinary';
    const APPLICATION__MAC_BINARY = 'application/mac-binary';
    const APPLICATION__MAC_BINHEX = 'application/mac-binhex';
    const APPLICATION__MAC_BINHEX40 = 'application/mac-binhex40';
    const APPLICATION__MAC_COMPACTPRO = 'application/mac-compactpro';
    const APPLICATION__MARC = 'application/marc';
    const APPLICATION__MBEDLET = 'application/mbedlet';
    const APPLICATION__MCAD = 'application/mcad';
    const APPLICATION__MIME = 'application/mime';
    const APPLICATION__MSPOWERPOINT = 'application/mspowerpoint';
    const APPLICATION__MSWORD = 'application/msword';
    const APPLICATION__MSWRITE = 'application/mswrite';
    const APPLICATION__NETMC = 'application/netmc';
    const APPLICATION__OCTET_STREAM = 'application/octet-stream';
    const APPLICATION__ODA = 'application/oda';
    const APPLICATION__OGG = 'application/ogg';
    const APPLICATION__PDF = 'application/pdf';
    const APPLICATION__PKCS10 = 'application/pkcs10';
    const APPLICATION__PKCS7_MIME = 'application/pkcs7-mime';
    const APPLICATION__PKCS7_SIGNATURE = 'application/pkcs7-signature';
    const APPLICATION__PKCS_12 = 'application/pkcs-12';
    const APPLICATION__PKCS_CRL = 'application/pkcs-crl';
    const APPLICATION__PKIX_CERT = 'application/pkix-cert';
    const APPLICATION__PKIX_CRL = 'application/pkix-crl';
    const APPLICATION__PLAIN = 'application/plain';
    const APPLICATION__POSTSCRIPT = 'application/postscript';
    const APPLICATION__POWERPOINT = 'application/powerpoint';
    const APPLICATION__PRO_ENG = 'application/pro_eng';
    const APPLICATION__RINGING_TONES = 'application/ringing-tones';
    const APPLICATION__RTF = 'application/rtf';
    const APPLICATION__SDP = 'application/sdp';
    const APPLICATION__SEA = 'application/sea';
    const APPLICATION__SEB = 'application/seb';
    const APPLICATION__SET = 'application/set';
    const APPLICATION__SLA = 'application/sla';
    const APPLICATION__SMIL = 'application/smil';
    const APPLICATION__SOLIDS = 'application/solids';
    const APPLICATION__SOUNDER = 'application/sounder';
    const APPLICATION__STEP = 'application/step';
    const APPLICATION__STREAMINGMEDIA = 'application/streamingmedia';
    const APPLICATION__TOOLBOOK = 'application/toolbook';
    const APPLICATION__VDA = 'application/vda';
    const APPLICATION__VND_FDF = 'application/vnd.fdf';
    const APPLICATION__VND_HP_HPGL = 'application/vnd.hp-hpgl';
    const APPLICATION__VND_HP_PCL = 'application/vnd.hp-pcl';
    const APPLICATION__VND_MS_EXCEL = 'application/vnd.ms-excel';
    const APPLICATION__VND_MS_EXCEL_ADDIN_MACRO_ENABLED_12 = 'application/vnd.ms-excel.addin.macroEnabled.12';
    const APPLICATION__VND_MS_EXCEL_SHEET_BINARY_MACRO_ENABLED_12 = 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
    const APPLICATION__VND_MS_PKI_CERTSTORE = 'application/vnd.ms-pki.certstore';
    const APPLICATION__VND_MS_PKI_PKO = 'application/vnd.ms-pki.pko';
    const APPLICATION__VND_MS_PKI_SECCAT = 'application/vnd.ms-pki.seccat';
    const APPLICATION__VND_MS_PKI_STL = 'application/vnd.ms-pki.stl';
    const APPLICATION__VND_MS_POWERPOINT = 'application/vnd.ms-powerpoint';
    const APPLICATION__VND_MS_PROJECT = 'application/vnd.ms-project';
    const APPLICATION__VND_NOKIA_CONFIGURATION_MESSAGE = 'application/vnd.nokia.configuration-message';
    const APPLICATION__VND_NOKIA_RINGING_TONE = 'application/vnd.nokia.ringing-tone';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_PRESENTATION = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_SLIDE = 'application/vnd.openxmlformats-officedocument.presentationml.slide';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_SLIDESHOW = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_TEMPLATE = 'application/vnd.openxmlformats-officedocument.presentationml.template';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_TEMPLATE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_DOCUMENT = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_TEMPLATE = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
    const APPLICATION__VND_RN_REALMEDIA = 'application/vnd.rn-realmedia';
    const APPLICATION__VND_RN_REALPLAYER = 'application/vnd.rn-realplayer';
    const APPLICATION__VND_WAP_WMLC = 'application/vnd.wap.wmlc';
    const APPLICATION__VND_WAP_WMLSCRIPTC = 'application/vnd.wap.wmlscriptc';
    const APPLICATION__VND_XARA = 'application/vnd.xara';
    const APPLICATION__VOCALTEC_MEDIA_DESC = 'application/vocaltec-media-desc';
    const APPLICATION__VOCALTEC_MEDIA_FILE = 'application/vocaltec-media-file';
    const APPLICATION__WORDPERFECT = 'application/wordperfect';
    const APPLICATION__WORDPERFECT6_0 = 'application/wordperfect6.0';
    const APPLICATION__WORDPERFECT6_1 = 'application/wordperfect6.1';
    const APPLICATION__XHTML_XML = 'application/xhtml+xml';
    const APPLICATION__XML = 'application/xml';
    const APPLICATION__X_123 = 'application/x-123';
    const APPLICATION__X_AIM = 'application/x-aim';
    const APPLICATION__X_AUTHORWARE_BIN = 'application/x-authorware-bin';
    const APPLICATION__X_AUTHORWARE_MAP = 'application/x-authorware-map';
    const APPLICATION__X_AUTHORWARE_SEG = 'application/x-authorware-seg';
    const APPLICATION__X_BCPIO = 'application/x-bcpio';
    const APPLICATION__X_BINARY = 'application/x-binary';
    const APPLICATION__X_BINHEX40 = 'application/x-binhex40';
    const APPLICATION__X_BSH = 'application/x-bsh';
    const APPLICATION__X_BYTECODE_PYTHON = 'application/x-bytecode.python';
    const APPLICATION__X_BZIP = 'application/x-bzip';
    const APPLICATION__X_BZIP2 = 'application/x-bzip2';
    const APPLICATION__X_CDF = 'application/x-cdf';
    const APPLICATION__X_CDLINK = 'application/x-cdlink';
    const APPLICATION__X_CHAT = 'application/x-chat';
    const APPLICATION__X_CMU_RASTER = 'application/x-cmu-raster';
    const APPLICATION__X_COCOA = 'application/x-cocoa';
    const APPLICATION__X_COMPACTPRO = 'application/x-compactpro';
    const APPLICATION__X_COMPRESS = 'application/x-compress';
    const APPLICATION__X_COMPRESSED = 'application/x-compressed';
    const APPLICATION__X_CONFERENCE = 'application/x-conference';
    const APPLICATION__X_CPIO = 'application/x-cpio';
    const APPLICATION__X_CPT = 'application/x-cpt';
    const APPLICATION__X_CSH = 'application/x-csh';
    const APPLICATION__X_DEEPV = 'application/x-deepv';
    const APPLICATION__X_DIRECTOR = 'application/x-director';
    const APPLICATION__X_DVI = 'application/x-dvi';
    const APPLICATION__X_ELC = 'application/x-elc';
    const APPLICATION__X_ENVOY = 'application/x-envoy';
    const APPLICATION__X_ESREHBER = 'application/x-esrehber';
    const APPLICATION__X_EXCEL = 'application/x-excel';
    const APPLICATION__X_FRAME = 'application/x-frame';
    const APPLICATION__X_FREELANCE = 'application/x-freelance';
    const APPLICATION__X_GSP = 'application/x-gsp';
    const APPLICATION__X_GSS = 'application/x-gss';
    const APPLICATION__X_GTAR = 'application/x-gtar';
    const APPLICATION__X_GZIP = 'application/x-gzip';
    const APPLICATION__X_HDF = 'application/x-hdf';
    const APPLICATION__X_HELPFILE = 'application/x-helpfile';
    const APPLICATION__X_HTTPD_IMAP = 'application/x-httpd-imap';
    const APPLICATION__X_IMA = 'application/x-ima';
    const APPLICATION__X_INTERNETT_SIGNUP = 'application/x-internett-signup';
    const APPLICATION__X_INVENTOR = 'application/x-inventor';
    const APPLICATION__X_IP2 = 'application/x-ip2';
    const APPLICATION__X_JAVASCRIPT = 'application/x-javascript';
    const APPLICATION__X_JAVA_APPLET = 'application/x-java-applet';
    const APPLICATION__X_JAVA_CLASS = 'application/x-java-class';
    const APPLICATION__X_JAVA_COMMERCE = 'application/x-java-commerce';
    const APPLICATION__X_KOAN = 'application/x-koan';
    const APPLICATION__X_KSH = 'application/x-ksh';
    const APPLICATION__X_LATEX = 'application/x-latex';
    const APPLICATION__X_LHA = 'application/x-lha';
    const APPLICATION__X_LISP = 'application/x-lisp';
    const APPLICATION__X_LIVESCREEN = 'application/x-livescreen';
    const APPLICATION__X_LOTUS = 'application/x-lotus';
    const APPLICATION__X_LOTUSSCREENCAM = 'application/x-lotusscreencam';
    const APPLICATION__X_LZH = 'application/x-lzh';
    const APPLICATION__X_LZX = 'application/x-lzx';
    const APPLICATION__X_MACBINARY = 'application/x-macbinary';
    const APPLICATION__X_MAC_BINHEX40 = 'application/x-mac-binhex40';
    const APPLICATION__X_MAGIC_CAP_PACKAGE_1_0 = 'application/x-magic-cap-package-1.0';
    const APPLICATION__X_MATHCAD = 'application/x-mathcad';
    const APPLICATION__X_MOBI = 'application/x-mobipocket-ebook';
    const APPLICATION__X_MEME = 'application/x-meme';
    const APPLICATION__X_MIDI = 'application/x-midi';
    const APPLICATION__X_MIF = 'application/x-mif';
    const APPLICATION__X_MIX_TRANSFER = 'application/x-mix-transfer';
    const APPLICATION__X_MPLAYER2 = 'application/x-mplayer2';
    const APPLICATION__X_MSEXCEL = 'application/x-msexcel';
    const APPLICATION__X_MSPOWERPOINT = 'application/x-mspowerpoint';
    const APPLICATION__X_NAVIDOC = 'application/x-navidoc';
    const APPLICATION__X_NAVIMAP = 'application/x-navimap';
    const APPLICATION__X_NAVISTYLE = 'application/x-navistyle';
    const APPLICATION__X_NAVI_ANIMATION = 'application/x-navi-animation';
    const APPLICATION__X_NETCDF = 'application/x-netcdf';
    const APPLICATION__X_NEWTON_COMPATIBLE_PKG = 'application/x-newton-compatible-pkg';
    const APPLICATION__X_NOKIA_9000_COMMUNICATOR_ADD_ON_SOFTWARE = 'application/x-nokia-9000-communicator-add-on-software';
    const APPLICATION__X_OMC = 'application/x-omc';
    const APPLICATION__X_OMCDATAMAKER = 'application/x-omcdatamaker';
    const APPLICATION__X_OMCREGERATOR = 'application/x-omcregerator';
    const APPLICATION__X_PAGEMAKER = 'application/x-pagemaker';
    const APPLICATION__X_PCL = 'application/x-pcl';
    const APPLICATION__X_PIXCLSCRIPT = 'application/x-pixclscript';
    const APPLICATION__X_PKCS10 = 'application/x-pkcs10';
    const APPLICATION__X_PKCS12 = 'application/x-pkcs12';
    const APPLICATION__X_PKCS7_CERTIFICATES = 'application/x-pkcs7-certificates';
    const APPLICATION__X_PKCS7_CERTREQRESP = 'application/x-pkcs7-certreqresp';
    const APPLICATION__X_PKCS7_MIME = 'application/x-pkcs7-mime';
    const APPLICATION__X_PKCS7_SIGNATURE = 'application/x-pkcs7-signature';
    const APPLICATION__X_POINTPLUS = 'application/x-pointplus';
    const APPLICATION__X_PORTABLE_ANYMAP = 'application/x-portable-anymap';
    const APPLICATION__X_PROJECT = 'application/x-project';
    const APPLICATION__X_QPRO = 'application/x-qpro';
    const APPLICATION__X_RTF = 'application/x-rtf';
    const APPLICATION__X_SDP = 'application/x-sdp';
    const APPLICATION__X_SEA = 'application/x-sea';
    const APPLICATION__X_SEELOGO = 'application/x-seelogo';
    const APPLICATION__X_SH = 'application/x-sh';
    const APPLICATION__X_SHAR = 'application/x-shar';
    const APPLICATION__X_SHOCKWAVE_FLASH = 'application/x-shockwave-flash';
    const APPLICATION__X_SIT = 'application/x-sit';
    const APPLICATION__X_SPRITE = 'application/x-sprite';
    const APPLICATION__X_STUFFIT = 'application/x-stuffit';
    const APPLICATION__X_SV4CPIO = 'application/x-sv4cpio';
    const APPLICATION__X_SV4CRC = 'application/x-sv4crc';
    const APPLICATION__X_TAR = 'application/x-tar';
    const APPLICATION__X_TBOOK = 'application/x-tbook';
    const APPLICATION__X_TCL = 'application/x-tcl';
    const APPLICATION__X_TEX = 'application/x-tex';
    const APPLICATION__X_TEXINFO = 'application/x-texinfo';
    const APPLICATION__X_TROFF = 'application/x-troff';
    const APPLICATION__X_TROFF_MAN = 'application/x-troff-man';
    const APPLICATION__X_TROFF_ME = 'application/x-troff-me';
    const APPLICATION__X_TROFF_MS = 'application/x-troff-ms';
    const APPLICATION__X_TROFF_MSVIDEO = 'application/x-troff-msvideo';
    const APPLICATION__X_USTAR = 'application/x-ustar';
    const APPLICATION__X_VISIO = 'application/x-visio';
    const APPLICATION__X_VND_AUDIOEXPLOSION_MZZ = 'application/x-vnd.audioexplosion.mzz';
    const APPLICATION__X_VND_LS_XPIX = 'application/x-vnd.ls-xpix';
    const APPLICATION__X_VRML = 'application/x-vrml';
    const APPLICATION__X_WAIS_SOURCE = 'application/x-wais-source';
    const APPLICATION__X_WINHELP = 'application/x-winhelp';
    const APPLICATION__X_WINTALK = 'application/x-wintalk';
    const APPLICATION__X_WORLD = 'application/x-world';
    const APPLICATION__X_WPWIN = 'application/x-wpwin';
    const APPLICATION__X_WRI = 'application/x-wri';
    const APPLICATION__X_X509_CA_CERT = 'application/x-x509-ca-cert';
    const APPLICATION__X_X509_USER_CERT = 'application/x-x509-user-cert';
    const APPLICATION__X_ZIP_COMPRESSED = 'application/x-zip-compressed';
    const APPLICATION__ZIP = 'application/zip';
    const AUDIO__AIFF = 'audio/aiff';
    const AUDIO__BASIC = 'audio/basic';
    const AUDIO__IT = 'audio/it';
    const AUDIO__MAKE = 'audio/make';
    const AUDIO__MAKE_MY_FUNK = 'audio/make.my.funk';
    const AUDIO__MID = 'audio/mid';
    const AUDIO__MIDI = 'audio/midi';
    const AUDIO__MOD = 'audio/mod';
    const AUDIO__MP4 = 'audio/mp4';
    const AUDIO__MPEG = 'audio/mpeg';
    const AUDIO__MPEG3 = 'audio/mpeg3';
    const AUDIO__NSPAUDIO = 'audio/nspaudio';
    const AUDIO__OGG = 'audio/ogg';
    const AUDIO__S3M = 'audio/s3m';
    const AUDIO__TSPLAYER = 'audio/tsplayer';
    const AUDIO__TSP_AUDIO = 'audio/tsp-audio';
    const AUDIO__VND_QCELP = 'audio/vnd.qcelp';
    const AUDIO__VOC = 'audio/voc';
    const AUDIO__VOXWARE = 'audio/voxware';
    const AUDIO__WAV = 'audio/wav';
    const AUDIO__XM = 'audio/xm';
    const AUDIO__X_ADPCM = 'audio/x-adpcm';
    const AUDIO__X_AIFF = 'audio/x-aiff';
    const AUDIO__X_AU = 'audio/x-au';
    const AUDIO__X_GSM = 'audio/x-gsm';
    const AUDIO__X_JAM = 'audio/x-jam';
    const AUDIO__X_LIVEAUDIO = 'audio/x-liveaudio';
    const AUDIO__X_MID = 'audio/x-mid';
    const AUDIO__X_MIDI = 'audio/x-midi';
    const AUDIO__X_MOD = 'audio/x-mod';
    const AUDIO__X_MPEG = 'audio/x-mpeg';
    const AUDIO__X_MPEG_3 = 'audio/x-mpeg-3';
    const AUDIO__X_MPEQURL = 'audio/x-mpequrl';
    const AUDIO__X_MS_WMA = 'audio/x-ms-wma';
    const AUDIO__X_NSPAUDIO = 'audio/x-nspaudio';
    const AUDIO__X_PN_REALAUDIO = 'audio/x-pn-realaudio';
    const AUDIO__X_PN_REALAUDIO_PLUGIN = 'audio/x-pn-realaudio-plugin';
    const AUDIO__X_PSID = 'audio/x-psid';
    const AUDIO__X_REALAUDIO = 'audio/x-realaudio';
    const AUDIO__X_TWINVQ = 'audio/x-twinvq';
    const AUDIO__X_TWINVQ_PLUGIN = 'audio/x-twinvq-plugin';
    const AUDIO__X_VND_AUDIOEXPLOSION_MJUICEMEDIAFILE = 'audio/x-vnd.audioexplosion.mjuicemediafile';
    const AUDIO__X_VOC = 'audio/x-voc';
    const AUDIO__X_WAV = 'audio/x-wav';
    const CHEMICAL__X_PDB = 'chemical/x-pdb';
    const IMAGE__ARW = 'image/ARW"';
    const IMAGE__BMP = 'image/bmp';
    const IMAGE__CMU_RASTER = 'image/cmu-raster';
    const IMAGE__CRW = 'image/CRW';
    const IMAGE__CR2 = 'image/CR2';
    const IMAGE__DNG = 'image/DNG';
    const IMAGE__FIF = 'image/fif';
    const IMAGE__FLORIAN = 'image/florian';
    const IMAGE__G3FAX = 'image/g3fax';
    const IMAGE__GIF = 'image/gif';
    const IMAGE__IEF = 'image/ief';
    const IMAGE__JPEG = 'image/jpeg';
    const IMAGE__JUTVISION = 'image/jutvision';
    const IMAGE__NAPLPS = 'image/naplps';
    const IMAGE__NEF = 'image/NEF';
    const IMAGE__PICT = 'image/pict';
    const IMAGE__PJPEG = 'image/pjpeg';
    const IMAGE__PNG = 'image/png';
    const IMAGE__SVG_XML = 'image/svg+xml';
    const IMAGE__TIFF = 'image/tiff';
    const IMAGE__VASA = 'image/vasa';
    const IMAGE__VND_DWG = 'image/vnd.dwg';
    const IMAGE__VND_FPX = 'image/vnd.fpx';
    const IMAGE__VND_NET_FPX = 'image/vnd.net-fpx';
    const IMAGE__VND_RN_REALFLASH = 'image/vnd.rn-realflash';
    const IMAGE__VND_RN_REALPIX = 'image/vnd.rn-realpix';
    const IMAGE__VND_WAP_WBMP = 'image/vnd.wap.wbmp';
    const IMAGE__VND_XIFF = 'image/vnd.xiff';
    const IMAGE__XBM = 'image/xbm';
    const IMAGE__XPM = 'image/xpm';
    const IMAGE__X_ADOBE_DNG = 'image/x-adobe-dng';
    const IMAGE__X_CANON_CRW = 'image/x-canon-crw';
    const IMAGE__X_CANON_CR2 = 'image/x-canon-cr2';
    const IMAGE__X_CMU_RASTER = 'image/x-cmu-raster';
    const IMAGE__X_DWG = 'image/x-dwg';
    const IMAGE__X_ICON = 'image/x-icon';
    const IMAGE__X_JG = 'image/x-jg';
    const IMAGE__X_JPS = 'image/x-jps';
    const IMAGE__X_MS_BMP = 'image/x-ms-bmp';
    const IMAGE__X_NIFF = 'image/x-niff';
    const IMAGE__X_NIKON_NEF = 'image/x-nikon-nef';
    const IMAGE__X_PCX = 'image/x-pcx';
    const IMAGE__X_PICT = 'image/x-pict';
    const IMAGE__X_PORTABLE_ANYMAP = 'image/x-portable-anymap';
    const IMAGE__X_PORTABLE_BITMAP = 'image/x-portable-bitmap';
    const IMAGE__X_PORTABLE_GRAYMAP = 'image/x-portable-graymap';
    const IMAGE__X_PORTABLE_GREYMAP = 'image/x-portable-greymap';
    const IMAGE__X_PORTABLE_PIXMAP = 'image/x-portable-pixmap';
    const IMAGE__X_QUICKTIME = 'image/x-quicktime';
    const IMAGE__X_RGB = 'image/x-rgb';
    const IMAGE__X_SONY_ARW = 'image/x-sony-arw';
    const IMAGE__X_TIFF = 'image/x-tiff';
    const IMAGE__X_WINDOWS_BMP = 'image/x-windows-bmp';
    const IMAGE__X_XBITMAP = 'image/x-xbitmap';
    const IMAGE__X_XBM = 'image/x-xbm';
    const IMAGE__X_XPIXMAP = 'image/x-xpixmap';
    const IMAGE__X_XWD = 'image/x-xwd';
    const IMAGE__X_XWINDOWDUMP = 'image/x-xwindowdump';
    const I_WORLD__I_VRML = 'i-world/i-vrml';
    const MESSAGE__RFC822 = 'message/rfc822';
    const MODEL__IGES = 'model/iges';
    const MODEL__VND_DWF = 'model/vnd.dwf';
    const MODEL__VRML = 'model/vrml';
    const MODEL__X_POV = 'model/x-pov';
    const MULTIPART__X_GZIP = 'multipart/x-gzip';
    const MULTIPART__X_USTAR = 'multipart/x-ustar';
    const MULTIPART__X_ZIP = 'multipart/x-zip';
    const MUSIC__CRESCENDO = 'music/crescendo';
    const MUSIC__X_KARAOKE = 'music/x-karaoke';
    const PALEOVU__X_PV = 'paleovu/x-pv';
    const TEXT__ASP = 'text/asp';
    const TEXT__CSS = 'text/css';
    const TEXT__CALENDAR = 'text/calendar';
    const TEXT__ECMASCRIPT = 'text/ecmascript';
    const TEXT__HTML = 'text/html';
    const TEXT__JAVASCRIPT = 'text/javascript';
    const TEXT__MARKDOWN = 'text/markdown';
    const TEXT__MCF = 'text/mcf';
    const TEXT__PASCAL = 'text/pascal';
    const TEXT__PLAIN = 'text/plain';
    const TEXT__RICHTEXT = 'text/richtext';
    const TEXT__SCRIPLET = 'text/scriplet';
    const TEXT__SGML = 'text/sgml';
    const TEXT__TAB_SEPARATED_VALUES = 'text/tab-separated-values';
    const TEXT__URI_LIST = 'text/uri-list';
    const TEXT__VND_ABC = 'text/vnd.abc';
    const TEXT__VND_FMI_FLEXSTOR = 'text/vnd.fmi.flexstor';
    const TEXT__VND_RN_REALTEXT = 'text/vnd.rn-realtext';
    const TEXT__VND_WAP_WML = 'text/vnd.wap.wml';
    const TEXT__VND_WAP_WMLSCRIPT = 'text/vnd.wap.wmlscript';
    const TEXT__VTT = 'text/vtt';
    const TEXT__WEBVIEWHTML = 'text/webviewhtml';
    const TEXT__XML = 'text/xml';
    const TEXT__X_ASM = 'text/x-asm';
    const TEXT__X_AUDIOSOFT_INTRA = 'text/x-audiosoft-intra';
    const TEXT__X_C = 'text/x-c';
    const TEXT__X_COMPONENT = 'text/x-component';
    const TEXT__X_FORTRAN = 'text/x-fortran';
    const TEXT__X_H = 'text/x-h';
    const TEXT__X_JAVA_SOURCE = 'text/x-java-source';
    const TEXT__X_LA_ASF = 'text/x-la-asf';
    const TEXT__X_M = 'text/x-m';
    const TEXT__X_PASCAL = 'text/x-pascal';
    const TEXT__X_SCRIPT = 'text/x-script';
    const TEXT__X_SCRIPT_CSH = 'text/x-script.csh';
    const TEXT__X_SCRIPT_ELISP = 'text/x-script.elisp';
    const TEXT__X_SCRIPT_GUILE = 'text/x-script.guile';
    const TEXT__X_SCRIPT_KSH = 'text/x-script.ksh';
    const TEXT__X_SCRIPT_LISP = 'text/x-script.lisp';
    const TEXT__X_SCRIPT_PERL = 'text/x-script.perl';
    const TEXT__X_SCRIPT_PERL_MODULE = 'text/x-script.perl-module';
    const TEXT__X_SCRIPT_PHYTON = 'text/x-script.phyton';
    const TEXT__X_SCRIPT_REXX = 'text/x-script.rexx';
    const TEXT__X_SCRIPT_SCHEME = 'text/x-script.scheme';
    const TEXT__X_SCRIPT_SH = 'text/x-script.sh';
    const TEXT__X_SCRIPT_TCL = 'text/x-script.tcl';
    const TEXT__X_SCRIPT_TCSH = 'text/x-script.tcsh';
    const TEXT__X_SCRIPT_ZSH = 'text/x-script.zsh';
    const TEXT__X_SERVER_PARSED_HTML = 'text/x-server-parsed-html';
    const TEXT__X_SETEXT = 'text/x-setext';
    const TEXT__X_SGML = 'text/x-sgml';
    const TEXT__X_SPEECH = 'text/x-speech';
    const TEXT__X_UIL = 'text/x-uil';
    const TEXT__X_UUENCODE = 'text/x-uuencode';
    const TEXT__X_VCALENDAR = 'text/x-vcalendar';
    const VIDEO__3_GPP = 'video/3gpp';
    const VIDEO__ANIMAFLEX = 'video/animaflex';
    const VIDEO__AVI = 'video/avi';
    const VIDEO__AVS_VIDEO = 'video/avs-video';
    const VIDEO__DL = 'video/dl';
    const VIDEO__FLI = 'video/fli';
    const VIDEO__GL = 'video/gl';
    const VIDEO__MPEG = 'video/mpeg';
    const VIDEO__MP4 = 'video/mp4';
    const VIDEO__MSVIDEO = 'video/msvideo';
    const VIDEO__OGG = 'video/ogg';
    const VIDEO__QUICKTIME = 'video/quicktime';
    const VIDEO__VDO = 'video/vdo';
    const VIDEO__VIMEO = 'video/vimeo';
    const VIDEO__VIVO = 'video/vivo';
    const VIDEO__VND_RN_REALVIDEO = 'video/vnd.rn-realvideo';
    const VIDEO__VND_VIVO = 'video/vnd.vivo';
    const VIDEO__VOSAIC = 'video/vosaic';
    const VIDEO__WEBM = 'video/webm';
    const VIDEO__X_AMT_DEMORUN = 'video/x-amt-demorun';
    const VIDEO__X_AMT_SHOWRUN = 'video/x-amt-showrun';
    const VIDEO__X_ATOMIC3D_FEATURE = 'video/x-atomic3d-feature';
    const VIDEO__X_DL = 'video/x-dl';
    const VIDEO__X_DV = 'video/x-dv';
    const VIDEO__X_FLI = 'video/x-fli';
    const VIDEO__X_FLV = 'video/x-flv';
    const VIDEO__X_GL = 'video/x-gl';
    const VIDEO__X_ISVIDEO = 'video/x-isvideo';
    const VIDEO__X_MOTION_JPEG = 'video/x-motion-jpeg';
    const VIDEO__X_MPEG = 'video/x-mpeg';
    const VIDEO__X_MPEQ2A = 'video/x-mpeq2a';
    const VIDEO__X_MSVIDEO = 'video/x-msvideo';
    const VIDEO__X_MS_ASF = 'video/x-ms-asf';
    const VIDEO__X_MS_ASF_PLUGIN = 'video/x-ms-asf-plugin';
    const VIDEO__X_MS_WM = 'video/x-ms-wm';
    const VIDEO__X_MS_WMD = 'video/x-ms-wmd';
    const VIDEO__X_MS_WMV = 'video/x-ms-wmv';
    const VIDEO__X_MS_WMX = 'video/x-ms-wmx';
    const VIDEO__X_MS_WMZ = 'video/x-ms-wmz';
    const VIDEO__X_MS_WVX = 'video/x-ms-wvx';
    const VIDEO__X_QTC = 'video/x-qtc';
    const VIDEO__X_SCM = 'video/x-scm';
    const VIDEO__X_SGI_MOVIE = 'video/x-sgi-movie';
    const VIDEO__YOUTUBE = 'video/youtube';
    const WINDOWS__METAFILE = 'windows/metafile';
    const WWW__MIME = 'www/mime';
    const XGL__DRAWING = 'xgl/drawing';
    const XGL__MOVIE = 'xgl/movie';
    const X_CONFERENCE__X_COOLTALK = 'x-conference/x-cooltalk';
    const X_MUSIC__X_MIDI = 'x-music/x-midi';
    const X_WORLD__X_3DMF = 'x-world/x-3dmf';
    const X_WORLD__X_SVR = 'x-world/x-svr';
    const X_WORLD__X_VRML = 'x-world/x-vrml';
    const X_WORLD__X_VRT = 'x-world/x-vrt';
    
    protected string $path = '';
    protected string $suffix = '';
    protected bool $external = false;
    protected string $fallback = self::APPLICATION__OCTET_STREAM;
    
    protected function __construct(string $path_to_file)
    {
        /** @noinspection HttpUrlsUsage */
        if (strpos($path_to_file, 'http://') !== false || strpos($path_to_file, 'https://') !== false) {
            $this->setExternal(true);
        }
        $parts = parse_url($path_to_file);
        $this->setPath($path_to_file);
        $this->setSuffix(pathinfo($parts['path'] ?? "", PATHINFO_EXTENSION));
    }
    
    /**
     * @return array<string, mixed>
     */
    public static function getExt2MimeMap() : array
    {
        /** @noRector */
        $suffix_map = include "mime_type_map.php";
        $map = [];
        foreach ($suffix_map as $k => $v) {
            $type = is_array($v) ? $v[0] : $v;
            $map['.' . $k] = $type;
        }
        
        return $map;
    }
    
    /**
     *
     * @deprecated use ILIAS\FileUpload\MimeType::lookupMimeType() instead
     */
    public static function getMimeType(string $a_file = '', string $a_filename = '', string $a_mime = '') : string
    {
        $path = '';
        if ($a_filename !== '' && $a_filename !== '0') {
            $path = $a_filename;
        } elseif ($a_file !== '' && $a_file !== '0') {
            $path = $a_file;
        }
        
        return self::lookupMimeType($path, $a_mime);
    }
    
    public static function lookupMimeType(
        string $path_to_file,
        string $fallback = self::APPLICATION__OCTET_STREAM,
        bool $a_external = false
    ) : string {
        $obj = new self($path_to_file);
        if ($a_external) {
            $obj->setExternal($a_external);
        }
        $obj->setFallback($fallback);
        
        return $obj->get();
    }
    
    public function get() : string
    {
        /** @noRector */
        $suffix_map = include "mime_type_map.php";
        if ($this->isExternal()) {
            if (is_int(strpos($this->getPath(), 'youtube.')) ||
                is_int(strpos($this->getPath(), 'youtu.be'))
            ) {
                return self::VIDEO__YOUTUBE;
            }
            if (is_int(strpos($this->getPath(), 'vimeo.'))) {
                return self::VIDEO__VIMEO;
            }
        }
        if ($this->getSuffix() !== '' && $this->getSuffix() !== '0' && isset($suffix_map[$this->getSuffix()])) {
            if (!is_array($suffix_map[$this->getSuffix()])) {
                return $suffix_map[$this->getSuffix()];
            } else {
                return $suffix_map[$this->getSuffix()][0];
            }
        }
        if (extension_loaded('Fileinfo') && is_file($this->getPath())) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $info = finfo_file($finfo, $this->getPath());
            finfo_close($finfo);
            if ($info) {
                return $info;
            }
        }
        
        return $this->getFallback();
    }
    
    protected function isExternal() : bool
    {
        return $this->external;
    }
    
    public function setExternal(bool $external) : void
    {
        $this->external = $external;
    }
    
    protected function getPath() : string
    {
        return $this->path;
    }
    
    protected function setPath(string $path) : void
    {
        $this->path = $path;
    }
    
    protected function getSuffix() : string
    {
        return $this->suffix;
    }
    
    protected function setSuffix(string $suffix) : void
    {
        // see #18157
        $this->suffix = strtolower($suffix);
    }
    
    protected function getFallback() : string
    {
        return $this->fallback;
    }
    
    public function setFallback(string $fallback) : void
    {
        $this->fallback = $fallback;
    }
}
