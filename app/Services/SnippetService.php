<?php

namespace App\Services;

use App\Models\Snippet;
use Carbon\Carbon;

class SnippetService{

	/**
	 * Object of Snippet class for working with database
	 *
	 * @var Snippet
	 */
	private $snippet;

	/**
	 * Create a new instance of SnippetService
	 *
	 * @param Snippet $snippet
	 * @return void
	 */

	private static $weekdays = array('Sunday' , 'Monday' , 'Tuesday' , 'Wednesday' , 'Thursday' , 'Friday' , 'Saturday');

	public function __construct()
	{
		$this->snippet = new Snippet();

	}

	/**
	 * Get a snippet using the token
	 *
	 * @param string @token
	 * @return Snippet
	 */
	public function getSnippetByToken($token)
	{
		return $this->snippet->where('api_token', $token)
			->first();
	}

	/**
	 * Get a snippet using subdomain
	 *
	 * @param string $subdomain
	 * @return Snippet
	 */
	public function getSnippetBySubdomain($subdomain)
	{
		return $this->snippet->where('subdomain', $subdomain)
			->first();
	}


    /**
     * Get a snippet date range
     *
     * @params $dateRange , $offset &$weekDates
     * @return Array
     */

	public static function createSnippetDateRange($dateRange,$offset,$customerTimezone) {

        $snippetDate = json_decode($dateRange);
        $now = Carbon::now()->setTimezone('UTC')->addHour($offset);

        $weekDates = [];

        $startTime = explode(":",trim($snippetDate->dateRangeStart));
        $endTime   = explode(":",trim($snippetDate->dateRangeEnd));


        $dates = [];

        foreach ($snippetDate->weekDays as $day) {

            $date = new \stdClass();
            $start =   Carbon::parse('next ' . $day)->setTime($startTime[0], $startTime[1])->format('Y-m-d H:i:s');
            $end   =   Carbon::parse('next ' . $day)->setTime($endTime[0], $endTime[1])->format('Y-m-d H:i:s');


            $date->start =   Carbon::createFromFormat('Y-m-d H:i:s', $start, $customerTimezone)->setTimezone('UTC')->addHour($offset);
            $date->end   =   Carbon::createFromFormat('Y-m-d H:i:s', $end, $customerTimezone)->setTimezone('UTC')->addHour($offset);

            if(Carbon::parse($date->end)->setTime(0,0)->gt(Carbon::parse($date->start)->setTime(0,0))) {
                $finalDate =  new \stdClass();
                //$finalDate->weekDay = $date->start->format('l');
                //$finalDate->start = $date->start->format('H:i');        $finalDate->start = self::mergeDateForToday($date,$now);

                $finalDate->weekDay = $date->start->format('l');
                $finalDate->end = '23:59';
                $finalDate->start = $date->start->format('H:i');
                $day = $date->start->format('l');

                //$finalDate = self::mergeDateForToday($finalDate,$day,$offset);

                if($finalDate) {
                    $dates[] = $finalDate;
                    $weekDates[$date->start->format('l')][] = $finalDate;
                }





                $finalDate =  new \stdClass();
                //$finalDate->weekDay = $date->end->format('l');

                //$finalDate->start = '00:01';
                $finalDate->start = '00:01';
                $finalDate->end = $date->end->format('H:i');
                $day = $date->start->format('l');
                //$finalDate = self::mergeDateForToday($finalDate,$day,$offset);

                if($finalDate) {
                    $dates[] = $finalDate;
                    $weekDates[$date->end->format('l')][] = $finalDate;
                }

            } else {

                $finalDate =  new \stdClass();
                $day = $date->start->format('l');
                $finalDate->start = $date->start->format('H:i');
                $finalDate->end = $date->end->format('H:i');

                //$finalDate = self::mergeDateForToday($finalDate,$day,$offset);

                if($finalDate) {
                    $weekDates[$date->start->format('l')][] = $finalDate;
                }

            }

        }

        return $weekDates;

    }


