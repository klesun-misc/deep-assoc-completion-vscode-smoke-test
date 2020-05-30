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

/**
 * @psalm-import-type SweetDreamsSong from \Psalm\Internal\Codebase\SweetDreams
 */

/** @param SweetDreamsSong $song */
function playSong($song) {
    //    \/ should suggest: lyrics, length, artists
    $song[''];
}

/**
 * @psalm-type Blogger = array{name: string, subscribers: int, team: BloggerTeam}
 * @psalm-type BloggerTeam = array{
 *     name: string,
 *     basis: 'non-profit' | 'commercial',
 *     members: Blogger[],
 * }
 */

/**
 * @psalm-import-type  FileMapType from \Psalm\Internal\Codebase\Analyzer
 *
 * \/ should suggest: psalm-type, psalm-import-type
 * @
 */

/** keys subset unit tests */
class UnitTest
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

        return $list;
    }

    public static function test_tupleAccess()
    {
        $segments = [
            ['from' => 'MOW', 'to' => 'LON'],
            ['from' => 'LON', 'to' => 'PAR'],
            ['from' => 'PAR', 'to' => 'MOW'],
        ];
        //           \/ should suggest: from, to
        $segments[0][''];
    }

    private static function test_strEq()
    {
        $i = rand(0, 3);
        $types = ['AIR', 'CAR', 'HOTEL', 'RAIL'];
        $type = $types[$i];
        //           \/ should suggest: AIR, CAR, HOTEL, RAIL
        if ($type == '') {

        }
        //                              \/ should suggest: AIR, CAR, HOTEL, RAIL
        if ((   $types[$i] ?? null) === '') {

        }
    }

    /** @param array{goreAmount: float, name: string, author: string} $mlpFanfic */
    private static function test_methPsalmParam($mlpFanfic)
    {
        //         \/ should suggest: goreAmount, name, author
        $mlpFanfic[''];
    }

    /**
     * @return array{error: string} | array{
     *     id: number,
     *     name: string,
     *     kind: 'cat' | 'dog' | 'fox' | 'hedgehog',
     * }
     */
    private static function _retrieveBlockchainCutie()
    {
        if (rand() < 0.2) {
            return ['error' => 'you are not lucky'];
        } else {
            $url = 'https://blockchaincuties.com/pet/t132998';
            return json_decode(file_get_contents($url), true);
        }
    }

    private static function test_psalmReturnType()
    {
        $cutie = self::_retrieveBlockchainCutie();
        //     \/ should suggest: error, id, name, kind
        $cutie[''];
    }

    /**
     * @param Blogger $blogger
     * @param Blogger[] $bloggers
     * @param array<string, Blogger> $nameToBlogger
     * @param array{host: Blogger, guest: Blogger} $featRecord
     * @param array{error: string}|Blogger $bestBlogger
     * @param BloggerTeam $team
     */
    private static function test_psalmTypeAlias($blogger, $bloggers, $nameToBlogger, $featRecord, $bestBlogger, $team)
    {
        //       \/ should suggest: name, subscribers, team
        $blogger[''];
        //                      \/ should suggest: name, subscribers, team
        $nameToBlogger['vasya'][''];
        //                  \/ should suggest: name, subscribers, team
        $featRecord['host'][''];
        //           \/ should suggest: name, subscribers, team
        $bloggers[0][''];
        //           \/ should suggest: error, name, subscribers, team
        $bestBlogger[''];
        //    \/ should suggest: name, basis, members
        $team[''];
        //                  \/ should suggest: name, subscribers, team
        $team['members'][0][''];
    }

    /** @param FileMapType $fileMap */
    private static function test_psalmImportType($fileMap)
    {
        //       \/ should suggest: 0, 1, 2
        $fileMap[''];
        //                  \/ should suggest: 0, 1, 2
        $fileMap[2][rand()][''];

        //                  \/ should suggest: 0, 1
        $fileMap[0][rand()][''];
        //                  \/ should suggest: 0, 1
        $fileMap[1][rand()][''];
    }

    // following not implemented yet
}
