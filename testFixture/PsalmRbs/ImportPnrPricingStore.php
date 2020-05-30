<?php
namespace PsalmRbs;

/**
 * @psalm-type PtcBlock = array{
 *     ptc: string,
 *     quantity: int,
 *     baseFare: array{currency: string, amount: string},
 *     netPrice: array{currency: string, amount: string},
 * }
 *
 * @psalm-type ImportPnrPricingStoreResult = array{
 *    pricingModifiers: string[],
 *    storeNumber: int,
 *    ptcBlocks: PtcBlock[],
 * }
 */

class ImportPnrPricingStore
{

}