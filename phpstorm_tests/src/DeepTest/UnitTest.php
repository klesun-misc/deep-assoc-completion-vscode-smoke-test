<?php
namespace DeepTest;

use Lib\ParamValidation\DictP;
use RbsVer\Parsers\Apollo\PricingParser\DataStructureWriters\PricingStructureWriter;
use RbsVer\Parsers\Sabre\Pricing\PqParserFull;
use RbsVer\Parsers\Sabre\Pricing\PqParserUnshiftOverflow;
use TestSamples\CmsSessionMemoryOverflow\CmsStatefulSession;
use TouhouNs\ReimuHakurei;
use TouhouNs\YakumoRan;


/**
 * TODO: make somehow possible to say that array
 * must have _only_ the keys from the expected output
 */
class UnitTest implements IProcessPntQueueAction /** extends \PHPUnit_Framework_Suite */
{
    public static function provideSimpleTest()
    {
        $list = [];

        // from function
        $list[] = [
            TestData::makeStudentRecord(),
            ['id' => [], 'firstName' => [], 'lastName' => [], 'year' => [], 'faculty' => [], 'chosenSubSubjects' => []],
        ];

        // from var
        $denya = TestData::makeStudentRecord();
        $list[] = [
            $denya,
            ['id' => [], 'firstName' => [], 'lastName' => [], 'year' => [], 'faculty' => [], 'chosenSubSubjects' => []],
        ];

        // from inner key
        $list[] = [
            $denya['friends'][0],
            ['name' => [], 'occupation' => []],
        ];

        return $list;
    }

    public static function provideTestScopes()
    {
        $list = [];

        $denya = TestData::makeStudentRecord();
        if (rand() > 0.5) {
            $denya = ['randomDenya' => -100];
            // should suggest _only_ randomDenya
            $list[] = [$denya, ['randomDenya' => []]];
        } elseif (rand() > 0.5) {
            $denya = ['randomDenya2' => -100];
            // should suggest _only_ randomDenya2
            $list[] = [$denya, ['randomDenya2' => []]];
        }
        // should suggest all keys from makeRecord(),
        // 'randomDenya' and 'randomDenya2'
        // (preferably highlighted in different collors)
        $list[] = [$denya, TestData::makeStudentRecord()];
        $list[] = [$denya, ['randomDenya' => [], 'randomDenya2' => []]];

        $denya = ['thisKeyWillNotBeSuggested' => 1414];


        return $list;
    }

    public static function provideTestElseIfAssignment()
    {
        $list = [];

        if ($res = ['a' => 1]) {
            // should suggest only a
            $list[] = [$res, ['a' => []]];
        } elseif ($res = ['b' => 2]) {
            $res['roro'] = 'asdasd';
            // should suggest only b and asdasd
            $list[] = [$res, ['b' => [], 'roro' => []]];
        } elseif ($res = ['c' => 3]) {
            // should suggest only c
            $list[] = [$res, ['c' => []]];
        } else if ($res = ['d' => 4]) {
            // should suggest only d
            $list[] = [$res, ['d' => []]];
        }
        // should suggest a,b,c.d
        $list[] = [$res, ['a' => [], 'b' => [], 'roro' => [], 'c' => [], 'd' => []]];

        return $list;
    }

    public static function provideTestKeyAssignment()
    {
        $list = [];

        $record = ['initialKey' => 123];
        if (rand() > 0.5) {
            $record = ['initialKey2' => 123];
            if (rand(0.5) > 0.5) {
                $record['dynamicKey1'] = 234;
            }
        } else {
            if (rand(0.5) > 0.5) {
                $record['dynamicKey2'] = 345;
                // must not contain dynamicKey1 and initialKey2
                $list[] = [$record, ['initialKey' => [], 'dynamicKey2' => []]];
            }
        }

        // should suggest initialKey, dynamicKey1, dynamicKey2
        $list[] = [$record, [
            'initialKey' => [], 'initialKey2' => [],
            'dynamicKey1' => [], 'dynamicKey2' => []]
        ];

        return $list;
    }

    public static function provideTestKeyKeyAccess()
    {
        $list = [];

        $record = TestData::makeStudentRecord();
        // should suggest birthDate, birthCountry,
        // passCode, expirationDate, family
        $list[] = [$record['pass'], [
            'birthDate' => [], 'birthCountry' => [],
            'passCode' => [], 'expirationDate' => [], 'family' => [],
        ]];
        $family = $record['pass']['family'];
        // should suggest spouse, children
        $list[] = [$family, ['spouse' => [], 'children' => []]];

        return $list;
    }

    public static function provideTestBasisListAccess()
    {
        $list = [];

        // should suggest name, priority
        $list[] = [
            TestData::makeStudentRecord()['chosenSubSubjects'][4],
            ['name' => [], 'priority' => []],
        ];

        $makeTax = function($i) {
            return [
                'currency' => -'USD',
                'amount' => 199 + $i,
            ];
        };
        $mapped = \array_map($makeTax, [1,2,3]);
        // should suggest currency, amount
        $mapped[0][''];
        $list[] = [$mapped[0], ['currency' => [], 'amount' => []]];

        return $list;
    }

    public static function provideTestArrayAppendInference()
    {
        $list = [];

        $records = [];

        for ($i = 0; $i < 10; ++$i) {
            $records[] = [
                'id' => $i,
                'randomValue' => rand(),
                'origin' => 'here',
            ];
        }

        // should suggest id, randomValue, origin
        $list[] = [$records[0], ['id' => [], 'randomValue' => [], 'origin' => []]];

        $mugiwaras = [];
        $mugiwaras['sanji']['cooking'] = 'good';
        $list[] = [$mugiwaras['sanji'], ['cooking' => []]];

        $lala = [];
        $lala[0]['asdas'][] = [
            'id' => -100,
            'randomValue' => rand(),
            'origin' => 'there',
            'originData' => [1,2,3],
        ];
        $lala['0']['asdas'][0][''];
        $lolo = $lala;
        // should suggest asdas
        $list[] = [$lolo[0], ['asdas' => []]];
        // should suggest id, randomValue, origin, originData
        $list[] = [$lolo[0]['asdas'][4], [
            'id' => [], 'randomValue' => [],
            'origin' => [], 'originData' => [],
        ]];

        return $list;
    }

