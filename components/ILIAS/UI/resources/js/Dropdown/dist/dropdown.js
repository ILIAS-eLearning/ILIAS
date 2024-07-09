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
!function(t){"use strict";function e(t){return t&&"object"==typeof t&&"default"in t?t:{default:t}}var i=e(t);class n{#t;#e;#i;#n;constructor(t){if(this.#e=t,this.#t=t.ownerDocument,this.#i=this.#e.querySelector(":scope > button"),null===this.#i)throw new Error("Dropdown: Expected exactly one button in dropdown element.",this.#e);if(this.#n=this.#e.querySelector(":scope > ul"),null===this.#n)throw new Error("Dropdown: Expected exactly one ul in dropdown element.",this.#e);this.#i.addEventListener("click",this.#s)}#o=t=>{27===t.key&&this.hide()};#s=t=>{t.stopPropagation(),this.show()};#d=()=>{this.hide()};show(){this.#n.style.display="block",this.#i.setAttribute("aria-expanded","true"),this.#t.addEventListener("keydown",this.#o),this.#t.addEventListener("click",this.#d),this.#i.removeEventListener("click",this.#s)}hide(){this.#n.style.display="none",this.#i.setAttribute("aria-expanded","false"),this.#t.removeEventListener("keydown",this.#o),this.#t.removeEventListener("click",this.#d),this.#i.addEventListener("click",this.#s)}}i.default.UI=i.default.UI||{},i.default.UI.dropdown={},i.default.UI.dropdown.init=function(t){return new n(t)}}(il);
