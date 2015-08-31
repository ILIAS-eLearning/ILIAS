<?php exit; ?>

===================================
Wiki User HTML Export
===================================

- On Button Click (Javascript): il.Wiki.Pres.startHTMLExport()
  - Ajax call to (PHP): ilObjWikiGUI->initUserHTMLExport()
    - On Ajax Success (Javascript):
      - Ajax call to (PHP): ilObjWikiGUI->startUserHTMLExport()
      - Call to il.Wiki.Pres.updateProgress
        - Ajax call to (PHP): ilObjWikiGUI->getUserHTMLExportProgress()
          - On Ajax Success:
			- If finished window.location.href to ilObjWikiGUI->downloadUserHTMLExport()
            - If not finished: Wait for a second and call to il.Wiki.Pres.updateProgress


===================================
New Wiki Links
===================================

- Button Presentation (1) -> WikiPageGUI adds button?

(1) OnClick
- Open Overlay
- Get Form per Ajax
  - Target Page (2)
  - Presentation Text
  - Search Button (3)
  - Add Link Button (4)
  - Cancel Button (5)

(2) OnChange
- Get/add autocomplete list
- Check existence and update status

(3) Click
- Get result list per ajax

(4) Click
- Add link to text frame (problem: keep selection)
- Close Overlay

(5) Click
- Close Overlay

