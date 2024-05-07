
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

export default class Util {

    constructor() {
        this.last = [];
    }

    addShapeListeners(mobEl) {
        mobEl.querySelectorAll("[data-copg-ed-type='shape']").forEach((shape) => {
            shape.addEventListener('click', () => {
                this.toggleTriggerPopupAtShape(shape, triggerNr);
              }
            );
        });
    }

    /* not needed currently, may be an alternative
       if getBoundingClientRect does not work on some browsers
    getShapeBox(shape) {
        const shapeBox = shape.getBBox();
        const svg = shape.closest("svg");
        const svgRect = svgRect.getBoundingClientRect();
        return {
            top: svgRect.top + shapeBox.y,
            left: svgRect.left + shapeBox.x,
            width: svgRect.width,
            height: svgRect.height
        }
    }*/

    refreshPopupPosition(topContainer, mobContainer, popEl, shape) {
        if (shape) {
            const containerRect = topContainer.getBoundingClientRect();

            // reset
            popEl.style.marginTop = "0";
            popEl.style.marginLeft = "0";

            // top positioning
            const shapeRect = shape.getBoundingClientRect();
            const popRect = popEl.getBoundingClientRect();
            popEl.style.marginTop =
              "-" + (popRect.top - shapeRect.top - shapeRect.height - 10) + "px";

            // left positioning
            const shapeCenter = shapeRect.left + (shapeRect.width / 2);
            const popCenter =  popRect.left + (popRect.width / 2);
            let offset = shapeCenter - popCenter;
            // to far to the right? reduce offset
            if (popCenter + offset + (popRect.width / 2) > containerRect.left + containerRect.width) {
                offset = containerRect.width - popRect.width;
            }
            // to far to the left? reduce offset
            if (popCenter - (popRect.width / 2) + offset < containerRect.left) {
                offset = 0;
            }
            popEl.style.marginLeft =
              offset + "px";
        }
    }

    attachPopupToShape(topContainer, mobContainer, popEl, shape) {
        const t = this;
        this.refreshPopupPosition(topContainer, mobContainer, popEl, shape);
        window.addEventListener("resize", (e) => {
            if (t.last[popEl.id] == shape) {
                t.refreshPopupPosition(topContainer, mobContainer, popEl, shape);
            }
        });
    }

    lastClicked(popEl, shape) {
        this.last[popEl.id] = shape;
    }

    attachPopupToTrigger(topContainer, mobContainer, popEl, triggerNr) {
        const t = this;
        const shape = mobContainer.querySelector("[data-copg-ed-type='shape'][data-trigger-nr='" +
          triggerNr + "']");
        this.attachPopupToShape(topContainer, mobContainer, popEl, shape);
    }

    getOverlaySvg(mobElement) {
        let svg = mobElement.querySelector("[data-copg-iim-type='svg-overlay']");
        if (!svg) {
            svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            svg.setAttribute('data-copg-iim-type', "svg-overlay");
            svg.style.position = "absolute";
            svg.style.left = "0px";
            svg.style.top = "0px";
            svg.style.width = "100%";
            svg.style.height = "100%";
            mobElement.appendChild(svg);
        };
        return svg;
    }

}
