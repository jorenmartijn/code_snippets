<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Util\Calendar;
use AppBundle\Entity\Hour;
use AppBundle\Entity\User;
use AppBundle\Entity\Calendar as CalendarEntity;
use AppBundle\Util\HourTools;

class CalendarController extends Controller
{
	// Rows and columns for this calendar layout
	public $calendar_rows = 6;
	public $calendar_columns = 7;
	private $filter_users = ['user_1', 'user_2'];

	// Counters
	private $days_counter = 1;
	private $days_next_month = 1;

	// Metadata
	private $days_in_month;
	private $days_in_previous_month;
	private $month;
	private $year;

	// Days and months
	private $days = [
		'Mon' => ['number' => 1, 'name' => 'Monday'],
		'Tue' => ['number' => 2, 'name' => 'Tuesday'],
		'Wed' => ['number' => 3, 'name' => 'Wednesday'],
		'Thu' => ['number' => 4, 'name' => 'Thursday'],
		'Fri' => ['number' => 5, 'name' => 'Friday'],
		'Sat' => ['number' => 6, 'name' => 'Saturday'],
		'Sun' => ['number' => 7, 'name' => 'Sunday']
	];
	private $months = [
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
	 * @Route("/", name="homepage"),
 	 * @Route("/calendar/{month}/{year}", name="calendar_index")
     */
    public function indexAction($month = null, $year = null)
    {
		if(is_null($month) || is_null($year)){
			$month = date('m');
			$year = date('Y');
		}
        return $this->render('days/index.html.twig', [
			'calendar' => $this->create((int) $month, (int) $year)
        ]);
    }

	/**
	 * Create a calendar for the current month
	 * This is the function you need to call when using the class (Calendar::create())
	 * @param $month (int) the month you want to use
	 * @param $year (int) the year you want to use
	 * @return (array) a associative array containing relevant calendar data/output
	 */
	public function create( $month,  $year) {
		$this->setMonth($month);
		$this->setYear($year);

		// Define variables
		$current_month			= date('M', mktime(0, 0, 0, $this->month, 1, $this->year));
		$this->days_in_month 	= date('t', mktime(0, 0, 0, $this->month, 1, $this->year));
		$first_day 				= date('D', mktime(0, 0, 0, $this->month, 1, $this->year));
		// Return our calendar structure
		return [
			'meta' => [
				'year' => $this->year,
				'month' => $this->months[$current_month],
				'days_in_month' => $this->days_in_month,
				'month_start_at' =>  $this->days[$first_day],
			],
			'navigation' => [
				'next' => $this->nextMonth(),
				'prev' => $this->prevMonth(),
			],
			'labels' => [
				'months' => $this->months,
				'days' => $this->days,
			],
			'output' => $this->generateGrid($this->days[$first_day]['number'])
		];
	}
	/**
	 * Generates a cell in the grid
	 * @param $row (int) current row
	 * @param $column (int) current column
	 * @param $start_at (int) which day to start at
	 * @param $month (int) current month
	 * @param $year
	 * @return (array) contents of a grid cell
	 */
	private function generateCell($row, $column, $start_at) {
		// Previous and next month
		$next = (object) $this->nextMonth();
		$previous  = (object) $this->prevMonth();
		// Store the number of days in the previous month
		$this->days_in_previous_month  = date('t', mktime(0, 0, 0, $previous->month, 1, $previous->year));
		switch(true) {
			case $row == 1 && $column < ($start_at): // Is the current cell for the previous month?
				$previous_day = ($column <= $start_at )?  $this->days_in_previous_month  - ($start_at - ($column + 1)): $this->days_in_previous_month;
				$day_code  = $this->dayCode( $previous_day, $previous->month, $previous->year);
				$weeknumber = $this->weekNumber( $previous_day, $previous->month, $previous->year);
				$weekend = $this->isWeekend($day_code);
				$compareDates =$this->compareDates(new \DateTime("$previous_day-$previous->month-$previous->year"));
				$future_or_past = ($compareDates == 'future')? 'future' : $compareDates;
				$previous_or_today = ($this->isCurrentOrToday($previous_day, $previous->month, $previous->year) == "today")? "previous-month today": 'previous-month';
				return $this->outputCell($previous_or_today, $previous_day, $previous->month, $previous->year, $day_code, $weekend, $weeknumber, $future_or_past);
			break;
			case $this->days_counter > $this->days_in_month: // Is the current cell for the next month?
				$day_code  = $this->dayCode( $this->days_next_month, $next->month, $next->year);
				$weekend = $this->isWeekend($day_code);
				$weeknumber = $this->weekNumber( $this->days_next_month, $next->month, $next->year);
				$day_number = $this->days_next_month;
				$compareDates =$this->compareDates(new \DateTime("$this->days_next_month-$next->month-$next->year"));
				$this->days_next_month++;

				return $this->outputCell('next-month', $day_number, $next->month, $next->year, $day_code, $weekend, $weeknumber, $compareDates);
			break;
			default: // Current month cell
				$day_code  = $this->dayCode($this->days_counter, $this->month,  $this->year);
				$weeknumber  = $this->weekNumber($this->days_counter, $this->month, $this->year);
				$weekend = $this->isWeekend($day_code);
				$current_or_today = $this->isCurrentOrToday($this->days_counter, $this->month, $this->year);
				$day_number = $this->days_counter;
				$compareDates =$this->compareDates(new \DateTime("$this->days_counter-$this->month-$this->year"));
				$future_or_past = ($current_or_today == 'today')? null : $compareDates;
				$this->days_counter++;
				return $this->outputCell($current_or_today, $day_number, $this->month, $this->year, $day_code, $weekend, $weeknumber, $future_or_past);
			break;
		}
	}

	/**
	 * Calculate the rows and columns for the current month
	 * @param $start_at (int) on which day does the month start_at
	 * @param $month (int) the current month
	 * @param $year (int) the current year
	 * @return (array) a multi-dimensional array representing the calendar structure
	 */
	private function generateGrid( $start_at )  {
		$calendar_output = [];	// Output array
		// Days in previous month so we know what to count backwards from
		for($row = 1; $row < ($this->calendar_rows + 1);$row++) {	// Generate a row
			$calendar_output[$row] = [];
			for($column = 1; $column < ($this->calendar_columns + 1); $column++){
				$calendar_output[$row][$column] = $this->generateCell($row, $column, $start_at);
			}
		}
		// Return our resulting multi-dimensional array
		return $calendar_output;
	}

	/**
	 * Standardised function to generate the output array of a cell
	 * @param $status (string) a status text/HTML class
	 * @param $day (int) the current day
	 * @param $month (int) the current month
	 * @param $year (int) the current year
	 * @param $day_name (string) current day's name, lower-case
	 * @param $weekend (boolean) are we on a weekday or in the weekend
	 * @param $weeknumber (int) the current weeknumber we're on
	 * @return (array) associative array with the params in it
	 */
	private function outputCell($status, $day, $month, $year, $day_name, $weekend, $weeknumber, $past_or_future) {
		$date =  new \DateTime("$year-$month-$day");
		$users = $this->getDoctrine()->getRepository(User::class)->findAll();

		$this->calendar = $this->getDoctrine()->getRepository(CalendarEntity::class)->findOneBy([
			'day' => $date
		]);
		// Check if the day exists, else create it to avoid errors.
		if(is_null($this->calendar)){
			$em = $this->getDoctrine()->getManager();
			$this->calendar = new CalendarEntity();
			$this->calendar->setDay($date);
			$this->calendar->setComplete(false);
			$em = $this->getDoctrine()->getManager();
			$em->persist($this->calendar);
			$em->flush();
		}
		$result = [];

		foreach($users as $user) {
			//dump($user);

			if(!in_array($user->getUserName(), $this->filter_users)){
				$hours = $this->getDoctrine()->getRepository(Hour::class)->findBy([
					'calendar' => $this->calendar->getId(),
					'userId' => $user->getId()
				]);
				$result[] = [
					'user' => $user,
					'hours' => $hours,
					'total' => HourTools::getTotalHours($hours)
				];
			}
		}
		return [
			'hours' => $result,
			'meta' => [
				'weekend' => $weekend,
				'weeknumber' => $weeknumber
			],
			'date' => [
				'day' => $day,
				'month' => $month,
				'year' => $year
			],
			'classes' => [
				'base' => 'day',
				'status' => $status,
				'weekend' => ($weekend)? 'weekend': 'weekday',
				'day_name' => $day_name,
				'past_or_future' => $past_or_future
			]
		];
	}

	/**
	 * Calculates the previous month and year (if needed)
	 * @param (int) the base month
	 * @param (int) the base year
	 * @return (array) associative array with the new month and year
	 */
	private function prevMonth () {
		$year  = ($this->month -1 < 1)? ($this->year -1): $this->year;
		$month = ($this->month -1 < 1)? 12 : ($this->month -1);
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
	private function nextMonth() {
		$year = ($this->month +1 > 12)? ($this->year + 1) : $this->year;
		$month = ($this->month + 1 > 12)? 1: ($this->month + 1);
		return [
			'month' => $month,
			'year' => $year
		];
	}

	/**
	 * Standardised weeknumber function
	 * @param $day (int) current day
	 * @param $month (int) current month
	 * @param $year (int) current year
	 */
	private function weekNumber($day, $month, $year) {
		return date('W', mktime(0, 0, 0, $month, $day, $year));
	}

	/**
	 * Generate standardised day code
	 * @param $day (int) current day
	 * @param $month (int) current month
	 * @param $year (int) current year
	 * @return (string) day code
	 */
	private function dayCode($day, $month, $year, $lowercase = true) {
		if($lowercase) {
			return strtolower(date('l', mktime(0, 0, 0, $month, $day, $year)));
		}
		else {
			return date('l', mktime(0, 0, 0, $month, $day, $year));
		}
	}

	/**
	 * Is it weekend yet?!
	 * @param $day_code (string) the current day's day_code
	 * @return (boolean) if it's weekend or not
	 */
	private function isWeekend($day_code) {
		return ($day_code == "saturday" || $day_code == "sunday")? true: false;
	}

	/**
	 * Is the current day today?
	 * @param $day (int) current day
	 * @param $month (int) current month
	 * @param $year (int) current year
	 * @return (string) if the day is today or in the current month
	 */
	private function isCurrentOrToday($day, $month, $year) {
		return (date('jnY') == "$day"."$month"."$year")? "today" : "current-month";
	}
	/**
	 * Compares a date to the current date (as in, today)
	 * @param $compare DateTime the date to compare to today
	 * @return (string) if the day is in the future or the past
	 */
	private function compareDates(\DateTime $compare){
		$today = new \DateTime('now');
		if($compare > $today){
			return 'future';
		}
		else {
			return 'past';
		}
	}

	/**
	 * Sets the current month
	 * @param $month (int) current month
	 */
	private function setMonth($month) {
		if(is_integer($month)){
			$this->month = $month;
		}
		else {
			throw new Exception('$month needs to be a integer.');
		}
	}

	/**
	 * Sets the current year
	 * @param $year (int) current year
	 */
	private function setYear($year) {
		if(is_integer($year)){
			$this->year = $year;
		}
		else {
			throw new Exception('$year needs to be a integer.');
		}
	}

}
