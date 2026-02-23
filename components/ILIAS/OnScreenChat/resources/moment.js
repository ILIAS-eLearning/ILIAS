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

(function (scope) {
	scope = scope || {};
	let locale = null;

	scope.ChatDateTimeFormatter = {};

	scope.ChatDateTimeFormatter.setLocale = function (theLocale) {
		locale = theLocale;
	};

	scope.ChatDateTimeFormatter.fromNowToTime = function (time) {
		const now = new Date()
		const currentTime = now.getTime();

		if (isNaN(time) || time > currentTime) {
			time = currentTime;
		}

		const date = new Date(time);
		const seconds = diffSeconds(now, date);
		const years = now.getFullYear() - date.getFullYear()
		const months = (now.getFullYear() * 12 + now.getMonth()) - (date.getFullYear() * 12 + date.getMonth());
		const days = Math.floor(seconds / 86400);

		return new Intl.RelativeTimeFormat(locale).format(...firstMatch([
			[years === 1 && months > 10 || years > 1, years, 'year'],
			[months === 1 && days > 27 || months > 1, months, 'month'],
			[604800, 'week'],
			[86400, 'day'],
			[3600, 'hour'],
			[60, 'minute'],
			[true, seconds, 'second'],
		]));

		function firstMatch(array)
		{
			const match = array.find(a => typeof a[0] === 'boolean' ? a[0] : seconds / a[0] >= 1);
			return typeof match[0] === 'boolean' ?
				[-match[1], match[2]] :
				[-Math.floor(seconds / match[0]), match[1]];
		}
	};

	scope.ChatDateTimeFormatter.format = function (time, format) {
		let currentTime = new Date().getTime();

		if (isNaN(time) || time > currentTime) {
			time = currentTime;
		}

		if (format !== 'LT') {
			throw Error('Only format LT allowed');
		}

		return new Intl.DateTimeFormat(locale, {timeStyle: 'medium'}).format(new Date(time));
	};

	scope.ChatDateTimeFormatter.formatDate = function (time) {
		const now = new Date();
		const currentTime = now.getTime();

		if (isNaN(time) || time > currentTime) {
			time = currentTime;
		}

		const date = new Date(time);
		const seconds = diffSeconds(now, date);

		if (now.getFullYear() === date.getFullYear() && now.getMonth() === date.getMonth() && now.getDate() <= date.getDate() + 1) {
			return now.getDate() === date.getDate() ?
				il.Language.txt('today') :
				il.Language.txt('yesterday');
		}

		return new Intl.DateTimeFormat(locale, {dateStyle: 'long'}).format(new Date(time));
	};

	function diffSeconds(a, b)
	{
		return Math.round((a.getTime() - b.getTime()) / 1000);
	}
})(window.il);