    public static function provideTestNullKeyAccess()
    {
        $list = [];

        $record = [
            'a' => 5,
            'b' => null,
            'c' => null,
            'd' => 7,
        ];
        // should suggest a,b,c,d
        $list[] = [$record, ['a' => [], 'b' => [], 'c' => [], 'd' => []]];

        return $list;
    }

    public static function provideTestTernaryOperator()
    {
        $list = [];

        $record = [
            'a' => 5,
            'b' => rand() > 0.5 ? [
                'trueKeyB' => 5,
            ] : [
                'falseKeyB' => 5,
            ],
        ];
        $record['c'] = rand() > 0.5 ? [
            'trueKeyC' => 5,
        ] : [
            'falseKeyC' => 5,
        ];

        // should suggest trueKeyB, falseKeyB
        $list[] = [$record['b'], ['trueKeyB' => [], 'falseKeyB' => []]];
        // should suggest trueKeyC, falsephpstormKeyC
        $list[] = [$record['c'], ['trueKeyC' => [], 'falseKeyC' => []]];

        return $list;
    }

    public static function provideTestNullCoalesce()
    {
        $list = [];

        $maybeRecord = null
            ?? TestData::makeStudentRecord()
            ?? TestData::makeStudentRecord()
            ?? ['error' => 'maybe no']
        ;

        // should suggest all from makeRecord() and error
        $list[] = [$maybeRecord, ['error' => []]];
        $list[] = [$maybeRecord, TestData::makeStudentRecord()];

        return $list;
    }

    /**
     * @param $a = ['key1' => 5, 'key2' => 6]
     * @param $b = [
     *     'nestedAssoc' => [
     *         'nestedKey1' => 213,
     *         'nestedKey2' => 213,
     *         'nestedKey3' => 213,
     *     ],
     *     'numbers' => [1,5,3,6],
     * ]
     * @param $c = TestData::makeStudentRecord()
     */
    public static function provideDocHint($a, $b, $c)
    {
        $list = [];

        // should suggest: 'key1', 'key2'
        $list[] = [$a, ['key1' => [], 'key2' => []]];
        // should suggest: 'nestedAssoc', 'numbers'
        $list[] = [$b, ['nestedAssoc' => [], 'numbers' => []]];
        // should suggest: 'nestedKey1', 'nestedKey2', 'nestedKey3'
        $list[] = [$b['nestedAssoc'], [
            'nestedKey1' => [], 'nestedKey2' => [], 'nestedKey3' => [],
        ]];
        // should suggest makeRecord keys
        $list[] = [$c['pass'], TestData::makeStudentRecord()['pass']];

        return $list;
    }

    /**
     * @param array $firstBomb = \DeepTest\KiraYoshikage::bombTransmutation()
     * @param array $secondBomb = KiraYoshikage::sheerHeartAttack()
     * @param array $secondBomb = ReimuHakurei::fantasySeal()
     * @param $thirdBomb = [
     *     'name' => 'Bites The Dust',
     *     'castRange' => 2.5,
     *     'power' => 999.99,
     *     'requirements' => [
     *         'desperation' => 0.99999,
     *         'magicArrows' => 1,
     *         'evilness' => 1.00,
     *     ],
     * ]
     */
    public static function provideForeignFileInDoc($firstBomb, $secondBomb, $thirdBomb)
    {
        $list = [];

        $list[] = [$secondBomb, ['veryTough' => [], 'smallCar' => ['that' => [], 'follows' => []]]];

        // should suggest all the keys from the function
        $list[] = [$firstBomb['touch'], ['into' => [], 'a' => [], 'bomb' => []]];

        $thirdBomb['requirements']['desperation'];

        return $list;
    }

    public static function provideForeachAccess()
    {
        $list = [];

        $record = TestData::makeStudentRecord();
        foreach ($record['chosenSubSubjects'] as $subject) {
            // should suggest name, priority
            $subject['priority'];
            $list[] = [$subject, ['name' => [], 'priority' => []]];
        }

        return $list;
    }

    public static function provideTupleDirectAccess()
    {
        $list = [];

        $simpleTuple = [
            ['a' => 5, 'b' => 6],
            ['a' => 5, 'b' => 6],
            'huj' => 'asd',
        ];
        // should suggest: "0", "1", "huj"
        $list[] = [$simpleTuple, ['0' => [], '1' => [], 'huj']];
        // should suggest: "a", "b"
        $list[] = [$simpleTuple['0'], ['a' => [], 'b' => []]];
        // should suggest: "a", "b"
        $list[] = [$simpleTuple[1], ['a' => [], 'b' => []]];

        return $list;
    }

    public static function provideTuples()
    {
        $list = [];

        $musician = ['genre' => 'jass', 'instrument' => 'trumpet'];
        $programmer = ['language' => 'C#', 'orientation' => 'backend'];
        $teacher = ['subject' => 'history', 'students' => 98];

        $tuple = [$musician, $programmer, $teacher];
        // should suggest genre, instrument
        $list[] = [$tuple['0'], ['genre' => [], 'instrument' => []]];
        // should suggest language, orientation
        $list[] = [$tuple['1'], ['language' => [], 'orientation' => []]];
        // should suggest subject, students
        $list[] = [$tuple['2'], ['subject' => [], 'students' => []]];

        list($mus, $prog, $tea) = $tuple;
        // should suggest what should be suggested
        $list[] = [$mus, ['genre' => [], 'instrument' => []]];
        $list[] = [$prog, ['language' => [], 'orientation' => []]];
        $list[] = [$tea, ['subject' => [], 'students' => []]];

        return $list;
    }

