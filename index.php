<?php

class Ololo
{
    public function doStuff()
    {
        print('stuff');
    }
}

function getOlolo() {
    return new Ololo();
}

function makeSale() {
    if (rand() < 0.2) {
        return [
            'error' => 'you are not lucky',
        ];
    }
    return [
        'amount' => '25.50',
        'curreny' => 'USD',
        'product' => 'AIRLINE_TICKET',
        'status' => 'CANCELLED',
    ];
}

class Cutie {
    public static function getAttributeStats() {
        return [
            'shy' => ['fire' => 1, 'water' => -1],
            'blue_eyes' => ['energy' => 2, 'earth' => -2],
        ];
    }

    public function useItem($itemEnt) {
        return [
            'status' => 'failure',
            'message' => 'level too high',
        ];
    }
}

/**
 * @param array{
 *   airline: 'AA',
 *   flightNumber: '123',
 *   bookingClass: 'C',
 * } $segment
 */
function makeGdsLine($segment) {
    $segment['']; // should suggest: 'airline', 'flightNumber', 'bookingClass'

    /** @var SomeCoolASDType $passenger */
    $passenger = [
        'name' => 'Vasya',
        123,
        'ticketNumber' => '8102345627456',
        'dob' => '1980-02-23',
    ];

    $passenger = [];
    $passenger['']; // should suggest: 'name', 'ticketNumber', 'dob'

    $sale = makeSale();
    $sale['']; // should suggest: 'amount', 'currency', 'product', 'status'
    makeSale()[''];

    $stats = Cutie::getAttributeStats();
    $stats[''];

    $cutie = new Cutie();
    $equip = $cutie->useItem();
    $equip[''];

    $ololo = getOlolo();
    $ololo->doStuff();
    $ololo[''];
}