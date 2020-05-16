<?php
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