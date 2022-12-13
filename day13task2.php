<?php

$input = file_get_contents(__DIR__ . '/input.txt');

$sum = 0;
foreach (explode("\n\n", $input) as $index => $pair) {
    $pair = explode("\n", $pair);
    $pairs = [];
    foreach (explode("\n\n", $input) as $index => $pair) {
        $pairs[] = array_map(fn ($item) => json_decode($item), explode("\n", $pair));
    }
    $pairs[] = [[[2]], [[6]]];

    $prod = 1;
    foreach ($pairs as $index => $pair) {

        if ($pair[0] === [[2]] || $pair[1] === [[2]] || $pair[0] === [[6]] || $pair[1] === [[6]]) {
            continue;
        }

        echo sprintf("Comparing (Index: %d) %s <=> %s\n", $index+1, $this->flatten($pair[0]), $this->flatten($pair[1]));
        if ($this->compare($pair[0], $pair[1])) {
            echo "\tOK\n";
            $prod *= $index + 1;
        }
    }
    var_dump($prod);
}
function compare(array|int $left, array|int $right): bool
{
    echo sprintf("\tComparing %s, %s\n", flatten($left), flatten($right));
    if (!is_array($left)) {
        $left = [$left];
    }
    if (!is_array($right)) {
        $right = [$right];
    }

    $ret = true;
    foreach ($left as $leftKey => $leftValue) {
        if (!isset($right[$leftKey])) {
            echo sprintf("Right side run out of items.\n");
            $ret = false;
            break;
        } elseif (!is_array($leftValue) && !is_array($right[$leftKey])) {
            echo sprintf("Comparing integers %d <=> %d\n", $leftValue, $right[$leftKey]);
            if ($leftValue > $right[$leftKey]) {
                $ret = false;
                break;
            }
            if ($leftValue < $right[$leftKey]) {
                break;
            }
        } elseif (!compare($leftValue, $right[$leftKey])) {
            $ret = false;
            break;
        }
    }

    return $ret;
}
function flatten(array|int $values): string
{
    if (!is_array($values)) {
        return (string)$values;
    }

    $ret = '';
    foreach ($values as $value) {
        $ret .= ',' . flatten($value);
    }

    return $ret;
}