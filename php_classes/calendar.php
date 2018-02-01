<?php
namespace AppBundle\Util;
/**
 * Calendar class for use in symfony (but can function independently)
 * @author Joren de Graaf <jorendegraaf@gmail.com>
 * @version 0.8
 */
class Calendar {
	// Rows and columns for this calendar layout
	public $calendar_rows = 6;
	public $calendar_columns = 7;
	// Days and months
	public $days = [
		'Mon' => ['number' => 1, 'name' => 'Monday'],
		'Tue' => ['number' => 2, 'name' => 'Tuesday'],
		'Wed' => ['number' => 3, 'name' => 'Wednesday'],
		'Thu' => ['number' => 4, 'name' => 'Thursday'],
		'Fri' => ['number' => 5, 'name' => 'Friday'],
		'Sat' => ['number' => 6, 'name' => 'Saturday'],
		'Sun' => ['number' => 7, 'name' => 'Sunday']
	];
	public $months = [
		'Jan' => ['number' => 1, 'name' => 'January'],
		'Feb' => ['number' => 2, 'name' => 'February'],
		'Mar' => ['number' => 3, 'name' => 'March'],
		'Apr' => ['number' => 4, 'name' => 'April'],
		'May' => ['number' => 5, 'name' => 'May'],
		'Jun' => ['number' => 6, 'name' => 'June'],
		'Jul' => ['number' => 7, 'name' => 'July'],
		'Aug' => ['number' => 8, 'name' => 'August'],
		'Sep' => ['number' => 9, 'name' => 'September'],
		'Oct' => ['number' => 10, 'name' => 'October'],
		'Nov' => ['number' => 11, 'name' => 'November'],
		'Dec' => ['number' => 12, 'name' => 'December']
	];
	/**
	 * Create a calendar for the current month
	 * This is the function you need to call when using the class (Calendar::create())
	 * @param $month (int) the month you want to use
	 * @param $year (int) the year you want to use
	 * @return (array) a associative array containing relevant calendar data/output
	 */
	public function create( $month,  $year) {
		// Define variables
		$current_month		= date('M', mktime(0, 0, 0, $month, 1, $year));
		$days_in_month 		= date('t', mktime(0, 0, 0, $month, 1, $year));
		$first_day 			= date('D', mktime(0, 0, 0, $month, 1, $year));
		// Generate output array with all the relevant data
		$calendar = [
			'year' => $year,
			'month_start_at' =>  $this->days[$first_day],
			'month' => $this->months[$current_month],
			'months' => $this->months,
			'days' => $this->days,
			'days_in_month' => $days_in_month,
			'next' => $this->nextMonth($month, $year),
			'prev' => $this->prevMonth($month, $year),
			'output' => $this->generateGrid($this->days[$first_day]['number'], $days_in_month, $month, $year)
		];
		// Return out calendar structure
		return $calendar;
	}

	/**
	 * Calculates the previous month and year (if needed)
	 * @param (int) the base month
	 * @param (int) the base year
	 * @return (array) associative array with the new month and year
	 */
	private function prevMonth ( $month, $year ) {
		$year  = (($month - 1) >=  1)? $year : ($year - 1); // Check if the year is the same
		$month = (($month - 1) >= 1)? ($month -1) : 12; // Check if we're on the first month already, if so go to december
		return [
			'month' => $month,
			'year'	=> $year
		];
	}

	/**
	 * Calculates the next month and year (if needed)
	 * @param (int) the base month
	 * @param (int) the base year
	 * @return (array) associative array with the new month and year
	 */
	private function nextMonth( $month, $year ) {
		$year  = (($month + 1) >=  1)? $year : ($year + 1); // Check if the year is the same
		$month = (($month + 1) >= 1)? ($month + 1) : 12; // Check if we're on the first month already, if so go to december
		return [
			'month' => $month,
			'year' => $year
		];
	}

