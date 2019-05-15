// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Various actions on course format
 *
 * @package    format
 * @subpackage topicstabs
 * @module     format_topicstabs/actions
 * @author Philippe MATABIAU
 * @copyright 2019 ÉTS Montréal {@link http://etsmtl.ca}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      2019050100
 */

define(['jquery', 'core/ajax'], function ($, ajax) {
	var getCookieValue = function (a) {
		var b = document.cookie.match('(^|[^;]+)\\s*' + a + '\\s*=\\s*([^;]+)');
		return b ? b.pop() : '';
	};

	return {
		init: function (courseId) {
			require(['theme_boost/tab'], function () { // obligé de charger tab, sinon $(..).tab not exist
				$(function () { // = shortcut for 'document ready'
					var cookiePrefix = 'mdl_topicstabs_', section = '';
					// Priorities: 1. hash, 2. cookie(max-age=1h), ... puis selon page PHP (section marquee ou sect-1)
					var hash = $(location).attr('hash');
					if (hash.substr(1, 8) === 'section-') {
						section = hash;
					} else {
						section = getCookieValue(cookiePrefix + courseId);
					}
					$('.topicstabs .nav-tabs li a[href="' + section + '"]').tab('show');

					// comportements
					$('.nav-tabs li a').on("click", function () {
						var section = $(this).attr('href');
						$(location).attr('hash', section);
						// set cookie idCourse_topicstab=hash
						document.cookie = cookiePrefix + courseId + '=' + section + '; max-age=' + 60 * 60;
					});
				});
			});
		}
	};
});
