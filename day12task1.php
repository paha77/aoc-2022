<?php

$input = file_get_contents(__DIR__ . '/input.txt');

// parse
list($map, $start, $end, $visited, $distances) = array_reduce(explode("\n", $input), function($carry, $item) {
    $row = [];
    foreach (str_split($item) as $pos) {
        $x = count($row);
        $y = count($carry[0]);
        if ($pos === 'S') {
            $carry[1][0] = $y;
            $carry[1][1] = $x;
            $row[] = PHP_INT_MAX;
        } elseif ($pos === 'E') {
            $carry[2][0] = $y;
            $carry[2][1] = $x;
            $row[] = 122; // z
        } else {
            $row[] = ord($pos);
        }
        $carry[3][$y][$x] = null;
        $carry[4][$y][$x] = PHP_INT_MAX;
    }
    $carry[0][] = $row;
    return $carry;
}, [[], [0, 0], [0, 0], [], []]);

// start from end and go backwards
$distances[$end[0]][$end[1]] = 0;
$queue = [$end];

while ($ql = count($queue)) {
    $position = array_shift($queue);
    $visited[$position[0]][$position[1]] = true;

    // all possible positions around the actual point
    $surroundingPositions = [];
    $above = [$position[0]-1,$position[1]];
    if (isset($map[$above[0]][$above[1]])) {
        $surroundingPositions[] = $above;
    }
    $below = [$position[0]+1,$position[1]];
    if (isset($map[$below[0]][$below[1]])) {
        $surroundingPositions[] = $below;
    }
    $left = [$position[0],$position[1]-1];
    if (isset($map[$left[0]][$left[1]])) {
        $surroundingPositions[] = $left;
    }
    $right = [$position[0],$position[1]+1];
    if (isset($map[$right[0]][$right[1]])) {
        $surroundingPositions[] = $right;
    }

    // check all possible position
    foreach ($surroundingPositions as $surroundingPosition) {
        $actualElevation = $map[$position[0]][$position[1]];
        $surroundingPositionElevation = $map[$surroundingPosition[0]][$surroundingPosition[1]];
        // it's a possible way
        if ($surroundingPositionElevation - 1 <= $actualElevation) {
            // is there a shorter way to the destination as before?
            $distances[$position[0]][$position[1]] = min($distances[$position[0]][$position[1]], $distances[$surroundingPosition[0]][$surroundingPosition[1]] + 1);
        }
        // we havent's been hier yet, adding to queue and mark visited
        if (is_null($visited[$surroundingPosition[0]][$surroundingPosition[1]]) && $actualElevation <= $surroundingPositionElevation + 1) {
            $queue[] = $surroundingPosition;
            $visited[$surroundingPosition[0]][$surroundingPosition[1]] = true;
        }
    }
}

// task 1
var_dump($distances[$start[0]][$start[1]]);

// task 2: search for shortest "a"
$min = PHP_INT_MAX;
foreach ($map as $row => $columns) {
    foreach ($columns as $column => $elevation) {
        if ($elevation == 97) { // a
            $min = min($min, $distances[$row][$column]);
        }
    }
}
var_dump($min);
