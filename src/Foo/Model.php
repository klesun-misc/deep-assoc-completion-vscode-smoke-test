<?php declare(strict_types = 1);
namespace Foo;
use InvalidArgumentException;

/**
 * @psalm-type Product = array{id:int, name:string, ololo: float}
 */

class Model {
	/**
	 * @param SomePurchase $data
	 */
	static public function doSomethingPhpstan(array $data): void {
	}

	/** @param PurchaseList $purchaseList */
	static public function processPurchaseList(array $purchaseList): void {
		$name = $purchaseList['']; // should suggest: customerName, items, lastUpdateDt
	}
}

Model::doSomethingPhpstan([
	'id' => 123,
	'amount' => 100.00,
	'currency' => 'USD',
	'prodict' => 'typo',
]);

Model::processPurchaseList([
	'customerName' => 'Vasya',
	'lastUpdateDt' => new \DateTime(),
	'items' => [
		[
			'id' => 123,
			'amount' => 100.00,
			'currency' => 'USD',
			'product' => 'typo',
		],
	],
]);