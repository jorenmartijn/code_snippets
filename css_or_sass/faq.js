// JS code to use with faq.scss
jQuery(document).ready(function($) {
	$('.faqs h6').on('click', function(){
	    var answer = "#"+$(this).data('toggles'); // h6[data-toggles="element to be toggled"]
	    $(this).toggleClass('active');
	    $(answer).toggleClass('active');
	});
});
