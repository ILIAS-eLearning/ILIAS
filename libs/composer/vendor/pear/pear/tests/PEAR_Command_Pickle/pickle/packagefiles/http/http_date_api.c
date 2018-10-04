/*
    +--------------------------------------------------------------------+
    | PECL :: http                                                       |
    +--------------------------------------------------------------------+
    | Redistribution and use in source and binary forms, with or without |
    | modification, are permitted provided that the conditions mentioned |
    | in the accompanying LICENSE file are met.                          |
    +--------------------------------------------------------------------+
    | Copyright (c) 2004-2005, Michael Wallner <mike@php.net>            |
    +--------------------------------------------------------------------+
*/

/* $Id$ */

#ifdef HAVE_CONFIG_H
#	include "config.h"
#endif
#include "php.h"

#include "php_http.h"
#include "php_http_std_defs.h"

#include <ctype.h>

static int check_day(char *day, size_t len);
static int check_month(char *month);
static int check_tzone(char *tzone);

/* {{{ day/month names */
static const char *days[] = {
	"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"
};
static const char *wkdays[] = {
	"Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"
};
static const char *weekdays[] = {
	"Monday", "Tuesday", "Wednesday",
	"Thursday", "Friday", "Saturday", "Sunday"
};
static const char *months[] = {
	"Jan", "Feb", "Mar", "Apr", "May", "Jun",
	"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
};
enum assume_next {
	DATE_MDAY,
	DATE_YEAR,
	DATE_TIME
};
#define DS -60
static const struct time_zone {
	const char *name;
	const int offset;
} time_zones[] = {
    {"GMT", 0},     /* Greenwich Mean */
    {"UTC", 0},     /* Universal (Coordinated) */
    {"WET", 0},     /* Western European */
    {"BST", 0 DS}, /* British Summer */
    {"WAT", 60},    /* West Africa */
    {"AST", 240},   /* Atlantic Standard */
    {"ADT", 240 DS},/* Atlantic Daylight */
    {"EST", 300},   /* Eastern Standard */
    {"EDT", 300 DS},/* Eastern Daylight */
    {"CST", 360},   /* Central Standard */
    {"CDT", 360 DS},/* Central Daylight */
    {"MST", 420},   /* Mountain Standard */
    {"MDT", 420 DS},/* Mountain Daylight */
    {"PST", 480},   /* Pacific Standard */
    {"PDT", 480 DS},/* Pacific Daylight */
    {"YST", 540},   /* Yukon Standard */
    {"YDT", 540 DS},/* Yukon Daylight */
    {"HST", 600},   /* Hawaii Standard */
    {"HDT", 600 DS},/* Hawaii Daylight */
    {"CAT", 600},   /* Central Alaska */
    {"AHST", 600},  /* Alaska-Hawaii Standard */
    {"NT",  660},   /* Nome */
    {"IDLW", 720},  /* International Date Line West */
    {"CET", -60},   /* Central European */
    {"MET", -60},   /* Middle European */
    {"MEWT", -60},  /* Middle European Winter */
    {"MEST", -60 DS},/* Middle European Summer */
    {"CEST", -60 DS},/* Central European Summer */
    {"MESZ", -60 DS},/* Middle European Summer */
    {"FWT", -60},   /* French Winter */
    {"FST", -60 DS},/* French Summer */
    {"EET", -120},  /* Eastern Europe, USSR Zone 1 */
    {"WAST", -420}, /* West Australian Standard */
    {"WADT", -420 DS},/* West Australian Daylight */
    {"CCT", -480},  /* China Coast, USSR Zone 7 */
    {"JST", -540},  /* Japan Standard, USSR Zone 8 */
    {"EAST", -600}, /* Eastern Australian Standard */
    {"EADT", -600 DS},/* Eastern Australian Daylight */
    {"GST", -600},  /* Guam Standard, USSR Zone 9 */
    {"NZT", -720},  /* New Zealand */
    {"NZST", -720}, /* New Zealand Standard */
    {"NZDT", -720 DS},/* New Zealand Daylight */
    {"IDLE", -720}, /* International Date Line East */
};
/* }}} */

/* {{{ Day/Month/TZ checks for http_parse_date()
	Originally by libcurl, Copyright (C) 1998 - 2004, Daniel Stenberg, <daniel@haxx.se>, et al. */
static int check_day(char *day, size_t len)
{
	int i;
	const char * const *check = (len > 3) ? &weekdays[0] : &wkdays[0];
	for (i = 0; i < 7; i++) {
	    if (!strcmp(day, check[0])) {
	    	return i;
		}
		check++;
	}
	return -1;
}

static int check_month(char *month)
{
	int i;
	const char * const *check = &months[0];
	for (i = 0; i < 12; i++) {
		if (!strcmp(month, check[0])) {
			return i;
		}
		check++;
	}
	return -1;
}

/* return the time zone offset between GMT and the input one, in number
   of seconds or -1 if the timezone wasn't found/legal */

static int check_tzone(char *tzone)
{
	unsigned i;
	const struct time_zone *check = time_zones;
	for (i = 0; i < sizeof(time_zones) / sizeof(time_zones[0]); i++) {
		if (!strcmp(tzone, check->name)) {
			return check->offset * 60;
		}
		check++;
	}
	return -1;
}
/* }}} */

