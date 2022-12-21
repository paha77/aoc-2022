<?php

$input = file_get_contents(__DIR__ . '/input1.txt');
$monkeys = array_reduce(explode("\n", $input), function($carry, $item) {
    if (!preg_match('#^([a-z]{4}): (([0-9]+)|(([a-z]{4}) (\+|-|\*|/) ([a-z]{4})))$#', $item, $matches)) {
        throw new Exception(sprintf('Invalid line: %s', $item));
    }

    // operation
    if (isset($matches[7])) {
        $carry[$matches[1]] = [
            'left' => $matches[5],
            'right' => $matches[7],
            'operation' => $matches[6],
        ];
    } else { // number
        $carry[$matches[1]] = $matches[3];
    }
    return $carry;
}, []);

var_dump(solve($monkeys, 'root'));

function solve($input, $key) {
    if (!is_array($input[$key])) {
        return $input[$key];
    }
    $op = $input[$key];
    unset($input[$key]);
    $left = solve($input, $op['left']);
    $right = solve($input, $op['right']);
    switch ($op['operation']) {
        case '+':
            return $left + $right;
        case '-':
            return $left - $right;
        case '*':
            return $left * $right;
        case '/':
            return $left / $right;
    }
}