    public static function createCustomSnippetDateRange ($dateRange, $offset, $customerTimezone, $forLocalSnippet = false, $forLocalSnippetsTimezones = false)
    {
	    /*
	     * if $forLocalSnippet is true then we not marging dates with today
	     * if $forLocalSnippetsTimezones is true then we not changing dates to user timezone
	     *
	     * */

        $snippetDate = json_decode($dateRange);

        $dayData = [];
        foreach ($snippetDate as $dates) {
            $date = get_object_vars($dates);
            $time = array_keys($date)[0];

            $days = array_values(array_values($date)[0]);
            $days = array_fill_keys($days,$time);

            $dateRangeStart = explode("-",trim($time))[0];
            $dateRangeEnd = explode("-",trim($time))[1];
            $startTime = explode(":",trim($dateRangeStart));
            $endTime   = explode(":",trim($dateRangeEnd));

            foreach ($days as $day => $time ) {
                $date = new \stdClass();
                $start =   Carbon::parse('next ' . $day)->setTime($startTime[0], $startTime[1])->format('Y-m-d H:i:s');
                $end   =   Carbon::parse('next ' . $day)->setTime($endTime[0], $endTime[1])->format('Y-m-d H:i:s');

                if ($forLocalSnippet and !$forLocalSnippetsTimezones) {
                    $date->start =   Carbon::createFromFormat('Y-m-d H:i:s', $start);
                    $date->end   =   Carbon::createFromFormat('Y-m-d H:i:s', $end);
                } else {
                    $date->start =   Carbon::createFromFormat('Y-m-d H:i:s', $start, $customerTimezone)->setTimezone('UTC')->addHour($offset);
                    $date->end   =   Carbon::createFromFormat('Y-m-d H:i:s', $end, $customerTimezone)->setTimezone('UTC')->addHour($offset);
                }

                if(Carbon::parse($date->end)->setTime(0,0)->gt(Carbon::parse($date->start)->setTime(0,0))) {
                    $dayName = $date->start->format('l');
                    $finalArray = [];
                    $finalDate1 =  new \stdClass();

                    $finalDate1->start = $date->start->format('H:i');
                    $finalDate1->end = '23:59';

                    if ($finalDate1) {
                        $finalArray[] = $finalDate1;
                    }

                    $finalDate2 =  new \stdClass();

                    $finalDate2->start = '00:00';
                    $finalDate2->end = $date->end->format('H:i');

                    if ($finalDate2) {
                        $finalArray[] = $finalDate2;
                    }

                    if(count($finalArray)) {
                        if (! array_key_exists($dayName,$dayData)) {
                            $dayData[$dayName] = $finalArray ;
                        } else {
                            $mergedWithDate1 = self::mergeDates($dayData[$dayName],$finalDate1);
                            $merged = self::mergeDates($mergedWithDate1,$finalDate2);
                            $dayData[$dayName] = $merged;
                        }
                    }
                } else {
                    $finalDate =  new \stdClass();
                    // $finalDate->weekDay = $date->start->format('l');
                    $finalDate->start = $date->start->format('H:i');
                    $finalDate->end = $date->end->format('H:i');
                    $dayName = $date->start->format('l');

                    if ($finalDate) {
                        if (! array_key_exists($dayName,$dayData)) {
                            $dayData[$dayName] = [$finalDate];
                        } else {
                            $merged = self::mergeDates($dayData[$dayName],$finalDate);
                            $dayData[$dayName] = $merged;
                        }
                    }
                }
            }
        }

        return $dayData;
    }

    public static function getNextWeekday($date, $holidayModeEnd) {
	    while ($holidayModeEnd->gt($date)) {
            $date = $date->addDays(7);
        }

        return $date->format('Y-m-d H:i:s');
    }

    public static function mergeDates($datesData, $date) {
	    $now = Carbon::now()->format('Y-m-d ');
	    $compareDateStart  = Carbon::createFromFormat('Y-m-d H:i:s', $now . $date->start . ":00");
	    $compareDateEnd  = Carbon::createFromFormat('Y-m-d H:i:s', $now . $date->end . ":00");

        foreach ($datesData as $key => $dateData) {
            $DateStart  = Carbon::createFromFormat('Y-m-d H:i:s', $now . $dateData->start . ":00");
            $DateEnd  = Carbon::createFromFormat('Y-m-d H:i:s', $now . $dateData->end . ":00");

            if ($compareDateStart->between($DateStart,$DateEnd) and $compareDateEnd->between($DateStart,$DateEnd)) {
                return $datesData;
            }

            if ($DateStart->between($compareDateStart,$compareDateEnd) and $DateEnd->between($compareDateStart,$compareDateEnd)) {
                unset($datesData[$key]);
            } elseif ($DateStart->between($compareDateStart,$compareDateEnd) and !$DateEnd->between($compareDateStart,$compareDateEnd)) {
                $compareDateEnd = $DateEnd;
                unset($datesData[$key]);
            } elseif (!$DateStart->between($compareDateStart,$compareDateEnd) and $DateEnd->between($compareDateStart,$compareDateEnd)) {
                $compareDateStart = $DateStart;
                unset($datesData[$key]);
            }
	    }

        $finalDate =  new \stdClass();

        $finalDate->start = $compareDateStart->format("H:i");
        $finalDate->end = $compareDateEnd->format("H:i");

        $datesData[] = $finalDate;
        $datesData = array_values($datesData);

        return $datesData;
    }

