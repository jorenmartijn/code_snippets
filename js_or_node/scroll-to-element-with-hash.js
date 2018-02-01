$(document).ready(function($) { 
	// Triggers the content box on loading the page for lease actions
	var triggerAction = function() {
		$(document).ready(function($) {
			// Stores the hash and its parts in variables
			var hash = window.location.hash.substr(1),
				hash_parts = hash.split('=');
			// Check if the hash has a part called leaseactie 
			// and if something after that is present
			if(hash_parts.length > 0 && hash_parts[0] == 'action' && hash_parts[1]) {
				$('#'+hash_parts[1]).trigger('click'); // Triggers a click event to show the content
				$('html, body').animate({ // Scrolls the page to just above the element
				      scrollTop: $('#'+hash_parts[1]).offset().top - 100
				 }, 1500);
			}
		});
	};
	triggerAction();
});