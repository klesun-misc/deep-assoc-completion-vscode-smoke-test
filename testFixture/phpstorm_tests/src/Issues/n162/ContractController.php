<?php

class Contract
{
    public $startDt;
    public $endDt;
    public $commission;
}

/**
 * @template T
 */
interface FormInterface extends \ArrayAccess, \Traversable, \Countable
{
    /**
     * @return T|null
     */
    public function getData();

    /**
     * @psalm-return T|null
     */
    public function getPsalmReturnData();

    public function doHuj();
}

/** @param FormInterface<Contract> $ifc */
function doZhopa($ifc) {
    $ifc->getData()->;
    $ifc->getPsalmReturnData()->;
}
