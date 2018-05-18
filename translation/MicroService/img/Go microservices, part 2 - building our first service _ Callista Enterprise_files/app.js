(function () {
	'use strict';

	// Initialize the carousel of projects on the start page.
	$(".owl-carousel").owlCarousel({
		autoPlay: true,
		singleItem: true,
		navigation: true,
		navigationText: ["<", ">"],
		pagination: true,
		paginationSpeed: 500,
		responsive: true,
		slideSpeed: 500,
		stopOnHover: true,
		theme: 'ce-owl'
	});

	// Used to handle clicks on table rows that acts like links.
	$(document).on('click', '[data-href]', function () {
		document.location = $(this).data('href');
	});

	// Used to toggle the menu if it is hidden (on small screens)
	$(document).on('click', '#menu', function () {
		$('#menu-primary').toggleClass('ce-active');
		$('#menu').toggleClass('ce-active');
	});

	$(function () {

		var personList = $('.ce-person-list-limited');
		if (personList.length > 0) {

			// Pick four random persons to show and load the real images for these persons.
			for (var i = 0; i < 4; i++) {
				var persons = personList.find('[data-src][src$=".gif"]');
				var number = Math.floor(Math.random() * persons.length);
				var person = $(persons.get(number));
				person.attr('src', person.data('src'));
			}

			// All persons are added by in the template but with an empty gif. Remove all persons
			// without a real image, i.e. the ones that weren't choosen by the randomizer.
			personList.find('[src$=".gif"]').parent().parent().parent().remove();
		}
	});
})();

function initDisqus() {

	var disqusShortname = 'callistablog';

	var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
	dsq.src = '//' + disqusShortname + '.disqus.com/embed.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
}
