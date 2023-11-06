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
    public const APPLICATION__ACAD = 'application/acad';
    public const APPLICATION__ARJ = 'application/arj';
    public const APPLICATION__ASTOUND = 'application/astound';
    public const APPLICATION__BASE64 = 'application/base64';
    public const APPLICATION__BINHEX = 'application/binhex';
    public const APPLICATION__BINHEX4 = 'application/binhex4';
    public const APPLICATION__BOOK = 'application/book';
    public const APPLICATION__CDF = 'application/cdf';
    public const APPLICATION__CLARISCAD = 'application/clariscad';
    public const APPLICATION__COMMONGROUND = 'application/commonground';
    public const APPLICATION__DRAFTING = 'application/drafting';
    public const APPLICATION__DSPTYPE = 'application/dsptype';
    public const APPLICATION__DXF = 'application/dxf';
    public const APPLICATION__ECMASCRIPT = 'application/ecmascript';
    public const APPLICATION__ENVOY = 'application/envoy';
    public const APPLICATION__EPUB = 'application/epub+zip';
    public const APPLICATION__EXCEL = 'application/excel';
    public const APPLICATION__FONT_WOFF = 'application/font-woff';
    public const APPLICATION__FRACTALS = 'application/fractals';
    public const APPLICATION__FREELOADER = 'application/freeloader';
    public const APPLICATION__FUTURESPLASH = 'application/futuresplash';
    public const APPLICATION__GNUTAR = 'application/gnutar';
    public const APPLICATION__GROUPWISE = 'application/groupwise';
    public const APPLICATION__HLP = 'application/hlp';
    public const APPLICATION__HTA = 'application/hta';
    public const APPLICATION__IGES = 'application/iges';
    public const APPLICATION__INF = 'application/inf';
    public const APPLICATION__I_DEAS = 'application/i-deas';
    public const APPLICATION__JAVA = 'application/java';
    public const APPLICATION__JAVASCRIPT = 'application/javascript';
    public const APPLICATION__JAVA_BYTE_CODE = 'application/java-byte-code';
    public const APPLICATION__JSON = 'application/json';
    public const APPLICATION__LHA = 'application/lha';
    public const APPLICATION__LZX = 'application/lzx';
    public const APPLICATION__MACBINARY = 'application/macbinary';
    public const APPLICATION__MAC_BINARY = 'application/mac-binary';
    public const APPLICATION__MAC_BINHEX = 'application/mac-binhex';
    public const APPLICATION__MAC_BINHEX40 = 'application/mac-binhex40';
    public const APPLICATION__MAC_COMPACTPRO = 'application/mac-compactpro';
    public const APPLICATION__MARC = 'application/marc';
    public const APPLICATION__MBEDLET = 'application/mbedlet';
    public const APPLICATION__MCAD = 'application/mcad';
    public const APPLICATION__MIME = 'application/mime';
    public const APPLICATION__MSPOWERPOINT = 'application/mspowerpoint';
    public const APPLICATION__MSWORD = 'application/msword';
    public const APPLICATION__MSWRITE = 'application/mswrite';
    public const APPLICATION__NETMC = 'application/netmc';
    public const APPLICATION__OCTET_STREAM = 'application/octet-stream';
    public const APPLICATION__ODA = 'application/oda';
    public const APPLICATION__OGG = 'application/ogg';
    public const APPLICATION__PDF = 'application/pdf';
    public const APPLICATION__PKCS10 = 'application/pkcs10';
    public const APPLICATION__PKCS7_MIME = 'application/pkcs7-mime';
    public const APPLICATION__PKCS7_SIGNATURE = 'application/pkcs7-signature';
    public const APPLICATION__PKCS_12 = 'application/pkcs-12';
    public const APPLICATION__PKCS_CRL = 'application/pkcs-crl';
    public const APPLICATION__PKIX_CERT = 'application/pkix-cert';
    public const APPLICATION__PKIX_CRL = 'application/pkix-crl';
    public const APPLICATION__PLAIN = 'application/plain';
    public const APPLICATION__POSTSCRIPT = 'application/postscript';
    public const APPLICATION__POWERPOINT = 'application/powerpoint';
    public const APPLICATION__PRO_ENG = 'application/pro_eng';
    public const APPLICATION__RINGING_TONES = 'application/ringing-tones';
    public const APPLICATION__RTF = 'application/rtf';
    public const APPLICATION__SDP = 'application/sdp';
    public const APPLICATION__SEA = 'application/sea';
    public const APPLICATION__SEB = 'application/seb';
    public const APPLICATION__SET = 'application/set';
    public const APPLICATION__SLA = 'application/sla';
    public const APPLICATION__SMIL = 'application/smil';
    public const APPLICATION__SOLIDS = 'application/solids';
    public const APPLICATION__SOUNDER = 'application/sounder';
    public const APPLICATION__STEP = 'application/step';
    public const APPLICATION__STREAMINGMEDIA = 'application/streamingmedia';
    public const APPLICATION__TOOLBOOK = 'application/toolbook';
    public const APPLICATION__VDA = 'application/vda';
    public const APPLICATION__VND_FDF = 'application/vnd.fdf';
    public const APPLICATION__VND_HP_HPGL = 'application/vnd.hp-hpgl';
    public const APPLICATION__VND_HP_PCL = 'application/vnd.hp-pcl';
    public const APPLICATION__VND_MS_EXCEL = 'application/vnd.ms-excel';
    public const APPLICATION__VND_MS_EXCEL_ADDIN_MACRO_ENABLED_12 = 'application/vnd.ms-excel.addin.macroEnabled.12';
    public const APPLICATION__VND_MS_EXCEL_SHEET_BINARY_MACRO_ENABLED_12 = 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
    public const APPLICATION__VND_MS_PKI_CERTSTORE = 'application/vnd.ms-pki.certstore';
    public const APPLICATION__VND_MS_PKI_PKO = 'application/vnd.ms-pki.pko';
    public const APPLICATION__VND_MS_PKI_SECCAT = 'application/vnd.ms-pki.seccat';
    public const APPLICATION__VND_MS_PKI_STL = 'application/vnd.ms-pki.stl';
    public const APPLICATION__VND_MS_POWERPOINT = 'application/vnd.ms-powerpoint';
    public const APPLICATION__VND_MS_PROJECT = 'application/vnd.ms-project';
    public const APPLICATION__VND_NOKIA_CONFIGURATION_MESSAGE = 'application/vnd.nokia.configuration-message';
    public const APPLICATION__VND_NOKIA_RINGING_TONE = 'application/vnd.nokia.ringing-tone';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_PRESENTATION = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_SLIDE = 'application/vnd.openxmlformats-officedocument.presentationml.slide';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_SLIDESHOW = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_TEMPLATE = 'application/vnd.openxmlformats-officedocument.presentationml.template';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_TEMPLATE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_DOCUMENT = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    public const APPLICATION__VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_TEMPLATE = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
    public const APPLICATION__VND_RN_REALMEDIA = 'application/vnd.rn-realmedia';
    public const APPLICATION__VND_RN_REALPLAYER = 'application/vnd.rn-realplayer';
    public const APPLICATION__VND_WAP_WMLC = 'application/vnd.wap.wmlc';
    public const APPLICATION__VND_WAP_WMLSCRIPTC = 'application/vnd.wap.wmlscriptc';
    public const APPLICATION__VND_XARA = 'application/vnd.xara';
    public const APPLICATION__VOCALTEC_MEDIA_DESC = 'application/vocaltec-media-desc';
    public const APPLICATION__VOCALTEC_MEDIA_FILE = 'application/vocaltec-media-file';
    public const APPLICATION__WORDPERFECT = 'application/wordperfect';
    public const APPLICATION__WORDPERFECT6_0 = 'application/wordperfect6.0';
    public const APPLICATION__WORDPERFECT6_1 = 'application/wordperfect6.1';
    public const APPLICATION__XHTML_XML = 'application/xhtml+xml';
    public const APPLICATION__XML = 'application/xml';
    public const APPLICATION__X_123 = 'application/x-123';
    public const APPLICATION__X_AIM = 'application/x-aim';
    public const APPLICATION__X_AUTHORWARE_BIN = 'application/x-authorware-bin';
    public const APPLICATION__X_AUTHORWARE_MAP = 'application/x-authorware-map';
    public const APPLICATION__X_AUTHORWARE_SEG = 'application/x-authorware-seg';
    public const APPLICATION__X_BCPIO = 'application/x-bcpio';
    public const APPLICATION__X_BINARY = 'application/x-binary';
    public const APPLICATION__X_BINHEX40 = 'application/x-binhex40';
    public const APPLICATION__X_BSH = 'application/x-bsh';
    public const APPLICATION__X_BYTECODE_PYTHON = 'application/x-bytecode.python';
    public const APPLICATION__X_BZIP = 'application/x-bzip';
    public const APPLICATION__X_BZIP2 = 'application/x-bzip2';
    public const APPLICATION__X_CDF = 'application/x-cdf';
    public const APPLICATION__X_CDLINK = 'application/x-cdlink';
    public const APPLICATION__X_CHAT = 'application/x-chat';
    public const APPLICATION__X_CMU_RASTER = 'application/x-cmu-raster';
    public const APPLICATION__X_COCOA = 'application/x-cocoa';
    public const APPLICATION__X_COMPACTPRO = 'application/x-compactpro';
    public const APPLICATION__X_COMPRESS = 'application/x-compress';
    public const APPLICATION__X_COMPRESSED = 'application/x-compressed';
    public const APPLICATION__X_CONFERENCE = 'application/x-conference';
    public const APPLICATION__X_CPIO = 'application/x-cpio';
    public const APPLICATION__X_CPT = 'application/x-cpt';
    public const APPLICATION__X_CSH = 'application/x-csh';
    public const APPLICATION__X_DEEPV = 'application/x-deepv';
    public const APPLICATION__X_DIRECTOR = 'application/x-director';
    public const APPLICATION__X_DVI = 'application/x-dvi';
    public const APPLICATION__X_ELC = 'application/x-elc';
    public const APPLICATION__X_ENVOY = 'application/x-envoy';
    public const APPLICATION__X_ESREHBER = 'application/x-esrehber';
    public const APPLICATION__X_EXCEL = 'application/x-excel';
    public const APPLICATION__X_FRAME = 'application/x-frame';
    public const APPLICATION__X_FREELANCE = 'application/x-freelance';
    public const APPLICATION__X_GSP = 'application/x-gsp';
    public const APPLICATION__X_GSS = 'application/x-gss';
    public const APPLICATION__X_GTAR = 'application/x-gtar';
    public const APPLICATION__X_GZIP = 'application/x-gzip';
    public const APPLICATION__X_HDF = 'application/x-hdf';
    public const APPLICATION__X_HELPFILE = 'application/x-helpfile';
    public const APPLICATION__X_HTTPD_IMAP = 'application/x-httpd-imap';
    public const APPLICATION__X_IMA = 'application/x-ima';
    public const APPLICATION__X_INTERNETT_SIGNUP = 'application/x-internett-signup';
    public const APPLICATION__X_INVENTOR = 'application/x-inventor';
    public const APPLICATION__X_IP2 = 'application/x-ip2';
    public const APPLICATION__X_JAVASCRIPT = 'application/x-javascript';
    public const APPLICATION__X_JAVA_APPLET = 'application/x-java-applet';
    public const APPLICATION__X_JAVA_CLASS = 'application/x-java-class';
    public const APPLICATION__X_JAVA_COMMERCE = 'application/x-java-commerce';
    public const APPLICATION__X_KOAN = 'application/x-koan';
    public const APPLICATION__X_KSH = 'application/x-ksh';
    public const APPLICATION__X_LATEX = 'application/x-latex';
    public const APPLICATION__X_LHA = 'application/x-lha';
    public const APPLICATION__X_LISP = 'application/x-lisp';
    public const APPLICATION__X_LIVESCREEN = 'application/x-livescreen';
    public const APPLICATION__X_LOTUS = 'application/x-lotus';
    public const APPLICATION__X_LOTUSSCREENCAM = 'application/x-lotusscreencam';
    public const APPLICATION__X_LZH = 'application/x-lzh';
    public const APPLICATION__X_LZX = 'application/x-lzx';
    public const APPLICATION__X_MACBINARY = 'application/x-macbinary';
    public const APPLICATION__X_MAC_BINHEX40 = 'application/x-mac-binhex40';
    public const APPLICATION__X_MAGIC_CAP_PACKAGE_1_0 = 'application/x-magic-cap-package-1.0';
    public const APPLICATION__X_MATHCAD = 'application/x-mathcad';
    public const APPLICATION__X_MOBI = 'application/x-mobipocket-ebook';
    public const APPLICATION__X_MEME = 'application/x-meme';
    public const APPLICATION__X_MIDI = 'application/x-midi';
    public const APPLICATION__X_MIF = 'application/x-mif';
    public const APPLICATION__X_MIX_TRANSFER = 'application/x-mix-transfer';
    public const APPLICATION__X_MPLAYER2 = 'application/x-mplayer2';
    public const APPLICATION__X_MSEXCEL = 'application/x-msexcel';
    public const APPLICATION__X_MSPOWERPOINT = 'application/x-mspowerpoint';
    public const APPLICATION__X_NAVIDOC = 'application/x-navidoc';
    public const APPLICATION__X_NAVIMAP = 'application/x-navimap';
    public const APPLICATION__X_NAVISTYLE = 'application/x-navistyle';
    public const APPLICATION__X_NAVI_ANIMATION = 'application/x-navi-animation';
    public const APPLICATION__X_NETCDF = 'application/x-netcdf';
    public const APPLICATION__X_NEWTON_COMPATIBLE_PKG = 'application/x-newton-compatible-pkg';
    public const APPLICATION__X_NOKIA_9000_COMMUNICATOR_ADD_ON_SOFTWARE = 'application/x-nokia-9000-communicator-add-on-software';
    public const APPLICATION__X_OMC = 'application/x-omc';
    public const APPLICATION__X_OMCDATAMAKER = 'application/x-omcdatamaker';
    public const APPLICATION__X_OMCREGERATOR = 'application/x-omcregerator';
    public const APPLICATION__X_PAGEMAKER = 'application/x-pagemaker';
    public const APPLICATION__X_PCL = 'application/x-pcl';
    public const APPLICATION__X_PIXCLSCRIPT = 'application/x-pixclscript';
    public const APPLICATION__X_PKCS10 = 'application/x-pkcs10';
    public const APPLICATION__X_PKCS12 = 'application/x-pkcs12';
    public const APPLICATION__X_PKCS7_CERTIFICATES = 'application/x-pkcs7-certificates';
    public const APPLICATION__X_PKCS7_CERTREQRESP = 'application/x-pkcs7-certreqresp';
    public const APPLICATION__X_PKCS7_MIME = 'application/x-pkcs7-mime';
    public const APPLICATION__X_PKCS7_SIGNATURE = 'application/x-pkcs7-signature';
    public const APPLICATION__X_POINTPLUS = 'application/x-pointplus';
    public const APPLICATION__X_PORTABLE_ANYMAP = 'application/x-portable-anymap';
    public const APPLICATION__X_PROJECT = 'application/x-project';
    public const APPLICATION__X_QPRO = 'application/x-qpro';
    public const APPLICATION__X_RTF = 'application/x-rtf';
    public const APPLICATION__X_SDP = 'application/x-sdp';
    public const APPLICATION__X_SEA = 'application/x-sea';
    public const APPLICATION__X_SEELOGO = 'application/x-seelogo';
    public const APPLICATION__X_SH = 'application/x-sh';
    public const APPLICATION__X_SHAR = 'application/x-shar';
    public const APPLICATION__X_SHOCKWAVE_FLASH = 'application/x-shockwave-flash';
    public const APPLICATION__X_SIT = 'application/x-sit';
    public const APPLICATION__X_SPRITE = 'application/x-sprite';
    public const APPLICATION__X_STUFFIT = 'application/x-stuffit';
    public const APPLICATION__X_SV4CPIO = 'application/x-sv4cpio';
    public const APPLICATION__X_SV4CRC = 'application/x-sv4crc';
    public const APPLICATION__X_TAR = 'application/x-tar';
    public const APPLICATION__X_TBOOK = 'application/x-tbook';
    public const APPLICATION__X_TCL = 'application/x-tcl';
    public const APPLICATION__X_TEX = 'application/x-tex';
    public const APPLICATION__X_TEXINFO = 'application/x-texinfo';
    public const APPLICATION__X_TROFF = 'application/x-troff';
    public const APPLICATION__X_TROFF_MAN = 'application/x-troff-man';
    public const APPLICATION__X_TROFF_ME = 'application/x-troff-me';
    public const APPLICATION__X_TROFF_MS = 'application/x-troff-ms';
    public const APPLICATION__X_TROFF_MSVIDEO = 'application/x-troff-msvideo';
    public const APPLICATION__X_USTAR = 'application/x-ustar';
    public const APPLICATION__X_VISIO = 'application/x-visio';
    public const APPLICATION__X_VND_AUDIOEXPLOSION_MZZ = 'application/x-vnd.audioexplosion.mzz';
    public const APPLICATION__X_VND_LS_XPIX = 'application/x-vnd.ls-xpix';
    public const APPLICATION__X_VRML = 'application/x-vrml';
    public const APPLICATION__X_WAIS_SOURCE = 'application/x-wais-source';
    public const APPLICATION__X_WINHELP = 'application/x-winhelp';
    public const APPLICATION__X_WINTALK = 'application/x-wintalk';
    public const APPLICATION__X_WORLD = 'application/x-world';
    public const APPLICATION__X_WPWIN = 'application/x-wpwin';
    public const APPLICATION__X_WRI = 'application/x-wri';
    public const APPLICATION__X_X509_CA_CERT = 'application/x-x509-ca-cert';
    public const APPLICATION__X_X509_USER_CERT = 'application/x-x509-user-cert';
    public const APPLICATION__X_ZIP_COMPRESSED = 'application/x-zip-compressed';
    public const APPLICATION__ZIP = 'application/zip';
    public const AUDIO__AIFF = 'audio/aiff';
    public const AUDIO__BASIC = 'audio/basic';
    public const AUDIO__IT = 'audio/it';
    public const AUDIO__MAKE = 'audio/make';
    public const AUDIO__MAKE_MY_FUNK = 'audio/make.my.funk';
    public const AUDIO__MID = 'audio/mid';
    public const AUDIO__MIDI = 'audio/midi';
    public const AUDIO__MOD = 'audio/mod';
    public const AUDIO__MP4 = 'audio/mp4';
    public const AUDIO__MPEG = 'audio/mpeg';
    public const AUDIO__MPEG3 = 'audio/mpeg3';
    public const AUDIO__NSPAUDIO = 'audio/nspaudio';
    public const AUDIO__OGG = 'audio/ogg';
    public const AUDIO__S3M = 'audio/s3m';
    public const AUDIO__TSPLAYER = 'audio/tsplayer';
    public const AUDIO__TSP_AUDIO = 'audio/tsp-audio';
    public const AUDIO__VND_QCELP = 'audio/vnd.qcelp';
    public const AUDIO__VOC = 'audio/voc';
    public const AUDIO__VOXWARE = 'audio/voxware';
    public const AUDIO__WAV = 'audio/wav';
    public const AUDIO__XM = 'audio/xm';
    public const AUDIO__X_ADPCM = 'audio/x-adpcm';
    public const AUDIO__X_AIFF = 'audio/x-aiff';
    public const AUDIO__X_AU = 'audio/x-au';
    public const AUDIO__X_GSM = 'audio/x-gsm';
    public const AUDIO__X_JAM = 'audio/x-jam';
    public const AUDIO__X_LIVEAUDIO = 'audio/x-liveaudio';
    public const AUDIO__X_MID = 'audio/x-mid';
    public const AUDIO__X_MIDI = 'audio/x-midi';
    public const AUDIO__X_MOD = 'audio/x-mod';
    public const AUDIO__X_MPEG = 'audio/x-mpeg';
    public const AUDIO__X_MPEG_3 = 'audio/x-mpeg-3';
    public const AUDIO__X_MPEQURL = 'audio/x-mpequrl';
    public const AUDIO__X_MS_WMA = 'audio/x-ms-wma';
    public const AUDIO__X_NSPAUDIO = 'audio/x-nspaudio';
    public const AUDIO__X_PN_REALAUDIO = 'audio/x-pn-realaudio';
    public const AUDIO__X_PN_REALAUDIO_PLUGIN = 'audio/x-pn-realaudio-plugin';
    public const AUDIO__X_PSID = 'audio/x-psid';
    public const AUDIO__X_REALAUDIO = 'audio/x-realaudio';
    public const AUDIO__X_TWINVQ = 'audio/x-twinvq';
    public const AUDIO__X_TWINVQ_PLUGIN = 'audio/x-twinvq-plugin';
    public const AUDIO__X_VND_AUDIOEXPLOSION_MJUICEMEDIAFILE = 'audio/x-vnd.audioexplosion.mjuicemediafile';
    public const AUDIO__X_VOC = 'audio/x-voc';
    public const AUDIO__X_WAV = 'audio/x-wav';
    public const CHEMICAL__X_PDB = 'chemical/x-pdb';
    public const IMAGE__ARW = 'image/ARW"';
    public const IMAGE__BMP = 'image/bmp';
    public const IMAGE__CMU_RASTER = 'image/cmu-raster';
    public const IMAGE__CRW = 'image/CRW';
    public const IMAGE__CR2 = 'image/CR2';
    public const IMAGE__DNG = 'image/DNG';
    public const IMAGE__FIF = 'image/fif';
    public const IMAGE__FLORIAN = 'image/florian';
    public const IMAGE__G3FAX = 'image/g3fax';
    public const IMAGE__GIF = 'image/gif';
    public const IMAGE__IEF = 'image/ief';
    public const IMAGE__JPEG = 'image/jpeg';
    public const IMAGE__JUTVISION = 'image/jutvision';
    public const IMAGE__NAPLPS = 'image/naplps';
    public const IMAGE__NEF = 'image/NEF';
    public const IMAGE__PICT = 'image/pict';
    public const IMAGE__PJPEG = 'image/pjpeg';
    public const IMAGE__PNG = 'image/png';
    public const IMAGE__SVG_XML = 'image/svg+xml';
    public const IMAGE__TIFF = 'image/tiff';
    public const IMAGE__VASA = 'image/vasa';
    public const IMAGE__VND_DWG = 'image/vnd.dwg';
    public const IMAGE__VND_FPX = 'image/vnd.fpx';
    public const IMAGE__VND_NET_FPX = 'image/vnd.net-fpx';
    public const IMAGE__VND_RN_REALFLASH = 'image/vnd.rn-realflash';
    public const IMAGE__VND_RN_REALPIX = 'image/vnd.rn-realpix';
    public const IMAGE__VND_WAP_WBMP = 'image/vnd.wap.wbmp';
    public const IMAGE__VND_XIFF = 'image/vnd.xiff';
    public const IMAGE__XBM = 'image/xbm';
    public const IMAGE__XPM = 'image/xpm';
    public const IMAGE__X_ADOBE_DNG = 'image/x-adobe-dng';
    public const IMAGE__X_CANON_CRW = 'image/x-canon-crw';
    public const IMAGE__X_CANON_CR2 = 'image/x-canon-cr2';
    public const IMAGE__X_CMU_RASTER = 'image/x-cmu-raster';
    public const IMAGE__X_DWG = 'image/x-dwg';
    public const IMAGE__X_ICON = 'image/x-icon';
    public const IMAGE__X_JG = 'image/x-jg';
    public const IMAGE__X_JPS = 'image/x-jps';
    public const IMAGE__X_MS_BMP = 'image/x-ms-bmp';
    public const IMAGE__X_NIFF = 'image/x-niff';
    public const IMAGE__X_NIKON_NEF = 'image/x-nikon-nef';
    public const IMAGE__X_PCX = 'image/x-pcx';
    public const IMAGE__X_PICT = 'image/x-pict';
    public const IMAGE__X_PORTABLE_ANYMAP = 'image/x-portable-anymap';
    public const IMAGE__X_PORTABLE_BITMAP = 'image/x-portable-bitmap';
    public const IMAGE__X_PORTABLE_GRAYMAP = 'image/x-portable-graymap';
    public const IMAGE__X_PORTABLE_GREYMAP = 'image/x-portable-greymap';
    public const IMAGE__X_PORTABLE_PIXMAP = 'image/x-portable-pixmap';
    public const IMAGE__X_QUICKTIME = 'image/x-quicktime';
    public const IMAGE__X_RGB = 'image/x-rgb';
    public const IMAGE__X_SONY_ARW = 'image/x-sony-arw';
    public const IMAGE__X_TIFF = 'image/x-tiff';
    public const IMAGE__X_WINDOWS_BMP = 'image/x-windows-bmp';
    public const IMAGE__X_XBITMAP = 'image/x-xbitmap';
    public const IMAGE__X_XBM = 'image/x-xbm';
    public const IMAGE__X_XPIXMAP = 'image/x-xpixmap';
    public const IMAGE__X_XWD = 'image/x-xwd';
    public const IMAGE__X_XWINDOWDUMP = 'image/x-xwindowdump';
    public const I_WORLD__I_VRML = 'i-world/i-vrml';
    public const MESSAGE__RFC822 = 'message/rfc822';
    public const MODEL__IGES = 'model/iges';
    public const MODEL__VND_DWF = 'model/vnd.dwf';
    public const MODEL__VRML = 'model/vrml';
    public const MODEL__X_POV = 'model/x-pov';
    public const MULTIPART__X_GZIP = 'multipart/x-gzip';
    public const MULTIPART__X_USTAR = 'multipart/x-ustar';
    public const MULTIPART__X_ZIP = 'multipart/x-zip';
    public const MUSIC__CRESCENDO = 'music/crescendo';
    public const MUSIC__X_KARAOKE = 'music/x-karaoke';
    public const PALEOVU__X_PV = 'paleovu/x-pv';
    public const TEXT__ASP = 'text/asp';
    public const TEXT__CSS = 'text/css';
    public const TEXT__CALENDAR = 'text/calendar';
    public const TEXT__ECMASCRIPT = 'text/ecmascript';
    public const TEXT__HTML = 'text/html';
    public const TEXT__JAVASCRIPT = 'text/javascript';
    public const TEXT__MARKDOWN = 'text/markdown';
    public const TEXT__MCF = 'text/mcf';
    public const TEXT__PASCAL = 'text/pascal';
    public const TEXT__PLAIN = 'text/plain';
    public const TEXT__RICHTEXT = 'text/richtext';
    public const TEXT__SCRIPLET = 'text/scriplet';
    public const TEXT__SGML = 'text/sgml';
    public const TEXT__TAB_SEPARATED_VALUES = 'text/tab-separated-values';
    public const TEXT__URI_LIST = 'text/uri-list';
    public const TEXT__VND_ABC = 'text/vnd.abc';
    public const TEXT__VND_FMI_FLEXSTOR = 'text/vnd.fmi.flexstor';
    public const TEXT__VND_RN_REALTEXT = 'text/vnd.rn-realtext';
    public const TEXT__VND_WAP_WML = 'text/vnd.wap.wml';
    public const TEXT__VND_WAP_WMLSCRIPT = 'text/vnd.wap.wmlscript';
    public const TEXT__VTT = 'text/vtt';
    public const TEXT__WEBVIEWHTML = 'text/webviewhtml';
    public const TEXT__XML = 'text/xml';
    public const TEXT__X_ASM = 'text/x-asm';
    public const TEXT__X_AUDIOSOFT_INTRA = 'text/x-audiosoft-intra';
    public const TEXT__X_C = 'text/x-c';
    public const TEXT__X_COMPONENT = 'text/x-component';
    public const TEXT__X_FORTRAN = 'text/x-fortran';
    public const TEXT__X_H = 'text/x-h';
    public const TEXT__X_JAVA_SOURCE = 'text/x-java-source';
    public const TEXT__X_LA_ASF = 'text/x-la-asf';
    public const TEXT__X_M = 'text/x-m';
    public const TEXT__X_PASCAL = 'text/x-pascal';
    public const TEXT__X_SCRIPT = 'text/x-script';
    public const TEXT__X_SCRIPT_CSH = 'text/x-script.csh';
    public const TEXT__X_SCRIPT_ELISP = 'text/x-script.elisp';
    public const TEXT__X_SCRIPT_GUILE = 'text/x-script.guile';
    public const TEXT__X_SCRIPT_KSH = 'text/x-script.ksh';
    public const TEXT__X_SCRIPT_LISP = 'text/x-script.lisp';
    public const TEXT__X_SCRIPT_PERL = 'text/x-script.perl';
    public const TEXT__X_SCRIPT_PERL_MODULE = 'text/x-script.perl-module';
    public const TEXT__X_SCRIPT_PHYTON = 'text/x-script.phyton';
    public const TEXT__X_SCRIPT_REXX = 'text/x-script.rexx';
    public const TEXT__X_SCRIPT_SCHEME = 'text/x-script.scheme';
    public const TEXT__X_SCRIPT_SH = 'text/x-script.sh';
    public const TEXT__X_SCRIPT_TCL = 'text/x-script.tcl';
    public const TEXT__X_SCRIPT_TCSH = 'text/x-script.tcsh';
    public const TEXT__X_SCRIPT_ZSH = 'text/x-script.zsh';
    public const TEXT__X_SERVER_PARSED_HTML = 'text/x-server-parsed-html';
    public const TEXT__X_SETEXT = 'text/x-setext';
    public const TEXT__X_SGML = 'text/x-sgml';
    public const TEXT__X_SPEECH = 'text/x-speech';
    public const TEXT__X_UIL = 'text/x-uil';
    public const TEXT__X_UUENCODE = 'text/x-uuencode';
    public const TEXT__X_VCALENDAR = 'text/x-vcalendar';
    public const VIDEO__3_GPP = 'video/3gpp';
    public const VIDEO__ANIMAFLEX = 'video/animaflex';
    public const VIDEO__AVI = 'video/avi';
    public const VIDEO__AVS_VIDEO = 'video/avs-video';
    public const VIDEO__DL = 'video/dl';
    public const VIDEO__FLI = 'video/fli';
    public const VIDEO__GL = 'video/gl';
    public const VIDEO__MPEG = 'video/mpeg';
    public const VIDEO__MP4 = 'video/mp4';
    public const VIDEO__MSVIDEO = 'video/msvideo';
    public const VIDEO__OGG = 'video/ogg';
    public const VIDEO__QUICKTIME = 'video/quicktime';
    public const VIDEO__VDO = 'video/vdo';
    public const VIDEO__VIMEO = 'video/vimeo';
    public const VIDEO__VIVO = 'video/vivo';
    public const VIDEO__VND_RN_REALVIDEO = 'video/vnd.rn-realvideo';
    public const VIDEO__VND_VIVO = 'video/vnd.vivo';
    public const VIDEO__VOSAIC = 'video/vosaic';
    public const VIDEO__WEBM = 'video/webm';
    public const VIDEO__X_AMT_DEMORUN = 'video/x-amt-demorun';
    public const VIDEO__X_AMT_SHOWRUN = 'video/x-amt-showrun';
    public const VIDEO__X_ATOMIC3D_FEATURE = 'video/x-atomic3d-feature';
    public const VIDEO__X_DL = 'video/x-dl';
    public const VIDEO__X_DV = 'video/x-dv';
    public const VIDEO__X_FLI = 'video/x-fli';
    public const VIDEO__X_FLV = 'video/x-flv';
    public const VIDEO__X_GL = 'video/x-gl';
    public const VIDEO__X_ISVIDEO = 'video/x-isvideo';
    public const VIDEO__X_MOTION_JPEG = 'video/x-motion-jpeg';
    public const VIDEO__X_MPEG = 'video/x-mpeg';
    public const VIDEO__X_MPEQ2A = 'video/x-mpeq2a';
    public const VIDEO__X_MSVIDEO = 'video/x-msvideo';
    public const VIDEO__X_MS_ASF = 'video/x-ms-asf';
    public const VIDEO__X_MS_ASF_PLUGIN = 'video/x-ms-asf-plugin';
    public const VIDEO__X_MS_WM = 'video/x-ms-wm';
    public const VIDEO__X_MS_WMD = 'video/x-ms-wmd';
    public const VIDEO__X_MS_WMV = 'video/x-ms-wmv';
    public const VIDEO__X_MS_WMX = 'video/x-ms-wmx';
    public const VIDEO__X_MS_WMZ = 'video/x-ms-wmz';
    public const VIDEO__X_MS_WVX = 'video/x-ms-wvx';
    public const VIDEO__X_QTC = 'video/x-qtc';
    public const VIDEO__X_SCM = 'video/x-scm';
    public const VIDEO__X_SGI_MOVIE = 'video/x-sgi-movie';
    public const VIDEO__YOUTUBE = 'video/youtube';
    public const WINDOWS__METAFILE = 'windows/metafile';
    public const WWW__MIME = 'www/mime';
    public const XGL__DRAWING = 'xgl/drawing';
    public const XGL__MOVIE = 'xgl/movie';
    public const X_CONFERENCE__X_COOLTALK = 'x-conference/x-cooltalk';
    public const X_MUSIC__X_MIDI = 'x-music/x-midi';
    public const X_WORLD__X_3DMF = 'x-world/x-3dmf';
    public const X_WORLD__X_SVR = 'x-world/x-svr';
    public const X_WORLD__X_VRML = 'x-world/x-vrml';
    public const X_WORLD__X_VRT = 'x-world/x-vrt';

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
    public static function getExt2MimeMap(): array
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
    public static function getMimeType(string $a_file = '', string $a_filename = '', string $a_mime = ''): string
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
    ): string {
        $obj = new self($path_to_file);
        if ($a_external) {
            $obj->setExternal($a_external);
        }
        $obj->setFallback($fallback);

        return $obj->get();
    }

    public function get(): string
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

    protected function isExternal(): bool
    {
        return $this->external;
    }

    public function setExternal(bool $external): void
    {
        $this->external = $external;
    }

    protected function getPath(): string
    {
        return $this->path;
    }

    protected function setPath(string $path): void
    {
        $this->path = $path;
    }

    protected function getSuffix(): string
    {
        return $this->suffix;
    }

    protected function setSuffix(string $suffix): void
    {
        // see #18157
        $this->suffix = strtolower($suffix);
    }

    protected function getFallback(): string
    {
        return $this->fallback;
    }

    public function setFallback(string $fallback): void
    {
        $this->fallback = $fallback;
    }
}
