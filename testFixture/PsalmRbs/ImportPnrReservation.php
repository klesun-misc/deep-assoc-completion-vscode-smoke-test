<?php
namespace PsalmRbs;

/**
 * @psalm-import-type  ImportPnrResult from \PsalmRbs\ImportPnr
 * @psalm-type Passenger = array{
 *     lastName: string,
 *     firstName: string,
 *     srcDividedBooking: ImportPnrResult,
 * }
 *
 * @psalm-type Segment = array{
 *     airline: string,
 *     departurePt: string,
 *     departureDt: \DateTime,
 *     arrivalPt: string,
 *     arrivalDt: \DateTime,
 * }
 *
 * @psalm-type ImportPnrReservationResult = array{
 *     passengers: Passenger[],
 *     itinerary: Segment[],
 * }
 */

class ImportPnrReservation
{
    /** @return ImportPnrReservationResult */
    public function execute()
    {
    }
}