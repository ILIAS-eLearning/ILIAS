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
!function(e){"use strict";const t="{::}",n="answers",o="c-test__dropzone";let s;function r(){const e=[],r=s.querySelector(`.${o}`);s.firstElementChild.classList.contains("answers")&&s.prepend(r.cloneNode()),s.lastElementChild.classList.contains("answers")&&s.append(r.cloneNode()),s.querySelectorAll(`.${n} + .${n}`).forEach((e=>{e.parentNode.insertBefore(r.cloneNode(),e)})),s.querySelectorAll(`.${o} + .${o}`).forEach((e=>{e.remove()})),s.querySelectorAll(`.${n} > div > span`).forEach((t=>{e.push(t.textContent)})),s.nextElementSibling.value=e.join(t)}const i="c-test__dropzone--active",a="c-test__dropzone--hover";let l,c,d,f,u,v,h;function g(e){m(e.target),e.dataTransfer.dropEffect="move",e.dataTransfer.effectAllowed="move",e.dataTransfer.setDragImage(u,0,0)}function p(e){e.preventDefault(),m(e.target.closest(`.${c}`)),v=u.cloneNode(!0),u.parentNode.insertBefore(v,u),u.style.position="fixed",u.style.left=e.touches[0].clientX-u.offsetWidth/2+"px",u.style.top=e.touches[0].clientY-u.offsetHeight/2+"px",u.addEventListener("touchmove",E),u.addEventListener("touchend",A)}function m(e){u=e,u.style.opacity=.5,u.previousElementSibling?.classList.contains(d)&&u.previousElementSibling.remove(),u.nextElementSibling?.classList.contains(d)&&u.nextElementSibling.remove(),l.querySelectorAll(`.${d}`).forEach((e=>{e.style.width=`${u.offsetWidth}px`,e.style.height=`${u.offsetHeight}px`,e.classList.add(i)}))}function E(e){e.preventDefault(),u.style.left=e.touches[0].clientX-u.offsetWidth/2+"px",u.style.top=e.touches[0].clientY-u.offsetHeight/2+"px";const t=l.ownerDocument.elementsFromPoint(e.changedTouches[0].pageX,e.changedTouches[0].pageY).filter((e=>e.classList.contains(d)));0===t.length&&void 0!==h&&(h.classList.remove(a),h=void 0),1===t.length&&h!==t[0]&&(void 0!==h&&h.classList.remove(a),[h]=t,h.classList.add(a))}function L(e){e.preventDefault()}function y(e){e.target.classList.add(a)}function S(e){e.target.classList.remove(a)}function $(){u.removeAttribute("style"),l.querySelectorAll(`.${d}`).forEach((e=>{e.classList.remove(i),e.classList.remove(a),D(e)}))}function x(e){e.preventDefault(),q(e.target)}function A(e){e.preventDefault();const t=l.ownerDocument.elementsFromPoint(e.changedTouches[0].pageX,e.changedTouches[0].pageY).filter((e=>e.classList.contains(d)));$(),v.remove(),1===t.length&&q(t[0])}function q(e){e.parentNode.insertBefore(u,e),f()}function D(e){e.removeEventListener("dragover",L),e.removeEventListener("dragenter",y),e.removeEventListener("dragleave",S),e.removeEventListener("drop",x),e.addEventListener("dragover",L),e.addEventListener("dragenter",y),e.addEventListener("dragleave",S),e.addEventListener("drop",x)}function w(e,t,n,o){l=e,c=t,d=n,f=o,l.querySelectorAll(`.${c}`).forEach((e=>{e.addEventListener("dragstart",g),e.addEventListener("dragend",$),e.addEventListener("touchstart",p)})),l.querySelectorAll(`.${d}`).forEach(D)}e.test=e.test||{},e.test.orderinghorizontal=e.test.orderinghorizontal||{},e.test.orderinghorizontal.init=(e,t)=>function(e,t){s=e,t(s,n,o,r)}(e,w)}(il);