	/**
	 * Standardised function to generate the output array
	 * @param $status (string) a status text/HTML class
	 * @param $day (int) the current day
	 * @param $month (int) the current month
	 * @param $year (int) the current year
	 * @param $day_name (string) current day's name, lower-case
	 * @param $weekend (boolean) are we on a weekday or in the weekend
	 * @param $weeknumber (int) the current weeknumber we're on
	 * @return (array) associative array with the params in it
	 */
	private function output($status, $day, $month, $year, $day_name, $weekend, $weeknumber) {
		return [
			'status' => $status,
			'day' => $day,
			'month' => $month,
			'year' => $year,
			'day_name' => $day_name,
			'weekend' => $weekend,
			'weeknumber' => $weeknumber
		];
	}

	/**
	 * Calculate the rows and columns for the current month
	 * @param $start_at (int) on which day does the month start_at
	 * @param $days_in_month (int) how many days does the current month have
	 * @param $month (int) the current month
	 * @param $year (int) the current year
	 * @return (array) a multi-dimensional array representing the calendar structure
	 */
	private function generateGrid( $start_at, $days_in_month, $month, $year )  {
		// Previous and next month
		$previous  = $this->prevMonth($month, $year);
		$next = $this->nextMonth($month, $year);
		$day_counter = 1; 		// Counters for days
		$days_next_month = 1; 	// Next month is easy, we just need to count up until we hit the end of the row
		$calendar_output = [];	// Output array
		// Days in previous month so we know what to count backwards from
		$days_in_previous_month  = date('t', mktime(0, 0, 0, $previous['month'], 1, $previous['year']));
		for($i = 1; $i < ($this->calendar_rows + 1);$i++) {	// Generate a row
			// Set up the output variable with rows, then fill the columns/cells
			$calendar_output[$i] = [];
			// Generate columns in the current row
			for($o = 1; $o < ($this->calendar_columns + 1); $o++){
				// Calculate where the starting cell of the first day of the month is
				switch(true) {
					case $i == 1 && $o < ($start_at): // Is the current cell for the previous month?
						// Calculates the current day we're on of the previous month, counting backwards
						$previous_day = ($o <= $start_at )?  $days_in_previous_month  - ($start_at - ($o + 1)): $days_in_previous_month;
						// Generate lowercase day name for CSS class usage
						$day_code  = strtolower(date('l', mktime(0, 0, 0, $previous['month'], $previous_day, $previous['year'])));
						// Generate the week number we're in right now
						$weeknumber  = date('W', mktime(0, 0, 0, $previous['month'], $previous_day, $previous['year']));
						// Calculate if it's currently a weekday or the weekend
						$weekend = ($day_code == "saturday" || $day_code == "sunday")? true: false;
						// Output the final array
						$calendar_output[$i][$o] = $this->output('previous', $previous_day, $previous['month'], $previous['year'], $day_code, $weekend, $weeknumber);
					break;
					case $day_counter > $days_in_month: // Is the current cell for the next month?
						// Generate lowercase day name for CSS class usage
						$day_code  = strtolower(date('l', mktime(0, 0, 0, $next['month'], $days_next_month, $next['year'])));
						// Generate the week number we're in right now
						$weeknumber  = date('W', mktime(0, 0, 0, $next['month'], $days_next_month, $next['year']));
						// Calculate if it's currently a weekday or the weekend
						$weekend = ($day_code == "saturday" || $day_code == "sunday")? true: false;
						// Output the final array
						$calendar_output[$i][$o] = $this->output('next', $days_next_month, $next['month'], $next['year'], $day_code, $weekend, $weeknumber);
						// Incremenent to the next day
						$days_next_month++;
					break;
					default: // Current month cell
						// Generate lowercase day name for CSS class usage
						$day_code  = strtolower(date('l', mktime(0, 0, 0, $month, $day_counter, $year)));
						// Generate the week number we're in right now
						$weeknumber  = date('W', mktime(0, 0, 0, $month, $day_counter, $year));
						// Calculate if it's currently a weekday or the weekend
						$weekend = ($day_code == "saturday" || $day_code == "sunday")? true: false;
						// Check if the current day is today
						$current_or_today = (date('j-n-Y') == "$day_counter-$month-$year")? "today" : "current";
						// Output the final array
						$calendar_output[$i][$o] = $this->output($current_or_today, $day_counter, $month, $year, $day_code, $weekend, $weeknumber);
						// Increment the counter for the current day this month
						$day_counter++;
					break;
				}
			}
		}
		// Return our resulting multi-dimensional array
		return $calendar_output;
	}
}?>