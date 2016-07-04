<?php
	/**
	 * ValueCounter
	 * Increments the ACF field value for $counter_column with the specified $counter_increment_value for the number of
	 * days that have passed since the page was last loaded, based on the $counter_date_column field.
	 * @author: Joren de Graaf <jorendegraaf@gmail.com>
	 */
	class ValueCounter{
		var $counter_column;
		var $counter_date_column;
		var $counter_increment_min;
		var $counter_increment_max;
		var $post_id;
		/**
		 * Constructor for incrementer.
		 * @param string counter_column The name of the counter field
		 * @param string counter_date_column The name of the date field
		 * @param integer counter_increment_value How much the counter_column has to be incremented each day
		 * @param integer post_id The post ID
		 */
		function __construct($counter_column 			= 	'home_counter_value', 
							$counter_date_column 		= 	'home_counter_date', 
							$counter_increment_min 	= 	114, 
							$post_id){
			$this->counter_column 			= $counter_column;
			$this->counter_date_column  	= $counter_date_column;
			$this->counter_increment_min	= $counter_increment_min;
			$this->counter_increment_max	= 200; //Hardcoded for now 
			$this->post_id					= $post_id;

			try{
				if(!function_exists('acf')){			// Is ACF enabled? 
					throw new Exception('Please enable the plugin Advanced Custom Fields');
				}
				if(!get_field($this->counter_column)){	// Are the ACF fields present on the page?
					throw new Exception('Please provide valid custom fields');
				}
			}
			catch(Exception $e){
				return $e->getMessage();
			}

		}

		/**
		 * Function that returns how many days have passed since the corresponding ACF Date Picker field was last updated
		 * @return integer
		 */
		function days_passed(){
		 	$prevDate = new DateTime(get_field($this->counter_date_column, $this->post_id));// Previous date
		 	$currDate = new DateTime("now");		// Today's date
		 	$interval = $currDate->diff($prevDate); // What's the difference between the two dates?
			return (int) $interval->format("d");	// Format the date as only the number of days.
		}

		/**
		 * Function to check if days have passed since the corresponding ACF date field was last updated
		 * @return boolean
		 */
		function have_days_passed(){
			//$have_days_passed = (($this->days_passed() > 0) ? true : false);
			if($this->days_passed() > 0){
				return true;
			}
			else{
				return false;
			}
		}

		/**
		 * Update field data for the counter and the date.
		 * @return boolean
		 */
		function update_data(){
			if($this->have_days_passed()){	// Have days passed? True? Then update the values 
				$date = new DateTime("now");
				$date = $date->format("Ymd");	// Format the current date in the way ACF expects
				$i = 0;
				$counter = $this->get_counter();	// Get the field data

				while($i <= $this->days_passed()){
					$counter = $counter+rand($this->counter_increment_min, $this->counter_increment_max);
					// Increment the counter with a (random) number between the counter_increment_value and the counter_increment_max values. 				 
					$i++;
				}

				if($counter > $this->get_counter()){	// Is the counter higher than the old value?
					update_field($this->counter_column, $counter, $this->post_id); // Update the counter field with the new information
					update_field($this->counter_date_column, $date, $this->post_id); // Update the date field with the new date
				}

				return true;
			}
			else{
				return false;
			}
		}
		
		/**
		 * Get the counter field value
		 * @return integer
		 */
		function get_counter(){
			return (int) get_post_meta($this->post_id,$this->counter_column)[0];
		}
	}
?>