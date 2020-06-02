<?php
namespace TypeDefs;

/**
 * @psalm-type PurchaseShape = array{
 *     kind: string,
 *     price: array{currency: string, amount: string},
 *     dt: \DateTime,
 * }
 *
 * @psalm-type CustomerShape = array{
 *     name: string,
 *     age: int,
 *     purchases: PurchaseShape,
 * }
 */

class Customer
{

}