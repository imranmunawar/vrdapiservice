<?php
	// use Torann\GeoIP\GeoIPFacade;
	use App\RecruiterScheduleInvite;
	use App\Fair;

	class AppHelper {

    public static function PageTitle($url)
    {
        $str = file_get_contents($url);
		  if(strlen($str)>0){
		    $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
		    preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title); // ignore case
		    return $title[1];
		  }
    }
	public static function localTime($timezone, $time, $fair_timezone)
    {
			date_default_timezone_set($timezone);
			$localTime = new \DateTime($time, new \DateTimeZone($fair_timezone));
      $localTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			return $localTime;
    }

    public static function dateScheduling($date, $u_id,$timezone)
    {
		date_default_timezone_set($timezone);
		$data = RecruiterScheduleInvite::where('u_id', '=', $u_id)->first();
		$fair_timezone = $data->FairDetails->timezone;
		//Converrting the Start time
		$date = new \DateTime($date, new \DateTimeZone($fair_timezone));
        $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		// $start_time = $start_time->format('h:i A');
		return $date;
    }

	public static function startTimeScheduling($start_time, $u_id,$timezone)
    {
		date_default_timezone_set($timezone);
		$data = RecruiterScheduleInvite::where('u_id', '=', $u_id)->first();
		$fair_timezone = $data->FairDetails->timezone;
		//Converrting the Start time
		$start_time = new \DateTime($start_time, new \DateTimeZone($fair_timezone));
        $start_time->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		// $start_time = $start_time->format('h:i A');
		return $start_time;
    }

		public static function endTimeScheduling($end_time, $u_id,$timezone)
		{
			date_default_timezone_set($timezone);
			$data = RecruiterScheduleInvite::where('u_id', '=', $u_id)->first();
			$fair_timezone = $data->FairDetails->timezone;
			//Converrting the End time
			$end_time = new \DateTime($end_time, new \DateTimeZone($fair_timezone));
			$end_time->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			// $end_time = $end_time->format('h:i A');
			return $end_time;
		}
		public static function startTimeSchedulingforCandidate($start_time, $fair_id,$timezone)
    {
		date_default_timezone_set($timezone);
		$data = Fair::find($fair_id)->first();
		$fair_timezone = $data->timezone;
		//Converrting the Start time
		$start_time = new \DateTime($start_time, new \DateTimeZone($fair_timezone));
        $start_time->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		// $start_time = $start_time->format('h:i A');
		return $start_time;
    }

		public static function endTimeSchedulingforCandidate($end_time, $fair_id,$timezone)
		{
			date_default_timezone_set($timezone);
			$data = Fair::find($fair_id)->first();
			$fair_timezone = $data->timezone;
			//Converrting the End time
			$end_time = new \DateTime($end_time, new \DateTimeZone($fair_timezone));
			$end_time->setTimezone(new \DateTimeZone(date_default_timezone_get()));
			// $end_time = $end_time->format('h:i A');
			return $end_time;
		}
}

?>