    public static function mergeDateForToday($date,$day,$offset) {
        $finalDate =  new \stdClass();
        $now = Carbon::now()->setTimezone('UTC')->addHour($offset);
        if ($day == $now->format('l')) {
            $start = explode(":",trim($date->start));
            $end = explode(":",trim($date->end));

            $todayStartHour = intval($start[0]);
            $todayStartMinute = intval($start[1]);

            $todayEndHour = intval($end[0]);
            $todayEndMinute = intval($end[1]);

            $todayStart = Carbon::now()->setTimezone('UTC')->addHour($offset)->setTime($todayStartHour,$todayStartMinute);
            $todayEnd = Carbon::now()->setTimezone('UTC')->addHour($offset)->setTime($todayEndHour,$todayEndMinute);

            if ($now->gt($todayStart) and $now->gt($todayEnd)) {
                return false;
            } elseif ($now->gt($todayStart)) {
                $h = $now->format("H");
                $i = intval($now->format("i"));
                if ($i % 5 == 0) {
                    $finalDate->start = $now->format("H:i");
                } else {
                    $finalDate->start = $h . ':' . ($i + (5 - $i % 5));
                }
                $finalDate->end  = $date->end;
            } else {
                $finalDate->start = $date->start;
                $finalDate->end = $date->end;
            }
        } else {
            $finalDate->start = $date->start;
            $finalDate->end = $date->end;
        }

        return $finalDate;
    }

    public function changeDateRangeTimezone($dareRanges, $fromTimezone, $toTimezone) {
        $now = Carbon::now()->format('Y-m-d ');
	    $fromDate = $dareRanges['dateRangeStart'];
	    $toDate = $dareRanges['dateRangeEnd'];

        $start  = Carbon::createFromFormat('Y-m-d H:i:s', $now . trim($fromDate) . ":00" ,$fromTimezone)->setTimezone($toTimezone)->format('H:i');
        $end  = Carbon::createFromFormat('Y-m-d H:i:s', $now . trim($toDate) . ":00", $fromTimezone)->setTimezone($toTimezone)->format('H:i');

        $dareRanges['dateRangeStart'] = $start;
        $dareRanges['dateRangeEnd'] = $end;

        return $dareRanges;
    }

    public function changeCustomDateRangeTimezone($dareRanges, $fromTimezone, $toTimezone) {
        $now = Carbon::now()->format('Y-m-d ');
        $final = array();
        foreach ($dareRanges as &$dareRange) {
            $current = array();
            $date = explode('-',array_keys($dareRange)[0]);
            $days = array_values($dareRange)[0];

            $start  = Carbon::createFromFormat('Y-m-d H:i:s', $now . trim($date[0]) . ":00" ,$fromTimezone)->setTimezone($toTimezone)->format('H:i');
            $end  = Carbon::createFromFormat('Y-m-d H:i:s', $now . trim($date[1]) . ":00", $fromTimezone)->setTimezone($toTimezone)->format('H:i');

            $current[$start . ' - ' . $end] = $days;
            $final[] = $current;
        }

        return $final;
    }

    public function mergedDataToJson($mergedData) {
	    $main = array();
	    $final = array();

	    foreach ($mergedData as $day => $times) {
	        foreach ($times as $time) {
                $main[$time->start . ' - ' . $time->end][] = $day;
            }
        }

        foreach ($main as $time => $days) {

	        $current = array();
	        $current[$time] = $days;
            $final[] = $current;

        }

        return json_encode($final);
    }
}