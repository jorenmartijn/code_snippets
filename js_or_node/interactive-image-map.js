/**
  * @author Joren de Graaf <jorendegraaf@gmail.com>
  * @summary A sort-of-library to enable responsive image maps (more like a big object than a library)
  * @description This library is like a modern interpretation of classic image maps.
  * It borrows some inspiration from HTML 5 image srcsets (responsive image standard), and allows you
  * to specify x and y positions for your dot elements (the ones you click on) on a per breakpoint basis.
  * This is handy because scaling images are always a pain to position anything on and keep it tied to the subject.
  * The library won't calculate the breakpoints' x and y positions automatically however (not yet at least),
  * and you'll need to specify those positions in the HTML. However once you've got that set-up the library takes over
  * and adjusts the positions according to what you specified.
  * @version 1.0
  * @example Example: html setup for the image container.
  * <!--
  * 	#interactive-image (your image's container, backgound-image needed, position: relative needed)
  * 		- #breakpoints (container for your dot elements, takes a unit attribute (px or %))
  * 			- .point (your dot element, this is the one that gets styled and placed,
  * 					 takes a target attribute and optional active attribute if you need something to toggle on page load)
  * 				- .coord (your point's breakpoint, takes a breakpoint name, x (left position) and y (top position) attribute)
  * -->
  * <div id="interactive-image" style="background-image: url('/assets/img/ship.jpg'); ">
  * 	<div id="breakpoints" data-unit="%">
  * 		<div data-target="#content-1" data-active="1" class="point">
  * 			<div data-breakpoint="medium" data-x="10" data-y="45" class="coord"></div>
  * 			<div data-breakpoint="large" data-x="10" data-y="45" class="coord"></div>
  * 			<div data-breakpoint="xlarge" data-x="10" data-y="45" class="coord"></div>
  * 			<div data-breakpoint="xxlarge" data-x="10" data-y="45" class="coord"></div>
  * 		</div>
  * 	</div>
  * </div>
  *
  * @example Example: the target that gets toggled.
  * <!-- You probably want to use position: relative on this container element.-->
  * <div class="info">
  * 	<!--
  * 		This element's ID corresponds with a .point's data-target attribute.
  * 		You likely want to show these cards in the same spot, so use something like:
  * 		position: absolute;
  * 		top: 0;
  * 		visibility: hidden;
  * 		opacity: 0;
  * 		That's the minimum CSS needed for a info card.
  * 	-->
  * 	<div class="info-card" id="content-1"></div>
  * </div>
  **/
