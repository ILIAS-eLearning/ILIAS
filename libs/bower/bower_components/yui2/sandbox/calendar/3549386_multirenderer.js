YAHOO.widget.Calendar.prototype.renderBody = function(workingDate, html) {

    // Pulled in from "constants", setup in calendar.js
    var Calendar = YAHOO.widget.Calendar,
        DEF_CFG = Calendar.DEFAULT_CONFIG,
        DateMath = YAHOO.widget.DateMath,
        Dom = YAHOO.util.Dom;

    var startDay = this.cfg.getProperty(DEF_CFG.START_WEEKDAY.key);

    this.preMonthDays = workingDate.getDay();
    if (startDay > 0) {
        this.preMonthDays -= startDay;
    }
    if (this.preMonthDays < 0) {
        this.preMonthDays += 7;
    }

    this.monthDays = DateMath.findMonthEnd(workingDate).getDate();
    this.postMonthDays = Calendar.DISPLAY_DAYS-this.preMonthDays-this.monthDays;

    workingDate = DateMath.subtract(workingDate, DateMath.DAY, this.preMonthDays);

    var weekNum,
        weekClass,
        weekPrefix = "w",
        cellPrefix = "_cell",
        workingDayPrefix = "wd",
        dayPrefix = "d",
        cellRenderers,
        renderer,
        t = this.today,
        cfg = this.cfg,
        todayYear = t.getFullYear(),
        todayMonth = t.getMonth(),
        todayDate = t.getDate(),
        useDate = cfg.getProperty(DEF_CFG.PAGEDATE.key),
        hideBlankWeeks = cfg.getProperty(DEF_CFG.HIDE_BLANK_WEEKS.key),
        showWeekFooter = cfg.getProperty(DEF_CFG.SHOW_WEEK_FOOTER.key),
        showWeekHeader = cfg.getProperty(DEF_CFG.SHOW_WEEK_HEADER.key),
        mindate = cfg.getProperty(DEF_CFG.MINDATE.key),
        maxdate = cfg.getProperty(DEF_CFG.MAXDATE.key),
        yearOffset = this.Locale.YEAR_OFFSET;

    if (mindate) {
        mindate = DateMath.clearTime(mindate);
    }
    if (maxdate) {
        maxdate = DateMath.clearTime(maxdate);
    }

    html[html.length] = '<tbody class="m' + (useDate.getMonth()+1) + ' ' + this.Style.CSS_BODY + '">';

    var i = 0,
        tempDiv = document.createElement("div"),
        cell = document.createElement("td");

    tempDiv.appendChild(cell);

    var cal = this.parent || this;

    for (var r=0;r<6;r++) {
        weekNum = DateMath.getWeekNumber(workingDate, startDay);
        weekClass = weekPrefix + weekNum;

        // Local OOM check for performance, since we already have pagedate
        if (r !== 0 && hideBlankWeeks === true && workingDate.getMonth() != useDate.getMonth()) {
            break;
        } else {
            html[html.length] = '<tr class="' + weekClass + '">';

            if (showWeekHeader) { html = this.renderRowHeader(weekNum, html); }

            for (var d=0; d < 7; d++){ // Render actual days

                cellRenderers = [];

                this.clearElement(cell);
                cell.className = this.Style.CSS_CELL;
                cell.id = this.id + cellPrefix + i;

                if (workingDate.getDate()  == todayDate && 
                    workingDate.getMonth()  == todayMonth &&
                    workingDate.getFullYear() == todayYear) {
                    cellRenderers[cellRenderers.length]=cal.renderCellStyleToday;
                }

                var workingArray = [workingDate.getFullYear(),workingDate.getMonth()+1,workingDate.getDate()];
                this.cellDates[this.cellDates.length] = workingArray; // Add this date to cellDates

                // Local OOM check for performance, since we already have pagedate
                if (workingDate.getMonth() != useDate.getMonth()) {
                    cellRenderers[cellRenderers.length]=cal.renderCellNotThisMonth;
                } else {
                    Dom.addClass(cell, workingDayPrefix + workingDate.getDay());
                    Dom.addClass(cell, dayPrefix + workingDate.getDate());

                    // Concat, so that we're not splicing from an array 
                    // which we're also iterating
                    var rs = this.renderStack.concat();

                    for (var s=0, l = rs.length; s < l; ++s) {

                        renderer = null;

                        var rArray = rs[s],
                            type = rArray[0],
                            month,
                            day,
                            year;

                        switch (type) {
                            case Calendar.DATE:
                                month = rArray[1][1];
                                day = rArray[1][2];
                                year = rArray[1][0];

                                if (workingDate.getMonth()+1 == month && workingDate.getDate() == day && workingDate.getFullYear() == year) {
                                    renderer = rArray[2];
                                    this.renderStack.splice(s,1);
                                }

                                break;
                            case Calendar.MONTH_DAY:
                                month = rArray[1][0];
                                day = rArray[1][1];

                                if (workingDate.getMonth()+1 == month && workingDate.getDate() == day) {
                                    renderer = rArray[2];
                                    this.renderStack.splice(s,1);
                                }
                                break;
                            case Calendar.RANGE:
                                var date1 = rArray[1][0],
                                    date2 = rArray[1][1],
                                    d1month = date1[1],
                                    d1day = date1[2],
                                    d1year = date1[0],
                                    d1 = DateMath.getDate(d1year, d1month-1, d1day),
                                    d2month = date2[1],
                                    d2day = date2[2],
                                    d2year = date2[0],
                                    d2 = DateMath.getDate(d2year, d2month-1, d2day);

                                if (workingDate.getTime() >= d1.getTime() && workingDate.getTime() <= d2.getTime()) {
                                    renderer = rArray[2];

                                    if (workingDate.getTime()==d2.getTime()) { 
                                        this.renderStack.splice(s,1);
                                    }
                                }
                                break;
                            case Calendar.WEEKDAY:
                                var weekday = rArray[1][0];
                                if (workingDate.getDay()+1 == weekday) {
                                    renderer = rArray[2];
                                }
                                break;
                            case Calendar.MONTH:
                                month = rArray[1][0];
                                if (workingDate.getMonth()+1 == month) {
                                    renderer = rArray[2];
                                }
                                break;
                        }

                        if (renderer) {
                            cellRenderers[cellRenderers.length]=renderer;
                        }
                    }

                }

                if (this._indexOfSelectedFieldArray(workingArray) > -1) {
                    cellRenderers[cellRenderers.length]=cal.renderCellStyleSelected; 
                }

                if ((mindate && (workingDate.getTime() < mindate.getTime())) ||
                    (maxdate && (workingDate.getTime() > maxdate.getTime()))
                ) {
                    cellRenderers[cellRenderers.length]=cal.renderOutOfBoundsDate;
                } else {
                    cellRenderers[cellRenderers.length]=cal.styleCellDefault;
                    cellRenderers[cellRenderers.length]=cal.renderCellDefault; 
                }

                for (var x=0; x < cellRenderers.length; ++x) {
                    if (cellRenderers[x].call(cal, workingDate, cell) == Calendar.STOP_RENDER) {
                        break;
                    }
                }

                workingDate.setTime(workingDate.getTime() + DateMath.ONE_DAY_MS);
                // Just in case we crossed DST/Summertime boundaries
                workingDate = DateMath.clearTime(workingDate);

                if (i >= 0 && i <= 6) {
                    Dom.addClass(cell, this.Style.CSS_CELL_TOP);
                }
                if ((i % 7) === 0) {
                    Dom.addClass(cell, this.Style.CSS_CELL_LEFT);
                }
                if (((i+1) % 7) === 0) {
                    Dom.addClass(cell, this.Style.CSS_CELL_RIGHT);
                }

                var postDays = this.postMonthDays; 
                if (hideBlankWeeks && postDays >= 7) {
                    var blankWeeks = Math.floor(postDays/7);
                    for (var p=0;p<blankWeeks;++p) {
                        postDays -= 7;
                    }
                }
                
                if (i >= ((this.preMonthDays+postDays+this.monthDays)-7)) {
                    Dom.addClass(cell, this.Style.CSS_CELL_BOTTOM);
                }

                html[html.length] = tempDiv.innerHTML;
                i++;
            }

            if (showWeekFooter) { html = this.renderRowFooter(weekNum, html); }

            html[html.length] = '</tr>';
        }
    }

    html[html.length] = '</tbody>';

    return html;
};
