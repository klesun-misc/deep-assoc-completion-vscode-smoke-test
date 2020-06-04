<?php
/**
 * @psalm-import-type PhoneType from \TypeDefs\Phone
 * @psalm-import-type CustomerShape from \TypeDefs\Customer;
 */
class User {
    /**
     * @psalm-return PhoneType
     */
    function toArray(): array {
        return array_merge([], (new \TypeDefs\Phone)->toArray());
    }

    /** @param CustomerShape $customer */
    public function doStuff($customer) {

    }
}