jQuery(document).ready(function($) {
	// Create the object that holds everything
	var interactive_image = new Object( {
		// General settings
		settings: {
			unit : '%',	// What unit to use, px or %?
			elements: { // Set up elements and selectors
				map : $('#interactive-image'),
				container : $('#breakpoints'),
				points : $('.point', this.container),
				coord : '.coord',
				icon : "<div class=\"icon\"><i class=\"fa fa-plus\"></i></div>"
			},
			// Set up breakpoints
			breakpoints : {
				medium :  1024,
				large:  1280,
				xlarge :  1440,
				xxlarge:  1920
			},
			// Set up base styles for dots and targets
			styles : {
				// Dot styles
				dots: {
					standardStyle: { width: 60 }, // Default/initial styles
					breakpoints: {	// Styles for in-between breakpoints
						medium: 	{ width: 60, },
						large: 		{ width: 58, },
						xlarge: 	{ width: 55, },
						xxlarge :	{ width: 50, },
					}
				},
				targets : { // Target toggle styles
					active : { visibility: "visible", opacity: 1 }, // Active/shown styles
					not_active: { visibility: "hidden", opacity: 0 } // Not active/hidden styles
				}
			}
		},
		previous: { // Set up history object
			dot: $('<div/>'),
			target : $('<div/>')
		},
		// Calculates the current breakpoint based on window size
		calculatePosition : function($elem) {
			// Set up variables to make everything easier to write
			var breakpoints = this.settings.breakpoints,
				unit = this.settings.unit,
				styles = this.settings.styles.dots.breakpoints,
				$width = $(window).width(),
				coords = { // Set up a styling object for the left and top positions
					medium : { left: 0, top: 0 },
					large: 	 { left: 0, top: 0 },
					xlarge:  { left: 0, top: 0 },
					xxlarge: { left: 0, top: 0 },
				};
			// Loop through each breakpoint of a element
			$(this.settings.elements.coord, $elem).each(function(key, value){
				var coord = $(value); // This is a breakpoint element within a dot
				switch(coord.data('breakpoint')){ // Switch cases based on breakpoint
					/**
					 * Every loop we get the next breakpoint name
					 * Sets the correct left and top positions per breakpoint
				     */
					case 'medium':
						coords.medium.left = coord.data('x')+unit;
						coords.medium.top = coord.data('y')+unit;
					break;
					case 'large':
						coords.large.left = coord.data('x')+unit;
						coords.large.top = coord.data('y')+unit;
					break;
					case 'xlarge':
						coords.xlarge.left = coord.data('x')+unit;
						coords.xlarge.top = coord.data('y')+unit;
					break;
					case 'xxlarge':
						coords.xxlarge.left = coord.data('x')+unit;
						coords.xxlarge.top = coord.data('y')+unit;
					break;
					default:
						coords.xxlarge.left = coord.data('x')+unit;
						coords.xxlarge.top = coord.data('y')+unit;
					break;
				}
			});
			// We want this to always run, the magic happens in the case statements
			switch(true){
				// Calculate on what breakpoint we are at the moment and set the position accordingly
				case ($width > (breakpoints.medium -1) && $width < (breakpoints.large)):
					$elem.css(coords.medium);
					this.styleDot($elem, styles.medium);
				break; // Medium breakpoint
				case ($width > (breakpoints.large -1) && $width < (breakpoints.xlarge)):
					$elem.css(coords.large);
					this.styleDot($elem, styles.large);
				break; // Large breakpoint
				case ($width > (breakpoints.xlarge -1) && $width < (breakpoints.xxlarge)):
					$elem.css(coords.xlarge);
					this.styleDot($elem, styles.xlarge);

				break; // XLarge breakpoint
				case ($width > breakpoints.xxlarge):
					$elem.css(coords.xxlarge);
					this.styleDot($elem, styles.xxlarge);
				break; // XXLarge breakpoint
				default:
					$elem.css(coords.xxlarge);
					this.styleDot($elem, styles.xxlarge);
				break;	// Default/XXLarge breakpoint
			}
		},
		// Provides functionality for the default selected dot
		activateDefault: function ($elem){
			if($elem.data('active') == '1'){
				$elem.trigger('click');
				$elem.addClass('active');
			}
		},
		// Create click event for the elements on screen.
		createEvent : function($elem){
			// Define some variables since we can't use 'this', the object call, inside the event function
			var object = this,
			 	previous = object.previous,
			 	settings = object.settings,
				targets = settings.styles.targets;
			// Create the click event for the current element
			$elem.on('click', function() {
				// Set up click history so no more than one item can be active at one time
				var $currentTarget = $($elem.data('target')),
					$previousDot = previous.dot,
				 	$previousTarget = previous.target;
				/**
				 * Make sure the previous target is the same
				 * as the current one
				 **/
				if($previousDot.data('target') == $elem.data('target')){
					previous.dot = $elem;
					previous.target = $($elem.data('target'));
					// Toggle current target between active and inactive.
					if(!$elem.hasClass('active')){
						$currentTarget.css(targets.active);
						$elem.addClass('active');
					}
					else {
						$currentTarget.css(targets.not_active);
						$elem.removeClass('active');
					}
				}
				else {
					// Deactivate the previous elements
					previous.dot.removeClass('active');
					previous.target.css(targets.not_active);
					// And activate the current elements
					$elem.addClass('active');
					$currentTarget.css(targets.active);
					/**
					 * Store the current element as the previous
					 * one for the next operation
					 **/
					previous.dot = $elem;
					previous.target = $($elem.data('target'));
				}
			});
			// Set up the window resize event with a small delay to prevent some slowdown.
			$(window).on('resize', function(){
				setTimeout(function(){
					// Recalculate the current position of the elements
					object.calculatePosition($elem);
				}, 400)
			});
		},
		// Set the unit so we can use it
		setUnit: function(){
			var container = this.settings.elements.container;
			if(container.data('unit')){
				this.settings.unit = container.data('unit');
			}
		},
		//  Generate the needed styles for the current elements
		createDot  : function($elem) {
			var settings = this.settings;
			this.styleDot($elem, settings.styles.dots.standardStyle); // Calculate some logical values to minimize repetition.
			$($elem).append($(settings.elements.icon)); // Apply pre-defined settings
		},
		// General style (re-)calculating function for current element
		styleDot: function($elem, settings){
			$elem.css({
				height: settings.width,	// Height is same as width
				fontSize: settings.width / 2  + "px", // Font size is half the width
				lineHeight: settings.width + "px", // Line height is same as width
			});
			$elem.css(settings); // Apply the rest of the settings
		},
		// Create a dot element and place it
		placeDot : function($elem) {
			this.createDot($elem); 			// Create the dot and put it on the image
			this.createEvent($elem);		// Create click and resize events
			this.calculatePosition($elem);	// (Re-)calculate position
			this.activateDefault($elem);	// If defined activate the default button
		}
	});
	// Initialize the object
	var $image = interactive_image;
		$elements = $image.settings.elements;
		$points = $elements.points;
	// Make sure the correct unit is set, otherwise use default unit
	$image.setUnit();

	// Loop through each dot
	$points.each(function(key, value) {
		// Assign current values to variables
		var point = $(value),
			target  = point.data('target');
		// Don't display a dot if the target doesn't exist
		if($('' + target).length > 0){
			$image.placeDot(point);	// Place the dot in the correct position
		}
	});
});
