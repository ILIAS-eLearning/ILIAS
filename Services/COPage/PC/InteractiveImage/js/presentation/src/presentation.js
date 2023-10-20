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

import Util from "../../common/src/util.js";
import Area from "../../editor/src/area/area.js";
import AreaFactory from "../../editor/src/area/area-factory.js";

const presentation = (function () {

  function mouseover(triggerId) {
    const overlay = document.getElementById("iim_ov_" + triggerId);
    if (overlay) {
      // of not positioned under ilc_Mob, do it
      if (!overlay.parentNode.classList.contains("ilc_Mob")) {
        const img = overlay.parentNode.querySelector(".ilc_Mob > img");
        overlay.style.position = 'absolute';
        img.after(overlay);

        // get the position from the trigger data element
        const triggerDataEl = document.querySelector("[data-copg-iim-data-type='trigger'][data-copg-iim-tr-id='" + triggerId + "']");
        overlay.style.left = triggerDataEl.getAttribute('data-copg-iim-ovx') + "px";
        overlay.style.top = triggerDataEl.getAttribute('data-copg-iim-ovy') + "px";
      }
      overlay.style.display = '';
    }
  }

  function mouseout(triggerId) {
    console.log("out " + triggerId);
    const overlay = document.getElementById("iim_ov_" + triggerId);
    if (overlay) {
      overlay.style.display = 'none';
    }
  }

  function init(node) {
    let iimId;
    let mobEl;
    let areaEl;
    let svg;
    //let popupEl;
    let popupNr;
    const util = new Util;
    const areaFactory = new AreaFactory();
    let topContainer;

    // find all triggers within the node
    node.querySelectorAll("[data-copg-iim-data-type='trigger']").forEach((tr) => {
      let areaDataEl, areaId, triggerNr, markerEl, clickEl;
      let triggerType, triggerId, size;
      topContainer = tr.closest('.ilc_page_cont_PageContainer');
      if (!topContainer) {
        topContainer = tr.closest('#il_center_col');
      }
      // get map area of trigger
      triggerNr = tr.getAttribute("data-copg-iim-nr");
      iimId = tr.getAttribute("data-copg-iim-id");  // image id
      triggerId = tr.getAttribute("data-copg-iim-tr-id");
      popupNr = tr.getAttribute("data-copg-iim-popup-nr");
      size = tr.getAttribute("data-copg-iim-popup-size");
      triggerType = tr.getAttribute("data-copg-iim-type");
      mobEl = tr.parentNode.querySelector(".ilc_Mob");
      areaEl = null;
      markerEl = null;
      clickEl = null;
      if (triggerType == "Area") {
        areaDataEl = document.querySelector("[data-copg-iim-data-type='area'][data-copg-iim-id='" +
          iimId + "'][data-copg-iim-tr-nr='" + triggerNr + "']");
        areaId = areaDataEl.getAttribute("data-copg-iim-area-id");
        areaEl = document.getElementById(areaId);
      } else {
        markerEl = document.querySelector("[data-copg-iim-data-type='marker'][data-copg-iim-id='" +
          iimId + "'][data-copg-iim-tr-nr='" + triggerNr + "']");
        clickEl = markerEl;
      }
      svg = util.getOverlaySvg(mobEl);
      let popupEl = null;
      if (popupNr) {
        popupEl = mobEl.parentNode.parentNode.parentNode.querySelector("[data-copg-cont-type='iim-popup'][data-copg-popup-nr='" + popupNr + "']");
        popupEl.id = "iim_popup_parent_" + iimId + "_" + popupNr;
      }

      if (areaEl) {
        const area = areaFactory.area(
          areaEl.getAttribute("shape"),
          areaEl.getAttribute("coords")
        );
        const shape = area.getShape();
        const shapeEl = shape.addToSvg(svg);
        clickEl = shapeEl;
      }
      if (popupEl && clickEl) {

        util.attachPopupToShape(topContainer, mobEl, popupEl, clickEl);
        clickEl.addEventListener("click", () => {
          util.lastClicked(popupEl, clickEl);
          if (popupEl.style.display === "none") {
            document.querySelectorAll("[data-copg-cont-type='iim-popup']").forEach((p) => {
              p.style.display = "none";
            });
            if (size == "") {
              size = "md";
            }
            popupEl.classList.remove('copg-iim-popup-md');
            popupEl.classList.remove('copg-iim-popup-lg');
            popupEl.classList.remove('copg-iim-popup-sm');
            popupEl.classList.add('copg-iim-popup-' + size);
            popupEl.style.display = "";
            util.refreshPopupPosition(topContainer, mobEl, popupEl, clickEl);
            window.dispatchEvent(new Event('resize'));
          } else {
            popupEl.style.display = "none";
          }
        });
      }

      if (clickEl) {
        clickEl.addEventListener("mouseover", () => {
          mouseover(triggerId);
        });
        clickEl.addEventListener("mouseout", () => {
          mouseout(triggerId);
        });
      }
    });

  }

  return {
    init
  };
})();
window.addEventListener('load', function () {
  presentation.init(document);
}, false);