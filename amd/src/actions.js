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
 * @package			format
 * @subpackage	topicstabs
 * @module			format_topicstabs/actions
 * @author			Philippe MATABIAU
 * @copyright		2023 ÉTS Montréal {@link http://etsmtl.ca}
 * @license			http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since				2023021300
*/

define(['jquery', 'core/ajax'], function ($, ajax) {
	return {
		init: function (courseId) {
			require(['theme_boost/bootstrap/tab'], function () { // obligé de charger tab, sinon $(..).tab not exist
				$(function () { // = shortcut for 'document ready'
					var section = '#section-0'; // Val par defaut

					if ($('.topicstabs .nav-tabs .nav-item a.tabcurrent').length > 0) // Section marquée existe
						section = $('.topicstabs .nav-tabs .nav-item a.tabcurrent').attr("href");

					var hash = $(location).attr('hash');
					if (hash.substr(1, 8) === 'section-') // Si section dans le hash de l'URL
						section = hash;

					// Priorities: 1. hash, 2. section marquée, 3. defaut (section-1)
					$('.topicstabs .nav-tabs .nav-item a[href="' + section + '"]').tab('show');

					// Comportements
					$('.topicstabs .nav-tabs .nav-item a').on("click", function () {
						$(location).attr('hash', $(this).attr('href'));
					});
					$(window).on('hashchange', function () {
						$('.topicstabs .nav-tabs .nav-item a[href="' + $(location).attr('hash') + '"]').tab('show');
					});
				});
			});
		}
	};
});