    public static function providePregMatch(string $line)
    {
        $list = [];

        $regex =
            '/^\s*'.
            '(?P<segmentNumber>\d+)\s+'.
            '(?P<airline>[A-Z0-9]{2})\s*'.
            '(?P<flightNumber>\d{1,4})\s*'.
            '(?P<bookingClass>[A-Z])'.
            '/';

        // TODO: following causes dead loop for some reson - fix!
        if (preg_match($regex, $line, $matches)) {
            // should suggest: "segmentNumber", "airline", "flightNumber", "bookingClass"
            $matches[''];
            $list[] = [$matches, [
                'segmentNumber' => [], 'airline' => [],
                'flightNumber' => [], 'bookingClass' => [],
            ]];
        }

        return $list;
    }

    public static function provideBuiltIns()
    {
        $list = [];

        $records = array_map(function($i){return [
            'type' => 'generated',
            'score' => rand(0,100),
            'student' => 'Vasya',
            'parsed' => [
                'id' => $i,
                'generationTime' => rand(0,10),
            ],
        ];}, range(0,10));
        $records[] = [
            'type' => 'mostAverage',
            'score' => 54,
            'student' => 'Vova',
            'parsed' => [
                'comment' => 'two units higher than a dog',
                'averageness' => 'averagelyAverage',
            ],
        ];
        $records[] = [
            'type' => 'mostBlonde',
            'score' => 52,
            'student' => 'Nastya',
            'parsed' => [
                'comment' => 'blonde soul can\'t be dyed',
                'blondeness' => 'veryBlonde',
            ],
        ];

        // all following should suggest: "id", "score", "student"
        $list[] = [$records[0], [
            'type' => [], 'score' => [], 'student' => [], 'parsed' => [],
        ]];
        $list[] = [
            array_pop($records),[
            'type' => [], 'score' => [], 'student' => [], 'parsed' => [],
        ]];
        $list[] = [
            array_shift($records),[
            'type' => [], 'score' => [], 'student' => [], 'parsed' => [],
        ]];
        $list[] = [
            array_reverse($records)[0],[
            'type' => [], 'score' => [], 'student' => [], 'parsed' => [],
        ]];

        $byType = array_combine(
            array_column($records, 'type'),
            array_column($records, 'parsed')
        );
        $byType[''];
        // should suggest: "id", "generationTime"
        $list[] = [
            $byType['generated'],
            ['id' => [], 'generationTime' => []],
        ];
        // should suggest: "averageness"
        $byType['mostAverage'][''];
        $list[] = [
            $byType['mostAverage'],
            ['averageness' => []],
        ];
        // should suggest: "blondeness"
        $list[] = [
            $byType['mostBlonde'],
            ['blondeness' => []],
        ];

        return $list;
    }

    private static function makeKonohaCitizen(): IKonohaCitizen
    {
        if (rand() % 2) {
            $konohanian = Naruto::kageBunshin();
        } else {
            $konohanian = new Konohamaru();
        }
        return $konohanian;
    }

    public function provideInstanceMethod()
    {

        $bunshin = Naruto::kageBunshin();
        $money = $bunshin->payForDinner(100);
        // should suggest: "currency", "amount"
        $list[] = [$money, ['currency' => [], 'amount' => []]];

        $konohanian = self::makeKonohaCitizen();
        $taxBundle = $konohanian->payTaxes();

        // should suggest from all implementations
        $taxBundle['incomeTax'];
        $list[] = [$taxBundle, ['currency' => [], 'incomeTax' => [], 'gamblingTax' => [], 'familyTax' => []]];

        return $list;
    }

    private static function makeKonohanianIface(): IKonohaCitizen
    {
        return self::makeKonohaCitizen();
    }

    /** @param $paidTaxes = IKonohaCitizen::payTaxes() */
    public function provideInterfaceMethod(IKonohaCitizen $randomGuy, $paidTaxes)
    {
        $list = [];

        $list[] = [$randomGuy->payTaxes(), ['currency' => [], 'incomeTax' => [], 'gamblingTax' => [], 'familyTax' => []]];

        $konohanian = self::makeKonohanianIface();
        $taxBundle = $konohanian->payTaxes();
        // should suggest either from doc in interface or from implementations
        $list[] = [$taxBundle, ['currency' => [], 'incomeTax' => [], 'gamblingTax' => [], 'familyTax' => []]];

        $list[] = [$paidTaxes, ['currency' => [], 'incomeTax' => [], 'gamblingTax' => [], 'familyTax' => []]];

        return $list;
    }

    public function provideArrayChunk()
    {
        $list = [];

        $vova = ['occupation' => 'salesman', 'salary' => '300'];
        $nastya = ['occupation' => 'hooker', 'salary' => '900'];
        $igorj = ['occupation' => 'pudge', 'salary' => '200'];
        $katja = ['occupation' => 'singer', 'salary' => '900'];

        $workers = [$vova, $nastya, $katja, $igorj];
        $pairs = array_chunk($workers, 2);
        foreach ($pairs as $pair) {
            $list[] = [$pair[0], ['occupation' => [], 'salary' => []]];
        }

        return $list;
    }

    private static function makeBarrel(int $i)
    {
        return [
            'material' => [
                0 => 'oak',
                1 => 'christmas tree',
                2 => 'bamboo',
            ][rand(0,3)],
            'radius' => rand(0,10),
            'daughter' => 'Amane Suzuha',
        ];
    }

