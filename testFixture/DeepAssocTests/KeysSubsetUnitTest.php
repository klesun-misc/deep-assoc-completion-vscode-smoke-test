<?php
namespace DeepAssocTests;

use \DeepTest\TestData;

$pax = ['name' => 'Vasya', 'age' => 19];
//   \/ should suggest: name, age
$pax[''];

/**
 * @param array{
 *     element: 'magic' | 'honesty' | 'loyalty' | 'kindness' | 'generosity' | 'slaughter',
 *     name: string,
 *     color: string,
 * } $pony
 */
function test_shapeDoc($pony) {
    //    \/ should suggest: element, name, color
    $pony[''];
}

//                                      \/ should suggest: id, firstName, lastName, year, faculty, pass, chosenSubSubjects
\DeepTest\TestData::makeStudentRecord()[''];

class KeysSubsetUnitTest
{
    public static function provideSimpleTest()
    {
        $list = [];

        // from function
        //                            \/ should suggest: id, firstName, lastName, year, faculty, pass, chosenSubSubjects
        TestData::makeStudentRecord()[''];
        $list[] = [
            TestData::makeStudentRecord(),
            ['id' => [], 'firstName' => [], 'lastName' => [], 'year' => [], 'faculty' => [], 'chosenSubSubjects' => []],
        ];

        // from var
        $denya = TestData::makeStudentRecord();
        //     \/ should suggest: id, firstName, lastName, year, faculty, pass, chosenSubSubjects
        $denya[''];
        $list[] = [
            $denya,
            ['id' => [], 'firstName' => [], 'lastName' => [], 'year' => [], 'faculty' => [], 'chosenSubSubjects' => []],
        ];

        // TODO: implement
        // from inner key
        $denya['friends'][0][''];
        $list[] = [
            $denya['friends'][0],
            ['name' => [], 'occupation' => []],
        ];

        return $list;
    }
}