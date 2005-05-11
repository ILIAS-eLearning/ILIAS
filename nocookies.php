<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* no cookies script for ilias
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
?>

<p>
Die aktuellen Versionen der gängigen Browser können in den
<br/>Sicherheitseinstellungen zwischen normalen und sogenannten
<br/>Sitzungscookies unterscheiden. Um ILIAS zu verwenden, müssen 
<br/>Sie zumindest die Annahme von Sitzungscookies bei Ihrem Browser
<br>zulassen. Und so gehts:
<br/>
<br/>Firefox:
<br/>Tools->Options->Privacy->Cookies
<br/>Dort 'Allow sites to set cookies' ankreuzen und bei der Option 'Keep
<br/>cookies' auf 'until I close Firefox' auswählen
<br/>
<br/>Mozilla/Netscape:
<br/>Edit->Preferences->Privacy&Security->Cookies
<br/>Dort unter 'Cookie Lifetime Policy' die Option 'Accept for current
<br/>session only' auswählen
<br/>
<br/>Internet Explorer:
<br/>Extras->Internetoptionen->Datenschutz->Erweitert...
<br/>- 'Automatische Cookiebehandlung aufheben' ankreuzen
<br/>- 'Cookies von Erstanbietern' und 'Cookies von Drittanbietern' auf
<br/>'Sperren' stellen
<br/>- 'Sitzungscookies immer zulassen' ankreuzen
</p>