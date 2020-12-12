<?php

/**
 * @template T
 */
class LazyValue
{
    /**
     * @psalm-var Closure(): T
     */
    private Closure $callable;

    /**
     * @psalm-var T
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $value;
    private bool $resolved = false;

    /**
     * @param Closure(): T $callable
     */
    public function __construct(Closure $callable)
    {
        $this->callable = $callable;
    }
    /**
     * @psalm-return T
     */
    public function getValue() // runs closure once, caches the value and returns it
    {
        if (!$this->resolved) {
            $this->value = ($this->callable)();
            $resolved = true;
        }
        return $this->value;
    }
}

class ContractPayment
{
    function getAmount() {}
    function getId() {}
}

class PaymentManager
{
    /**
     * @var LazyValue<array<ContractPayment>>
     */
    private LazyValue $lazyPayments;

    public function getRealPayments(): array
    {
        $payments = $this->lazyPayments->getValue();

        $results = [];
        foreach ($payments as $payment) {
            $payment->g;
        }
    }
}
