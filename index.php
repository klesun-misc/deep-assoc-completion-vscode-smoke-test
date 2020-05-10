<?php declare(strict_types = 1);

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

function parseXml($xml) {
    if (rand() < 0.2) {
        return parseXml(substr($xml, 0, 10));
    } else {
        return [
            'tagName' => 'div',
            'children' => [],
        ];
    }
}

function parseSomething() {
    return parseXml();
}

makeGdsLine(['']);
makeGdsLine([
    'asd' => 123,
    ''
]);

/**
 * @param array{
 *   airline: 'AA',
 *   flightNumber: '123',
 *   bookingClass: 'C',
 *   mealOptions: array<array{
 *     types: array<'DINNER' | 'BRUNCH'>,
 *     cabinClasses: array<'BUSINESS' | 'FIRST' | 'ECONOMY', boolean>,
 *     flags: array{
 *       isVegetarian: boolean,
 *       isKosher: boolean,
 *       hasLactose: boolean,
 *     },
 *   }>,
 *   aircraft: string,
 * } $segment
 */
function makeGdsLine($segment) {
    $segment['']; // should suggest: 'airline', 'flightNumber', 'bookingClass'

    foreach ( $segment['mealOptions'] as $option) {
        $option['flags']['isKosher'];
    }

    $segment['mealOptions'][0][''];
    $segment['mealOptions'][0]['flags'][];
    $segment['mealOptions'][0]['cabinClasses'][];

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
    doStuff(parseSomething($asd)[]);
    $huj = $equip['status'];

    $ololo = getOlolo();
    $ololo->doStuff();
    $ololo[''];

    parseSomething()[''];
}

makeGdsLine([
    'asd' => [1, 2, 3],
]);
