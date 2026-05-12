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
!function(t){"use strict";class e{#t;#e;#n;#i;constructor(t){if(!(t&&t instanceof HTMLElement))throw new TypeError("Dropdown: Expected an HTMLElement root (.dropdown); received "+(null===t?"null":typeof t));if(this.#e=t,this.#t=t.ownerDocument,this.#n=this.#e.querySelector(":scope > button"),null===this.#n)throw new Error("Dropdown: Expected exactly one button in dropdown element.",this.#e);if(this.#i=this.#e.querySelector(".dropdown-menu"),null===this.#i)throw new Error("Dropdown: Expected exactly a dropdown element.",this.#e);this.#n.addEventListener("click",this.#o)}#s=t=>{27===t.key&&this.hide()};#o=t=>{t.stopPropagation(),this.show()};#d=()=>{this.hide()};#l=()=>{const t=this.#t.documentElement.clientWidth;this.#n.getBoundingClientRect().left+this.#i.getBoundingClientRect().width>t?(this.#i.classList.remove("dropdown-menu__right"),this.#i.classList.add("dropdown-menu__left")):(this.#i.classList.remove("dropdown-menu__left"),this.#i.classList.add("dropdown-menu__right"))};show(){il.UI.dropdown.opened?.hide(),il.UI.dropdown.opened=this,this.#i.style.display="block",this.#l(),this.#n.setAttribute("aria-expanded","true"),this.#t.addEventListener("keydown",this.#s),this.#t.addEventListener("click",this.#d),this.#n.removeEventListener("click",this.#o)}hide(){this.#i.style.display="none",this.#n.setAttribute("aria-expanded","false"),this.#t.removeEventListener("keydown",this.#s),this.#t.removeEventListener("click",this.#d),this.#n.addEventListener("click",this.#o)}}t.UI=t.UI||{},t.UI.dropdown={},t.UI.dropdown.opened=null,t.UI.dropdown.init=function(t){return new e(t)}}(il);
