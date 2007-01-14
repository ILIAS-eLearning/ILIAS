
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * Note: Some of this code derives from other work by the original author that 
 * has been published under Common Public License (CPL 1.0). Please send mail 
 * for more information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 * 
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ...
 *   
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 

WHAT TO DO NEXT
- Admin: Remove CMI Data on "Remove"
- Admin: Adding Player controlling settings like 
	- which gui components to show
	- caching interval
	- etc.
- Player/Tracking: dataset conversion sql=>php=>json=>cmi and reverse 
- Player/Tracking: CMIDataStore object
- Player/Tracking: Correct initialization and binding of API on delivery  
- Player/Debug: Show CMI Data in SCO Wrapper and refresh on SetValue events
 
WHAT TO DO THEN
- Player/Tracking: Checking API principal workflows   
- Player/Sequencing: Implementing NavEvents in the following order:
		Previous, Exit, Abandon, SuspendAll, ExitAll, AbandonAll
		(Backward and Forward will not be implemented)     
		(Implementation of Start, ResumeAll and Continue have already begun)
- Player/Sequencing: Implementing Rollup behaviors

WHAT TO DO LATER
- updating of choice gui componentes on API.Commit event
- visible toggling of tree states on clicking block items
- import of SCORM-1.2 Packages 
- running of SCORM-1.2 Packages with all SCORM1.3 variables set to 
	default values, using normal SCORM RTE API, and simple datafield conversion
	where types have chaged
	