    public function provideMethByRef()
    {
        $list = [];

        // array_map with inline closure
        $ingredients = array_map(function($name){return [
            'name' => $name,
            'amount' => strlen($name),
        ];}, ['tomato', 'cucumber', 'pepper']);
        $list[] = [$ingredients[2], ['name' => [], 'amount' => []]];

        // with closure in a variable
        $makeSnowman = function(){return [
            'headSize' => rand(0,10),
            'torsoSize' => rand(10,20),
            'legsSize' => rand(20,30),
        ];};
        $snowmen = array_map($makeSnowman, range(1,10));
        $list[] = [$snowmen[4], ['headSize' => [], 'torsoSize' => [], 'legsSize' => []]];

        $barrels = array_map([self::class, 'makeBarrel'], [0,1,2,3,4]);
        $list[] = [$barrels[2], ['material' => [], 'radius' => [], 'daughter' => []]];

        $bombs = array_map([ReimuHakurei::class, 'evilSealingCircle'], [0,1,2,3,4]);
        $list[] = [$bombs[2], ['missileDensity' => [], 'missileDamage' => [], 'arcDegree' => []]];

        $kira = new \DeepTest\KiraYoshikage();
        ([$kira, 'murder'])()[''];

        $murderedNumbers = array_map([$kira, 'murder'], [1,2,3]);
        $murderedNumbers[0][''];
        $list[] = [$murderedNumbers[0], ['mood' => [], 'murderMethods' => []]];

        $barrels = array_map(['self', 'makeBarrel'], [0,1,2,3,4]);
        $list[] = [$barrels[2], ['material' => [], 'radius' => [], 'daughter' => []]];

        $bitesZaDustos = array_map(['\DeepTest\KiraYoshikage', 'bitesZaDusto'], [0,1,2,3,4]);
        $list[] = [$bitesZaDustos[0], ['time' => [], 'goes' => [], 'back' => []]];

        return $list;
    }

    /** @return array like [
     *     ['index' => 1, 'value' => 1, 'time' => 0.002],
     *     ['index' => 2, 'value' => 1, 'time' => 0.004],
     *     ['index' => 3, 'value' => 2, 'time' => 0.008],
     *     ...
     * ] */
    private static function fibonacci(int $n)
    {
        if ($n <= 0) {
            $result = [];
        } elseif ($n === 1) {
            $result = [];
            $result[] = ['index' => $n, 'value' => 1, 'time' => 0.00];
        } else {
            $startTime = microtime();
            $result = self::fibonacci($n - 1);
            $value = $result[$n - 3]['value'] + $result[$n - 2]['value'];
            $result[] = ['index' => $n, 'value' => $value, 'time' => microtime() - $startTime];
        }
        return $result;
    }

    public function provideRecursiveFunc()
    {
        $list = [];

        // it would be perfect if plugin detected
        // that it is recursive function at once instead
        // of interrupting after reaching a certain depth
        $fiboRecs = self::fibonacci(10);
        $list[] = [$fiboRecs[0], ['index' => [], 'value' => [], 'time' => []]];

        return $list;
    }

    private function addFullDt($passedSeg)
    {
        $passedSeg['fullDt'] = date('Y-m-d H:i:s');
        return $passedSeg;
    }

    public function provideBasicGenericTyping()
    {
        $list = [];

        $seg = ['from' => 'KIV', 'to' => 'RIX'];
        $fullSeg = self::addFullDt($seg);
        $fullSeg[''];
        $list[] = [$fullSeg, ['from' => [], 'to' => [], 'fullDt' => []]];

        // apparently something wrong happens when name is same - should correct
        // var scope to not include assigned var to passed var resolutions
        $sfoSeg = ['from' => 'LON', 'to' => 'SFO', 'netPrice' => '240.00'];
        $sfoSeg = self::addFullDt($sfoSeg);
        $sfoSeg[''];
        $list[] = [$sfoSeg, ['from' => [], 'to' => [], 'netPrice' => [], 'fullDt' => []]];


        $denis = ['job' => false, 'girlfriend' => false];
        $denis = ['whiskey' => true, 'dota' => true, 'oldDenis' => $denis];
        $list[] = [$denis['oldDenis'], ['job' => [], 'girlfriend' => []]];

        return $list;
    }

    private static function addTripStr($segment)
    {
        $segment['trip'] = $segment['from'].'-'.$segment['to'];
        return $segment;
    }

    public function providePassedArgInAKey()
    {
        $list = [];

        $segment = ['from' => 'LOS', 'to' => 'MNL'];
        $segment = self::addTripStr($segment);

        $list[] = [$segment, ['from' => [], 'to' => [], 'trip' => []]];

        return $list;
    }

    private static function callFunc(callable $func)
    {
        return $func();
    }

    public function providePassedFunc()
    {
        $list = [];
        $mkJobData = function(){return [
            'pcc' => '1O3K',
            'queue' => '100',
        ];};
        $called = self::callFunc($mkJobData);
        $called[''];
        $list[] = [$called, ['pcc' => [], 'queue' => []]];
        return $list;
    }

	public function provideArrayColumnWithNums()
	{
		$list = [];
		$fcSplit = [
            ['0' => 5, '1' => ['lo' => 5]],
            ['0' => 5, '1' => ['lo' => 5]],
            ['0' => 5, '1' => ['lo' => 5]],
        ];
        $values = array_column($fcSplit, 1);
		$list[] = [$values[0], ['lo' => []]];
		return $list;
	}

