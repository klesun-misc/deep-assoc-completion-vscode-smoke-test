<?php
namespace PsalmRbs;

/**
 * @psalm-import-type ImportPnrReservationResult from \PsalmRbs\ImportPnrReservation
 * @psalm-import-type ImportPnrPricingStoreResult from \PsalmRbs\ImportPnrPricingStore
 *
 * @psalm-type ImportPnrResult = array{
 *     status: 'success' | 'inaccessiblePnr' | 'internalFailure',
 *     pnrFields: array{
 *         reservation: ImportPnrReservationResult,
 *         pricingStores: ImportPnrPricingStoreResult[],
 *     },
 * }
 */

class ImportPnr
{
    /** @return ImportPnrResult */
    public function execute()
    {
    }
}

(new ImportPnr())->execute()['pnrFields']['reservation']['passengers'][0]['srcDividedBooking'][''];