/* {{{ char *http_date(time_t) */
PHP_HTTP_API char *_http_date(time_t t TSRMLS_DC)
{
	struct tm *gmtime, tmbuf;

	if (gmtime = php_gmtime_r(&t, &tmbuf)) {
		char *date = ecalloc(1, 31);
		snprintf(date, 30,
			"%s, %02d %s %04d %02d:%02d:%02d GMT",
			days[gmtime->tm_wday], gmtime->tm_mday,
			months[gmtime->tm_mon], gmtime->tm_year + 1900,
			gmtime->tm_hour, gmtime->tm_min, gmtime->tm_sec
		);
		return date;
	}

	return NULL;
}
/* }}} */

/* {{{ time_t http_parse_date(char *)
	Originally by libcurl, Copyright (C) 1998 - 2004, Daniel Stenberg, <daniel@haxx.se>, et al. */
PHP_HTTP_API time_t _http_parse_date(const char *date)
{
	time_t t = 0;
	int tz_offset = -1, year = -1, month = -1, monthday = -1, weekday = -1,
		hours = -1, minutes = -1, seconds = -1;
	struct tm tm;
	enum assume_next dignext = DATE_MDAY;
	const char *indate = date;

	int part = 0; /* max 6 parts */

	while (*date && (part < 6)) {
		int found = 0;

		while (*date && !isalnum(*date)) {
			date++;
		}

		if (isalpha(*date)) {
			/* a name coming up */
			char buf[32] = "";
			size_t len;
			sscanf(date, "%31[A-Za-z]", buf);
			len = strlen(buf);

			if (weekday == -1) {
				weekday = check_day(buf, len);
				if (weekday != -1) {
					found = 1;
				}
			}

			if (!found && (month == -1)) {
				month = check_month(buf);
				if (month != -1) {
					found = 1;
				}
			}

			if (!found && (tz_offset == -1)) {
				/* this just must be a time zone string */
				tz_offset = check_tzone(buf);
				if (tz_offset != -1) {
					found = 1;
				}
			}

			if (!found) {
				return -1; /* bad string */
			}
			date += len;
		}
		else if (isdigit(*date)) {
			/* a digit */
			int val;
			char *end;
			if ((seconds == -1) &&
				(3 == sscanf(date, "%02d:%02d:%02d", &hours, &minutes, &seconds))) {
				/* time stamp! */
				date += 8;
				found = 1;
			}
			else {
				val = (int) strtol(date, &end, 10);

				if ((tz_offset == -1) && ((end - date) == 4) && (val < 1300) &&
					(indate < date) && ((date[-1] == '+' || date[-1] == '-'))) {
					/* four digits and a value less than 1300 and it is preceded with
					a plus or minus. This is a time zone indication. */
					found = 1;
					tz_offset = (val / 100 * 60 + val % 100) * 60;

					/* the + and - prefix indicates the local time compared to GMT,
					this we need ther reversed math to get what we want */
					tz_offset = date[-1] == '+' ? -tz_offset : tz_offset;
				}

				if (((end - date) == 8) && (year == -1) && (month == -1) && (monthday == -1)) {
					/* 8 digits, no year, month or day yet. This is YYYYMMDD */
					found = 1;
					year = val / 10000;
					month = (val % 10000) / 100 - 1; /* month is 0 - 11 */
					monthday = val % 100;
				}

				if (!found && (dignext == DATE_MDAY) && (monthday == -1)) {
					if ((val > 0) && (val < 32)) {
						monthday = val;
						found = 1;
					}
					dignext = DATE_YEAR;
				}

				if (!found && (dignext == DATE_YEAR) && (year == -1)) {
					year = val;
					found = 1;
					if (year < 1900) {
						year += year > 70 ? 1900 : 2000;
					}
					if(monthday == -1) {
						dignext = DATE_MDAY;
					}
				}

				if (!found) {
					return -1;
				}

				date = end;
			}
		}

		part++;
	}

	if (-1 == seconds) {
		seconds = minutes = hours = 0; /* no time, make it zero */
	}

	if ((-1 == monthday) || (-1 == month) || (-1 == year)) {
		/* lacks vital info, fail */
		return -1;
	}

	if (sizeof(time_t) < 5) {
		/* 32 bit time_t can only hold dates to the beginning of 2038 */
		if (year > 2037) {
			return 0x7fffffff;
		}
	}

	tm.tm_sec = seconds;
	tm.tm_min = minutes;
	tm.tm_hour = hours;
	tm.tm_mday = monthday;
	tm.tm_mon = month;
	tm.tm_year = year - 1900;
	tm.tm_wday = 0;
	tm.tm_yday = 0;
	tm.tm_isdst = 0;

	t = mktime(&tm);

	/* time zone adjust */
	if (t != -1) {
		struct tm *gmt, keeptime2;
		long delta;
		time_t t2;

		if(!(gmt = php_gmtime_r(&t, &keeptime2))) {
			return -1; /* illegal date/time */
		}

		t2 = mktime(gmt);

		/* Add the time zone diff (between the given timezone and GMT) and the
		diff between the local time zone and GMT. */
		delta = (tz_offset != -1 ? tz_offset : 0) + (t - t2);

		if((delta > 0) && (t + delta < t)) {
			return -1; /* time_t overflow */
		}

		t += delta;
	}

	return t;
}
/* }}} */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */

