<?php

namespace Dashifen\WordPress\Themes\Dashifen2025\Entities;

use DateTime;
use Exception;
use DateTimeZone;
use Dashifen\WPDebugging\WPDebuggingTrait;
use Dashifen\WordPress\Themes\Dashifen2025\Theme;

class TimeOfDay extends AbstractEntity
{
  use WPDebuggingTrait;
  
  // in memory, the sunrise, sunset, and tomorrow properties are  always
  // integers.  but, because we want to use the format property, set by
  // promotion in the constructor, to make them look pretty in their get hook,
  // the type hint has to also include strings.
  
  protected(set) int|string $sunrise {
    get {
      return $this->getLocalTime($this->sunrise)->format($this->format);
    }
  }
  
  protected(set) int|string $sunset {
    get {
      return $this->getLocalTime($this->sunset)->format($this->format);
    }
  }
  
  protected(set) int|string $tomorrow {
    get {
      return $this->getLocalTime($this->tomorrow)->format($this->tomorrow);
    }
  }
  
  protected(set) int $timeOfDayNumber;
  protected(set) string $timeOfDay;
  
  /**
   * __construct
   *
   * Uses the database or the sunrise-sunset.org API to get sunrise and sunset
   * times and calculates the site's time of day based on that.
   *
   * @throws Exception
   */
  public function __construct(
    private readonly string $format = 'n/j/Y \a\\t g:ia'
  ) {
    $solarTime = $this->getSolarTime();
    foreach ($solarTime as $property => $value) {
      $this->$property = $value;
    }
  }
  
  /**
   * Returns the array of data we need to identify the relative solar time of
   * day in Dash's rough location.
   *
   * @param bool $forceFetch
   *
   * @return array
   * @throws Exception
   */
  private function getSolarTime(bool $forceFetch = false): array
  {
    $now = $this->getCurrentTimestamp();
    $solarTime = $this->getApiData($forceFetch);
    
    // if it's nighttime, we add 100 to our numeric time of day.  this is so
    // that nighttime "percentages" will be between 100 and 200 when we perform
    // the match statement below.
    
    $numericTimeOfDay = $this->isNight($now, $solarTime->sunset)
      ? $this->calculatePercent($now, $solarTime->sunset, $solarTime->tomorrow) + 100
      : $this->calculatePercent($now, $solarTime->sunrise, $solarTime->sunset);
    
    $timeOfDay = match (true) {
      $numericTimeOfDay <= 25  => 'morning',
      $numericTimeOfDay <= 75  => 'day',
      $numericTimeOfDay <= 100 => 'evening',
      $numericTimeOfDay <= 125 => 'twilight',
      $numericTimeOfDay <= 175 => 'night',
      $numericTimeOfDay <= 200 => 'dawn',
      default                  => 'oops',
    };
    
    // the numeric time of day shouldn't be greater than 200 unless our
    // transient existed when we called getApiData below but the data it held
    // referenced yesterday.  in that case, we want to force the getApiData
    // method to fetch information from the API, so we re-call this method
    // with a set flag as its argument.  otherwise, we merge the pertinent
    // information from our $solarTime object along with the time of day data
    // we calculated herein.
    
    return $numericTimeOfDay > 200
      ? $this->getSolarTime(true)
      : array_merge($solarTime->toArray(), [
        'timeOfDayNumber' => $numericTimeOfDay,
        'timeOfDay'       => $timeOfDay,
      ]);
  }
  
  /**
   * When necessary, accesses the sunrise/sunset API or grabs existing
   * information out of the database.
   *
   * @param bool $forceFetch
   *
   * @return SolarTime
   */
  private function getApiData(bool $forceFetch): SolarTime
  {
    $transient = Theme::SLUG . '-solar-time';
    $solarTime = get_transient($transient);
    
    if ($forceFetch || $solarTime === false) {
      $today = $this->getApiResponse('today');
      $tomorrow = $this->getApiResponse('tomorrow');
      $solarTime = new SolarTime($this->validate($today), $this->validate($tomorrow));
      set_transient($transient, $solarTime, DAY_IN_SECONDS);
    }
    
    return $solarTime;
  }
  
