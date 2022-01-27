<?php namespace App\Helpers;

/**
 * The planner class can be used for planning the output of an event list.
 * For example, see the planEvents function.
 *
 * @author Jonas Dahl <jonadahl@kth.se>
 * @version 2016-11-23
 */
class Planner
{
    /**
     * The start date of the week.
     *
     * @var timestamp integer
     */
    public $startDate;

    /**
     * The end date of the week.
     *
     * @var timestamp integer
     */
    public $endDate;

    /**
     * The events.
     *
     * @var [events]
     */
    public $events;

    /**
     * Constructs a new planner object.
     *
     * @param timestamp $startDate the start date of the week
     * @param timestamp $endDate the end date of the week
     * @param array of events $events        events to schedule
     */
    public function __construct($startDate, $endDate, $events)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->events = $events;
    }

    /**
     * Checks if booking collides with any event in $tracks.
     * A collision is if at any moment event A and event B occurs simultaneously.
     *
     * @param array of Event  $track   $booking will be checked agains all these events
     * @param Event $booking the event to check for collisions agains
     * @return boolean                    true if booking does collides with at
     *                                least one event in $track, false otherwise
     */
    public static function collidesWithTrack($track, $booking)
    {
        // Get timestamps instead of dates, easier to compare
        $bookingStart = strtotime($booking->start);
        $bookingEnd = strtotime($booking->end);

        // Now traverse through the list and return true as soon as we find a collision
        foreach ($track as $element) {
            $elementStart = strtotime($element->start);
            $elementEnd = strtotime($element->end);

            if ($elementStart <= $bookingStart && $elementEnd > $bookingStart)
                return true;
            if ($bookingStart <= $elementStart && $bookingEnd > $elementStart)
                return true;
        }

        // If we survived all way here, we must know that no events collide
        return false;
    }

    /**
     * Splits up every event in $event so that no event span more than one day.
     * Multiday events will therefore be split up in the amount of days they span,
     * and their start and end time adjusted to the new values.
     *
     * @param array of events $events all the events to split ut
     * @return array of events, $events but split up so no multiday events occur
     */
    public function splitEvents()
    {
        $res = [];
        foreach ($this->events as $booking) {
            while (date("Y-m-d", strtotime($booking->start)) != date("Y-m-d", strtotime($booking->end) - 1)
                && strtotime($booking->start) < strtotime($booking->end)) {
                $newBooking = clone $booking;
                $newBooking->end = $booking->end;
                $newBooking->start = date("Y-m-d", strtotime($booking->end) - 1) . ' 00:00:00';
                $booking->end = date("Y-m-d", strtotime($booking->end) - 1) . ' 00:00:00';
                if (strtotime($newBooking->end) <= $this->endDate && strtotime($newBooking->start) >= $this->startDate)
                    $res[] = $newBooking;
            }
            $res[] = $booking;
        }
        $this->events = $res;
    }

    /**
     * Divides all the events in $events array into tracks.
     * There can be different amount of tracks for every day.
     * If an event spans over more than one day, it will be devided and its
     * start and end times adjusted to the split. The event ID may therefore be
     * present in more than one event but not in the same track.
     *
     * The algorithm is greedy. It creates a new track if it cannot fit the event into
     * one of the existing tracks that day. At last, a colspan property is created
     * for every event, if it can span more tracks to the right.
     *
     * @return array of tracks (one array element per day) which is array of events (one array element per track)
     */
    public function planEvents()
    {
        $this->splitEvents();

        // Create initial values
        $tracks = [];
        $numTracks = [];

        foreach ($this->events as $booking) {
            // Init every event with colspan 1, this will be adjusted later if possible
            $booking->colspan = 1;

            // Found will be false until we have added the event to a track
            $found = false;
            $date = date("Y-m-d", strtotime($booking->start));

            // Init the $numTracks array for the date if it has not yet been done
            if (!isset($numTracks[$date])) $numTracks[$date] = 0;

            // Loop through the tracks and see which track we can add it to
            for ($i = 0; $i < $numTracks[$date]; $i++) {
                if (!Planner::collidesWithTrack($tracks[$date][$i], $booking)) {
                    $tracks[$date][$i][] = $booking;
                    $found = true;
                    break;
                }
            }

            // If we couldn't add it to any track, it is time to create a new
            if (!$found) {
                $numTracks[$date]++;
                $tracks[$date][$numTracks[$date] - 1][] = $booking;
            }
        }

        // For presentation purposes, it is nice to expand events that does not collide
        // with any other event to the right. So for every event, check the tracks to the right
        // and increase colspan for every track that does not collide with the booking.
        //
        // Well, it is not nice, however it is only O(n^2) since the 3 foreach loops only loops over every event.
        foreach ($tracks as $date => $dayTracks) {
            foreach ($dayTracks as $key => $track) {
                foreach ($track as $booking) {
                    for ($i = $key + 1; $i < count($tracks[$date]); $i++) {
                        if (!Planner::collidesWithTrack($tracks[$date][$i], $booking)) {
                            $booking->colspan++;
                        } else {
                            break;
                        }
                    }
                }
            }
        }

        return [$tracks, $numTracks];
    }
}
