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
 */
!function(e,o){"use strict";e.test=e.test||{},e.test.questionpage=e.test.questionpage||{},e.test.questionpage.init=()=>{[...o.querySelectorAll("#ilAssQuestionPreview .modal-header form, #ilAssQuestionPreview .modal-footer form, #ilc_Page .modal-header form, #ilc_Page .modal-footer form")].forEach(e=>{const t=o.createDocumentFragment();for(;e.firstChild;)t.appendChild(e.firstChild);e.parentNode.replaceChild(t,e)}),[...o.querySelectorAll('#ilAssQuestionPreview .modal-header [formmethod="dialog"], #ilAssQuestionPreview .modal-header [formmethod="dialog"], #ilc_Page .modal-header [formmethod="dialog"], #ilc_Page .modal-footer [formmethod="dialog"]')].forEach(e=>{e.addEventListener("click",()=>e.closest("dialog").close())})}}(il,document);