  /**
   * Given a day (i.e., today or tomorrow) gets the API response from the
   * sunrise-sunset.org API.
   *
   * @param string $day
   *
   * @return array
   */
  private function getApiResponse(string $day): array
  {
    $query = [
      'lat'       => 38.8051095,
      'lng'       => -77.0470229,
      'formatted' => 0,
      'date'      => $day,
    ];
    
    $url = 'https://api.sunrise-sunset.org/json?' . http_build_query($query);
    $response = wp_remote_get($url);
    
    // in the unlikely event that we generate a WP_Error with the remote get
    // call, we'll return an empty array.  otherwise, we just return what ever
    // the remote get returned.
    
    return !is_a($response, 'WP_Error') ? $response : [];
  }
  
  /**
   * Returns our response if it's valid or null otherwise.
   *
   * @param array $response
   *
   * @return array|null
   */
  private function validate(array $response): ?array
  {
    return wp_remote_retrieve_response_code($response) === 200
      ? $response
      : null;
  }
  
  /**
   * Returns the current timestamp in UTC.
   *
   * @return int
   * @throws Exception
   */
  private function getCurrentTimestamp(): int
  {
    // the first getTimestamp method is the one we've defined herein.  the
    // second one we call, though, is a method of the DateTime object that
    // is created by ours.
    
    return $this->getTimestamp('UTC')->getTimestamp();
  }
  
  /**
   * Given a timezone and (optionally) a timestamp, returns a timestamp in the
   * specified timezone.
   *
   * @param string   $timezone
   * @param int|null $timestamp
   *
   * @return DateTime
   * @throws Exception
   */
  private function getTimestamp(string $timezone, ?int $timestamp = null): DateTime
  {
    $timezone = new DateTimeZone($timezone);
    $datetime = new DateTime(timezone: $timezone);
    if ($timestamp !== null) {
      
      // if the timestamp isn't null, then we're taking an existing time and
      // converting it into the specified timestamp.  we set our DateTime
      // object's internal time to the one we're given to make that happen.
      
      $datetime->setTimestamp($timestamp);
    }
    
    return $datetime;
  }
  
  /**
   * This straightforward method exists to make code above a little more
   * readable.  It returns true if the current time is after sunset, i.e. if it
   * is night.
   *
   * @param int $now
   * @param int $sunset
   *
   * @return bool
   */
  private function isNight(int $now, int $sunset): bool
  {
    return $now > $sunset;
  }
  
  /**
   * Given a timestamp, now, between sunrise and sunset, returns the percent
   * of the day that has elapsed.
   *
   * @param int $now
   * @param int $sunrise
   * @param int $sunset
   *
   * @return int
   */
  private function calculatePercent(int $now, int $sunrise, int $sunset): int
  {
    // to find the amount of time elapsed after $sunrise as represented by
    // $now, we need both the number of seconds between $now and $sunrise and
    // the total number of seconds between $sunset and $sunrise.  once we have
    // that, it's a simple division and multiplication operation to produce a
    // percentage.
    
    return round(($now - $sunrise) / ($sunset - $sunrise) * 100);
  }
  
  /**
   * Given a timestamp in UTC, convert it to Eastern US time.
   *
   * @param int $timestamp
   *
   * @return DateTime
   * @throws Exception
   */
  private function getLocalTime(int $timestamp): DateTime
  {
    return $this->getTimestamp('America/New_York', $timestamp);
  }
  
  /**
   * Returns our properties as an array.
   *
   * @return array
   */
  public function toArray(): array
  {
    return [
      'sunrise'         => $this->sunrise,
      'sunset'          => $this->sunset,
      'tomorrow'        => $this->tomorrow,
      'timeOfDayNumber' => $this->timeOfDayNumber,
      'timeOfDay'       => $this->timeOfDay,
    ];
  }
}
