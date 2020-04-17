<?php

/**
 * @param array{
 *   airline: 'AA',
 *   flightNumber: '123',
 *   bookingClass: 'C',
 * } $segment
 */
function makeGdsLine($segment) {
    $segment['air']; // should suggest 'airline'
}