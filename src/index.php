<?php

/** @param array{ololo: string} $arg */
function doStuff($arg) {

}

doStuff([
	'ololo' => 'lalal',
]);

// does not work: type imports only work on classes in current psalm version
//
// /** @param CustomerShape $customer */
// function processCustomer($customer) {

// }

/**
 * @psalm-import-type CustomerShape from \TypeDefs\Customer;
 */
class Index
{
	/** @param CustomerShape $customer */
	function processCustomer($customer) {

	}
}