    public function provideVeryDeepKey()
    {
        $list = [];

        // not implemented yet

        // ideally, limit should be some ridiculously big number
        // so you would never reach it in normal circumstances,
        // but that requires handling circular references properly

        $addict = [
            'face' => [
                'eyes' => [
                    'left' => [
                        'pupil' => [
                            'color' => 'red',
                            'size' => [
                                'value' => 'veryBig',
                                'reason' => 'marijuana',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // should suggest all these keys by the wya
        $pupilSize = $addict['face']['eyes']['left']['pupil']['size'];
        // should suggest: "value", "reason"
        $list[] = [$pupilSize, ['value' => [], 'reason' => []]];

        $policeDepartment = [
            'evidenceOfTheYear' => $pupilSize,
            'offices' => [
                '402' => [
                    'evidenceOfTheChef' => $pupilSize,
                    'deskByTheWindow' => [
                        'dayShift' => [
                            'favouriteEvidence' => $pupilSize,
                            'cases' => [
                                '8469132' => [
                                    'mainEvidence' => $pupilSize,
                                    'evidences' => [$pupilSize],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // should suggest: "value", "reason"
        $list[] = [
            $policeDepartment['evidenceOfTheYear'],
            ['value' => [], 'reason' => []]
        ];
        $list[] = [
            $policeDepartment['offices']['402']['evidenceOfTheChef'],
            ['value' => [], 'reason' => []]
        ];
        // following will fail till i fix circular references
        // TODO: uncomment!
        $list[] = [
            $policeDepartment['offices']['402']['deskByTheWindow']['dayShift']['favouriteEvidence'],
            ['value' => [], 'reason' => []]
        ];
        $list[] = [
            $policeDepartment['offices']['402']['deskByTheWindow']['dayShift']['cases']['8469132']['mainEvidence'],
            ['value' => [], 'reason' => []]
        ];
        $policeDepartment['offices']['402']['deskByTheWindow']['dayShift']['cases']['8469132']['evidences'][0][''];
        $list[] = [
            $policeDepartment['offices']['402']['deskByTheWindow']['dayShift']['cases']['8469132']['evidences'][0],
            ['value' => [], 'reason' => []]
        ];

        return $list;
    }

    public function provideUnresolvedVarKey()
    {
        $list = [];
        $ptcBlock = [
            'ptcInfo' => ['ptc' => 'ADT', 'quantity' => 2],
            'netPrice' => ['currency' => 'USD', 'amount' => '75.00'],
        ];
        $store = ['pricingBlockList' => [], 'pricingModifiers' => []];
        $store['pricingBlockList'][] = $ptcBlock;
        $storeInfo = ['pricingList' => []];
        $storeInfo['pricingList'][] = $store;

        foreach ($storeInfo['pricingList'] as $i => $store) {
            foreach ($store['pricingBlockList'] as $j => $ptcBlock) {
                $storeInfo['pricingList'][0][''];
                $storeInfo['pricingList'][$i][''];
                $list[] = [$storeInfo['pricingList'][$i], ['pricingBlockList' => [], 'pricingModifiers' => []]];
            }
        }
        return $list;
    }

    public function provideTupleAndArrayMix()
    {
        $list = [];
        $pricingList = [];
        if ($error = $parsed['error'] ?? null) {
            return ['error' => $error];
        } elseif (rand() % 2) {
            // GDS returned single PTC pricing instantly
            if (rand() % 2) {
                $ptcBlock = ['netPrice' => '150.00', 'singlePtcBonus' => '20.00'];
                $pricingList[] = [
                    'pricingModifiers' => [],
                    'pricingBlockList' => [$ptcBlock],
                    'singlePtcSpecificKey' => -100,
                ];
            } else {
                return ['error' => 'GDS returned output for single PTC even though there were multiple pricing stores in command'];
            }
        } elseif (rand() % 2) {
            // pricing summary with partial data - no FC, carrier, taxes...
            // need to call a separate command for each PTC
            $ptcGroups = range(1,5);
            foreach ($ptcGroups as $i => $ptcInfo) {
                $storeNum = $ptcInfo['storeNumber'] ?? 1;
                $ptcBlock = ['netPrice' => '150.00', 'multiPtcPenalty' => '-30.00'];
                if ($error = $ptcBlock['error'] ?? null) {
                    return ['error' => 'Failed to fetch '.$ptcInfo['ptc'].' PTC block - '.$error];
                } else {
                    $pricingList[$storeNum - 1]['pricingModifiers'] = $cmdStores[$storeNum - 1] ?? [];
                    $pricingList[$storeNum - 1]['pricingBlockList'][] = $ptcBlock;
                    $pricingList[$storeNum - 1]['multiPtcSpecificKey'] = -200;
                }
            }
        } else {
            return ['error' => 'Unexpected pricing type'];
        }
        foreach ($pricingList as $store) {
            // should suggest both "singlePtcBonus" and "multiPtcPenalty"
            // cuz we can't say for sure that the latter can not be "0"
            $store['pricingBlockList'][0][''];
            $list[] = [$store['pricingBlockList'][0], ['singlePtcBonus' => [], 'multiPtcPenalty' => []]];
            // should not suggest "singlePtcBonus" cuz we know
            // for sure that it can belong only to "0"-th store
            $store['pricingBlockList'][1][''];
            $list[] = [$store['pricingBlockList'][1], ['multiPtcPenalty' => []]];
        }
        $pricingList[0][''];
        // should treat it as `any key`, since $storeNum is not a constant
        $list[] = [$pricingList[0], ['singlePtcSpecificKey' => [], 'multiPtcSpecificKey' => []]];
        return $list;
    }

    private static function transformSegment($seg)
    {
        return [
            'from' => $seg['departure'],
            'to' => $seg['destination'],
            'when' => $seg['date'],
        ];
    }

    public function provideKeysReturnedByClosure(array $itinerary, $pokemon)
    {
        $list = [];
        $pokemon['name'] = 'pikachu';
        $pokemon[''];
        $list[] = [$pokemon, ['name' => []]];

        $brejsabtrjsa = [];
        $brejsabtrjsa['numa']['numa']['ej'] = -100;

        $common = array_map([self::class, 'transformSegment'], $itinerary);
        $common['0'][''];

        // apparently keys assigned to a var without type are not preserved...
        $transform = function($seg) {
            //$seg = self::transformSegment($seg);
            $seg['closured'] = true;
            return $seg;
        };
        $common = array_map($transform, $itinerary);
        $common['0'][''];
        $list[] = [$common['0'], ['closured' => []]];
        return $list;
    }

    private static function transformStore($parsed)
    {
        return [
            'pricingBlockList' => array_map(function($ptcBlock){
                return [
                    'fareInfo' => [
                        'baseFare' => $ptcBlock['baseFare'],
                        'taxAmount' => $ptcBlock['taxAmount'],
                        'netPrice' => $ptcBlock['netPrice'],
                        'fareConstructionRaw' => $ptcBlock['fareCalculation']['raw'],
                    ],
                ];
            }, $parsed['pricingBlockList']),
        ];
    }

    /**
     * @param $stores = [UnitTest::transformStore(), ...]
     * fetches all rule sections, no matter how long they are
     */
    private function provideUnknownTypeKeyShouldMeanAny(array $stores, array $ruleRecords)
    {
        $list = [];

        $numToStore = array_combine(array_column($stores, 'quoteNumber'), $stores);
        foreach ($ruleRecords as $ruleRecord) {
            $storeNum = $ruleRecord['pricingNumber'];
            $ptcNum = $ruleRecord['subPricingNumber'];

            $numToStore[$storeNum]['']; // should suggest: "pricingBlockList"
            $list[] = [$numToStore[0], ['pricingBlockList' => []]];
            $list[] = [$numToStore[$storeNum], ['pricingBlockList' => []]];
            $list[] = [$numToStore[$storeNum]['pricingBlockList'][$ptcNum - 1], ['fareInfo' => []]];
        }
        return $list;
    }

    private static function provideSimpleFieldKeyAssignment()
    {
        $list = [];

        $stor = new PersonStorage();
        $stor->mainPerson[''];
        $list[] = [$stor->mainPerson, ['name' => [], 'age' => []]];

        $first = $stor->allPersons[0];
        $first[''];
        $list[] = [$first, ['name' => [], 'age' => []]];

        return $list;
    }

    private static function provideFieldDefinedThroughItself()
    {
        $list = [];
        $ran = new YakumoRan();
        $ran->acquireJuniorDevelopers([
            ['name' => 'Vasya', 'power' => 'bridge-jumping'],
            ['name' => 'Vova', 'power' => 'sneezing'],
        ]);
        $ran->getFreeShikigami()[''];
        $list[] = [$ran->getFreeShikigami(), ['name' => [], 'power' => []]];
        return $list;
    }

    private static function provideFieldKeyAssignment()
    {
        $list = [];

        // this test would actually fail if completion was
        // as smart as executed code, since did not actually
        // call any writing functions in the writer
        // so if per chance one day plugin is as smart as
        // compiler - don't hesitate to correct this test

        $pricingStore = PricingStructureWriter::make()->getStructure();
        $list[] = [$pricingStore, ['pricingBlockList' => [], 'wholePricingMarkers' => []]];

        $ptcBlock = $pricingStore['pricingBlockList'][0];
        $ptcBlock[''];
        $list[] = [$ptcBlock, ['passengerNumbers' => [], 'defaultPlatingCarrier' => [], 'fareConstruction' => []]];

        return $list;
    }

    public static function provideObjectInAKey()
    {
        $list = [];
        $storRecord = [
            'capacity' => '256mb',
            'path' => '/home/klesun/person_storage.db',
            'stor' => new PersonStorage(),
        ];
        $storRecord['stor']->mainPerson;

        $storRecord['stor']->mainPerson[''];
        $list[] = [$storRecord['stor']->mainPerson, ['name' => [], 'age' => []]];
        return $list;
    }

    public function provideAssignObjectFromArray()
    {
        $list = [];

        $rpcResult = [
            'success' => true,
            'result' => new PersonStorage(),
        ];
        $stor = $rpcResult['result'];
        $rpcResult['result'] = [];
        $stor->mainPerson[''];
        $list[] = [$stor->mainPerson, ['name' => [], 'age' => []]];

        return $list;
    }

    public function phpunitProvideSessionTests()
    {
        $list = [];

        $sessionData = ['pcc' => '2GF6', 'gds' => 'apollo', 'record_locator' => 'D64GBF'];
        $expected = [
            'response_code' => 1,
            'result' => [
                'status' => 'executed',
                'pnrData' => [
                    'reservation' => [],
                    'currentPricing' => [],
                ],
            ],
        ];
        $calledCommands = [
            ['cmd' => '*R', 'output' => 'ASFASFAFV A F A FA'],
            ['cmd' => '*SVC', 'output' => 'ASFASFAFV A F A FA'],
            ['cmd' => '$B', 'output' => 'ASFASFAFV A F A FA'],
        ];

        $list[] = [$sessionData, $expected, $calledCommands];

        return $list;
    }

    /**
     * @test
     * @dataProvider phpunitProvideSessionTests
     */
    public function providePhpunitDataProviderTest($sessionData, $expected, $calledCommands)
    {
        $list = [];

        $sessionData['gds'];

        $list[] = [$sessionData, ['pcc' => [], 'gds' => [], 'record_locator' => []]];
        $list[] = [$expected, ['response_code' => [], 'result' => []]];
        $list[] = [$calledCommands[0], ['cmd' => [], 'output' => []]];

        return $list;
    }

    public function provideElseIfInALoop()
    {
        $list = [];
        $locations = [
            ['type' => 'flight', 'departureAirport' => 'KIV', 'airline' => 'BA'],
            ['type' => 'flight', 'departureAirport' => 'RIX', 'airline' => 'UA'],
            ['type' => 'ARNK', 'departureAirport' => 'LON'],
        ];
        $segments = [];
        $i = -1;
        foreach ($locations as $location) {
            if ($i > -1) {
                $segments[$i]['destinationAirport'] = $location['departureAirport'];
            }
            if ($location['type'] === 'flight') {
                $segments[$i]['destinationAirport'] = $location['departureAirport'];
                $segments[++$i] = $location;
            } elseif ($location['type'] === 'ARNK') {
                $list[] = [$segments[$i], ['departureAirport' => [], 'airline' => []]];
                $segments[$i]['destinationAirport'] = $location['departureAirport'];
            }
        }
        return $list;
    }

    private static function normArrStatFilt($arr)
    {
        return array_filter($arr);
    }

    private static function normArrStat($arr)
    {
        return $arr;
    }

    public function provideLambdaInTernaryOperator()
    {
        $list = [];
        $forClient = rand() % 2 ? true : false;

        $instaNormed = $forClient
            ? ['dendi' => 1, 'dread' => 2]
            : ['maddison' => 1, 'hovanskiy' => 2, 'kamikadzed' => 3];

        $instaNormed[''];
        $list[] = [$instaNormed, ['dendi' => [], 'dread' => [], 'maddison' => [], 'hovanskiy' => [], 'kamikadzed' => []]];

        $noTernar = self::normArrStat(['k' => [], 'v' => []]);
        $noTernar[''];
        $list[] = [$noTernar, ['k' => [], 'v' => []]];

        $statInVar = function(array $arr){return $arr;};
        $fromStatInVar = $statInVar(['z' => [], 'r' => []]);
        $fromStatInVar[''];
        $list[] = [$fromStatInVar, ['z' => [], 'r' => []]];

        $normArr = !$forClient
            ? function(array $arr){return -100;}
            : function(array $arr){return array_filter($arr);};
        $normed = $normArr(['a' => 1, 'b' => 2]);
        $normed[''];
        $list[] = [$normed, ['a' => [], 'b' => []]];

        $normArrStat = !$forClient
            ? [self::class, 'normArrStatFilt']
            : [self::class, 'normArrStat'];
        $normedStat = $normArrStat(['a' => 1, 'b' => 2]);
        $normedStat[''];
        $list[] = [$normedStat, ['a' => [], 'b' => []]];

        return $list;
    }

    // got a bug: when resolving a field it used args
    // passed to func where field was referenced, but
    // passed them to the function field was declared in!
    // it caused idea to hang for a long time
    public function provideArgsFromWrongMethodBug(CmsStatefulSession $session)
    {
        $list = [];
        $cmdRec = $session->logAndRunCommandByGds('asdwqe-12312', '*R');
        $list[] = [$cmdRec, ['cmd' => [], 'output' => [], 'dt' => []]];
        return $list;
    }

    public function provideHangingDeepRecursion(CmsStatefulSession $session)
    {
        $list = [];
        $sessionData = $session->getSessionData();
        $list[] = [$sessionData, ['record_locator' => [], 'is_pnr_stored' => [], 'internal_token' => []]];
        return $list;
    }

    public function provideArrayUnshiftInference()
    {
        $list = [];
        $segments = [];
        array_unshift($segments, ['from' => 'RIX', 'to' => 'LON']);
        array_unshift($segments, ['from' => 'KIV', 'to' => 'RIX']);
        $segments[0]['from'];
        $list[] = [$segments[0], ['from' => [], 'to' => []]];
        return $list;
    }

    public function provideImplementedMethArg($optionalData)
    {
        $list = [];
        // arg type should be taken from interface doc comment
        $optionalData['queueNumber'];
        $list[] = [$optionalData, ['pcc' => [], 'queueNumber' => [], 'sessionData' => []]];
        $list[] = [$optionalData['sessionData'], ['id' => [], 'gds' => [], 'token' => []]];
        return $list;
    }

    public function providePqParserNotArrayUnshiftMemoryOverflow(string $pqDump)
    {
        $list = [];
        $parsed = PqParserFull::parse($pqDump);
        $parsed['pqList'][0]['priceInfo']['additionalInfo'][''];
        $list[] = [
            $parsed['pqList'][0]['priceInfo']['additionalInfo'],
            ['privateFareType' => [], 'tourCode' => [], 'commission' => [], 'unparsed' => []],
        ];
        return $list;
    }

    public function provideNoNsNoCompletion($srcEmpl)
    {
        $list = [];
        $empl1 = veryveryverylongprefixpromote($srcEmpl);
        $empl1[''];
        $list[] = [$empl1, [
            'salary' => [], 'position' => [], 'fullName' => [],
        ]];
        $empl2 = (new \ClassWithoutNameSpace())->promote($srcEmpl);
        $empl2[''];
        $list[] = [$empl2, [
            'salary' => [], 'position' => [], 'fullName' => [],
        ]];
        return $list;
    }

    public function providePqParserArrayUnshiftMemoryOverflow(string $pqDump)
    {
        $list = [];
        /** @var $parsed = YakumoRan::acquireChen() */
        $parsed = PqParserUnshiftOverflow::parse($pqDump);
        $parsed[0][''];
        // should not cause infinite recursion
        $list[] = [$parsed[0], ['unparsed' => []]];
        return $list;
    }

    /** @param $getAreaData = function($letter){
     *     return ['id' => 1234, 'cmd' => '$BB', 'output' => 'ASD ASD'];
     * } */
    public static function provideAnonFuncInADoc(callable $getAreaData)
    {
        $list = [];
        $areaData = $getAreaData();
        $areaData[''];
        $list[] = [$areaData, ['id' => [], 'cmd' => [], 'output' => []]];
        return $list;
    }

    private static function provideCircularRefsSub()
    {
        return [
            static::provideCircularRefs(),
            static::provideCircularRefs(),
            'asdsad',
        ];
    }

    // after recent feature of caching expression resolutions,
    // we may get two types that point to each other like here
    // this causes infinite recursion on attempt to describe whole structure
    public static function provideCircularRefs()
    {
        $list = [];
        $duct = [
//            'asd' => ['a' => 5, 'b' => 6],
//            'dsa' => 123,
        ];
        $duct[] = static::provideCircularRefsSub();
        // should not cause Stack Overflow exception
        $duct['asd'][''];
        // _Ctrl + Alt + Q_ should not cause Stack Overflow exception
        $duct;
        $list[] = [$duct, []];
        return $list;
    }

    private static function getLast(array $values)
    {
        return $values[count($values) - 1];
    }

    /**
     * @param PersonStorage[] $storages
     */
    public static function provideIdeaObjArrPhpDoc($storages)
    {
        $list = [];
        $storages[0]->mainPerson[''];
        $list[] = [$storages[0]->mainPerson, ['name' => [], 'age' => []]];
        static::getLast($storages)->mainPerson[''];
        $list[] = [static::getLast($storages)->mainPerson, ['name' => [], 'age' => []]];
        return $list;
    }

    public static function provideForeachKeyCompletion()
    {
        $requestedData = [
            'reservation' => ['mandatory', 'async'],
            'fareQuoteInfo' => ['mandatory'],
            'repeatedItinerary' => ['optional'],
        ];
        $result = [];
        foreach ($requestedData as $field => list($prefLevel, $mode)) {
            $result[$field] = [
                'errors' => [],
                'data' => [1,2,3],
            ];
        }
        $list[] = [$result, ['reservation' => [], 'fareQuoteInfo' => [], 'repeatedItinerary' => []]];

        $simpleFchArr = [];
        foreach (['a' => 1, 'b' => 3] as $k => $v) {
            $simpleFchArr[$k] = $v * 5;
        }
        $list[] = [$simpleFchArr, ['a' => [], 'b' => []]];
        return $list;
    }

    private static function getRecursedArrayCombine()
    {
        if (rand() % 2) {
            $values = static::getRecursedArrayCombine();
        } else {
            $values = [['cute' => 1], ['popular' => 1], ['strong' => 1]];
        }
        return array_combine(
            ['pichu', 'pikachu', 'raichu'],
            $values
        );
    }

    public function provideStackOverflowArrayCombine()
    {
        $list = [];
        $combined = static::getRecursedArrayCombine();
        $combined['pichu']['strong'];
        $list[] = [$combined['raichu'], ['strong' => []]];
        return $list;
    }

    public function provideIfcReturnDoc()
    {
        $honestOpinion = static::makeKonohaCitizen()->getHonestOpinion();
        $honestOpinion[''];
        $list[] = [$honestOpinion, [
            'whoShouldBeTheHokage' => [],
            'whoStealsFromTreasury' => [],
            'whoShouldNotExist' => [],
        ]];
        return $list;
    }

    public function provideGlobals()
    {
        // should infer keys from all places in project where $GLOBALS is written
        $GLOBALS[''];
        $list[] = [$GLOBALS, ['asd' => [], 'haruka' => [], 'zhopa' => []]];
        global $zhopa;
        $zhopa[''];
        $list[] = [$zhopa, ['ololo' => [], 'DmitryNagiev' => []]];
        return $list;
    }

    /** @param $citizenOpt = Result::makeOk(new ReimuHakurei()) */
    public function provideNoNsNew($citizenOpt)
    {
        $citizenOpt->result->demandDonuts();
        $citizen->demandDonuts()[''];
        $honestOpinion = static::makeKonohaCitizen()->getHonestOpinion();
        $honestOpinion[''];
        $list[] = [$honestOpinion, [
            'whoShouldBeTheHokage' => [],
            'whoStealsFromTreasury' => [],
            'whoShouldNotExist' => [],
        ]];
        return $list;
    }


    private function sendVerificaionEmail($_arguments = [
        'user' => null,
        'controllerIdentifier' => null,
        'actionIdentifier' => null,
    ])
    {
        $_arguments[''];
        return ['params' => $_arguments];
    }

    public function provideDefaultArgValues()
    {
        $sent = $this->sendVerificaionEmail([
            'user' => 'vova',
            // should suggest: 'controlIdentifier', 'actionIdentifier'
            '' => 'ololo123',
        ]);
        $sent['params'][''];
        $list[] = [$sent['params'], ['user' => [], 'controllerIdentifier' => [], 'actionIdentifier' => []]];
        return $list;
    }

    //=============================
    // following are not implemented yet
    //=============================

    public function provideStrTypeKeyFilter()
    {
        $list = [];
        if (rand() % 2) {
            $segment = [
                'type' => 'AIR',
                'from' => 'KIV',
                'to' => 'RIX',
            ];
        } else {
            $segment = [
                'type' => 'CAR',
                'model' => 'Opel',
                'state' => 'new',
            ];
        }
        if ($segment['type'] === 'AIR') {
            // should suggest _only_: type, from, to
            $segment[''];
            $list[] = [$segment, ['type' => [], 'from' => [], 'to' => []]];
        } elseif ($segment['type'] === 'CAR') {
            // should suggest _only_: type, model, state
            $segment[''];
            $list[] = [$segment, ['type' => [], 'model' => [], 'state' => []]];
        }
        return $list;
    }
}
