
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


/**
 * Overlay
 */
export default class Overlay {

    /**
     * @param int x
     * @param int y
     */
    constructor(x, y, src, imgPath) {
        this.x = x;
        this.y = y;
        this.src = src;
        this.imgPath = imgPath;
        this.onDrag = null;
    }

    /**
     * @return int
     */
    getX() {
        return this.x;
    }

    /**
     * @return int
     */
    getY() {
        return this.y;
    }

    /**
     * @return string
     */
    getSrc() {
        return this.src;
    }

    /**
     * @return string
     */
    getImgPath() {
        return this.imgPath;
    }

    getCoordsString() {
        return this.x + "," + this.y;
    }

    addOverlayToMobElement(mobEl, drag = false) {
        const overlay = document.createElement("img");
        overlay.setAttribute('data-copg-iim-type', 'overlay');
        overlay.style.position = "absolute";
        overlay.style.display = "block";
        overlay.style.left = this.getX() + "px";
        overlay.style.top = this.getY() + "px";
        overlay.src = this.getImgPath();
        if (drag) {
            this.draggable(overlay);
        }
        mobEl.appendChild(overlay);
    }

    setOnDrag(f) {
        this.onDrag = f;
    }

    draggable(elmnt) {
        var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        const t = this;
        elmnt.onmousedown = dragMouseDown;

        function dragMouseDown(e) {
            e = e || window.event;
            e.preventDefault();
            // get the mouse cursor position at startup:
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            // call a function whenever the cursor moves:
            document.onmousemove = elementDrag;
        }

        function elementDrag(e) {
            e = e || window.event;
            e.preventDefault();
            // calculate the new cursor position:
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
            // set the element's new position:
            elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
            elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
            t.x = (elmnt.offsetLeft - pos1);
            t.y = (elmnt.offsetTop - pos2);
            if (t.onDrag) {
                const f = t.onDrag;
                console.log("call on drag");
                f();
            }
        }

        function closeDragElement() {
            // stop moving when mouse button is released:
            document.onmouseup = null;
            document.onmousemove = null;
        }
    }
}
