/*
*
*
* Custom JS
*
*
*/

import $ from 'jquery';
import prestashop from 'prestashop';


$(document).ready(function() {
	var faqTrigger = $('.cd-faq-trigger');
	//show faq content clicking on faqTrigger
	faqTrigger.on('click', function(event){
		event.preventDefault();
		$(this).next('.cd-faq-content').slideToggle(200).end().parent('li').toggleClass('content-visible');
	});
});