<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DaysController extends Controller
{
	public $calendar_rows = 5;
	public $calendar_columns = 7;
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
     * @Route("/days/index", name="days_index")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('days/index.html.twig', [
			'calendar' => $this->createCalendar((int) date('n'), (int) date('Y')),
			'request' => $request,
			'month' => (int) date('n'),
			'year' => (int) date('Y')
        ]);
    }
	/**
     * @Route("/days/show/{month}/{year}", name="days_show")
     */
	public function showAction( $month,  $year)
	{

		return $this->render('days/index.html.twig', [
			'calendar' => $this->createCalendar((int) $month, (int) $year),

		]);
	}
	/**
	 * Create a calendar for the current month
	 */
	public function createCalendar( $month,  $year) {
		// Define variables
		$current_month		= date('M', mktime(0, 0, 0, $month, 1, $year));
		$days_in_month 		= date('t', mktime(0, 0, 0, $month, 1, $year));
		$first_day 			= date('D', mktime(0, 0, 0, $month, 1, $year));
		$calendar = [
			'year' => $year,
			'month' => $this->months[$current_month],
			'days' => $this->days,
			'days_in_month' => $days_in_month,
			'month_start_at' =>  $this->days[$first_day],
			'output' => $this->calculateDays($this->days[$first_day]['number'], $days_in_month, $month, $year)
		];
		return $calendar;
	}
	/**
	 * Calculate the rows and columns for the current month
	 */
	public function calculateDays( $start_at, $days_in_month, $month, $year )  {
		// Calculate the correct number of days for the previous month
		$previous_year  = (($month - 1) >=  1)? $year : ($year - 1); // Check if the year is the same
		$previous_month = (($month - 1) >= 1)? ($month -1) : 12; // Check if we're on the first month already, if so go to december
		$days_in_previous_month  = date('t', mktime(0, 0, 0, $previous_month, 1, $previous_year));
		// Next month/year
		$next_year  = (($month + 1) >=  1)? $year : ($year + 1); // Check if the year is the same
		$next_month = (($month + 1) >= 1)? ($month + 1) : 12; // Check if we're on the first month already, if so go to december
		// Counters for days
		$day_counter = 1;
		$days_next_month = 1;

		// Output array
		$calendar_output = [];
		// Generate a row
		for($i = 1; $i < ($this->calendar_rows + 1);$i++) {
			$calendar_output[$i] = [];
			// Generate columns in the row
			for($o = 1; $o < ($this->calendar_columns + 1); $o++){
				// Calculate where the starting cell of the first day of the month is
				switch(true) {
					case $i == 1 && $o < ($start_at):
						$previous_day = ($o <= $start_at )?  $days_in_previous_month  - ($start_at - ($o + 1)): $days_in_previous_month;
						$calendar_output[$i][$o] = ['status' => 'previous-month',
													'day' => $previous_day,
												 	'month' => $previous_month,
													'year' => $previous_year];
					break;
					case $day_counter > $days_in_month:
						$calendar_output[$i][$o] = ['status' => 'next-month',
													'day' => $days_next_month,
													'month' => $next_month,
													'year' => $next_year];
						$days_next_month++;
					break;
					default:
					 	if(date('j-n-Y') == "$day_counter-$month-$year"){
							$calendar_output[$i][$o] = ['status' => 'current-day',
														'day' => $day_counter,
														'month' => $month,
														'year' => $year];
						}
						else {
							$calendar_output[$i][$o] = ['status' => 'current-month',
														'day' => $day_counter,
														'month' => $month,
														'year' => $year];
						}
						$day_counter++;
					break;
				}
			}
		}
		return $calendar_output;
	}
}
?>