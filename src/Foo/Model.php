<?php declare(strict_types = 1);
namespace Foo;
use InvalidArgumentException;

/**
 * @psalm-type Product = array{id:int, name:string, ololo: float}
 * 
 * a workaround for psalm to not report types declared in phpstan-specific way as errors
 * @psalm-type PhpstanSomePurchase = mixed
 * @psalm-type PhpstanPurchaseList = mixed
 */

class Model {
	/**
	 * @param PhpstanSomePurchase $data
	 */
	static public function doSomethingPhpstan(array $data): void {
	}

	/** @param PhpstanPurchaseList $purchaseList */
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
