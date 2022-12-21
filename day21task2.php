<?php

$input = file_get_contents(__DIR__ . '/input1.txt');
$monkeys = array_reduce(explode("\n", $input), function($carry, $item) {
    if (!preg_match('#^([a-z]{4}): (([0-9]+)|(([a-z]{4}) (\+|-|\*|/) ([a-z]{4})))$#', $item, $matches)) {
        throw new Exception(sprintf('Invalid line: %s', $item));
    }

    // skipping humn
    if ($matches[1] === 'humn') {
        return $carry;
    }

    // operation
    if (isset($matches[7])) {
        $carry[$matches[1]] = [
            'left' => $matches[5],
            'right' => $matches[7],
            'operation' => $matches[1] === 'root' ? '=' : $matches[6],
        ];
    } else { // number
        $carry[$matches[1]] = $matches[3];
    }
    return $carry;
}, []);

$comparison = $monkeys['root'];
unset($monkeys['root']);

$humn = 1;
$right = solve($monkeys, $comparison['right'], $humn);
$left = solve($monkeys, $comparison['left'], $humn);

$min = 0;
$max = PHP_INT_MAX;

// trying with approximation
while($left != $right && $max != $min)
{
    $humn = (($max - $min) / 2) + $min;
    $left = solve($monkeys, $comparison['left'], $humn);
    $right = solve($monkeys, $comparison['right'], $humn);

    if ($left > $right) {
        $min = $humn;
    } elseif ($left < $right) {
        $max = $humn;
    } else {
        $max--;
        $min++;
    }
}

var_dump($humn);

function solve($input, $key, $humn) {

    if ($key === 'humn') {
        return $humn;
    }

    if (!is_array($input[$key])) {
        return $input[$key];
    }
    $op = $input[$key];
    unset($input[$key]);
    $left = solve($input, $op['left'], $humn);
    $right = solve($input, $op['right'], $humn);
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
