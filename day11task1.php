<?php

const ROUNDS = 10000; // 20;
const RELIEF = false; // true;

$input = file_get_contents(__DIR__ . '/input.txt');

/** @var Monkey[] $monkeys */
$monkeys = array_reduce(explode("\n\n", $input), function ($carry, $item) {

    $itemLines = explode("\n", $item);

    if (!isset($itemLines[1]) || !preg_match('/items: (.+)$/i', $itemLines[1], $matches)) {
        return $carry;
    }

    $monkey = new Monkey();
    $monkey->items = array_map(fn (string $item) => new Item((int)$item), explode(', ', $matches[1]));

    if (!preg_match('/operation: (.+)$/i', $itemLines[2], $matches)) {
        throw new Exception(sprintf('Operation missing: %s', $item));
    }
    $monkey->operationRule = $matches[1];

    if (!preg_match('/divisible by (\d+)$/i', $itemLines[3], $matches)) {
        throw new Exception(sprintf('Test rule divider missing: %s', $item));
    }
    $monkey->testDivider = (int)$matches[1];

    if (!preg_match('/if true: throw to monkey (\d+)$/i', $itemLines[4], $matches)) {
        throw new Exception(sprintf('Test true monkey missing: %s', $item));
    }
    $monkey->testTrueMonkey = (int)$matches[1];

    if (!preg_match('/if false: throw to monkey (\d+)$/i', $itemLines[5], $matches)) {
        throw new Exception(sprintf('Test false monkey missing: %s', $item));
    }
    $monkey->testFalseMonkey = (int)$matches[1];

    $carry[] = $monkey;

    return $carry;
}, []);

$lcm = 1;
foreach ($monkeys as $monkey) {
    $lcm = $lcm * $monkey->testDivider;
}

for ($round = 1; $round <= ROUNDS; $round++) {
    foreach ($monkeys as $monkeyKey => $monkey) {
        $monkey->lcm = $lcm;
        while ($item = array_shift($monkey->items)) {
            $monkey->inspect($item, RELIEF);
            $newMonkeyKey = $monkey->test($item) ? $monkey->testTrueMonkey : $monkey->testFalseMonkey;
            array_push($monkeys[$newMonkeyKey]->items, $item);
        }
    }
}

$inspectionCounts = array_map(fn ($monkey) => $monkey->inspectionCount, $monkeys);
rsort($inspectionCounts);

echo sprintf("%d\n", $inspectionCounts[0] * $inspectionCounts[1]);

class Monkey
{
    public $items = [];

    public $operationRule;

    public $testDivider;

    public $testTrueMonkey;

    public $testFalseMonkey;

    public $inspectionCount = 0;

    public $lcm = 1;

    public function operation(Item $item): void
    {
        eval('$item->worryLevel = ' . str_replace(['new = ', 'old'], ['', '$item->worryLevel'], $this->operationRule) . ';');
    }

    public function inspect(Item $item, $relief = true): void
    {
        $this->inspectionCount++;
        $this->operation($item);
        // monkey gets bored
        if ($relief) {
            $item->worryLevel = floor($item->worryLevel / 3);
        } else {
            $item->worryLevel = $item->worryLevel % $this->lcm;
        }
    }

    public function test(Item $item): bool
    {
        return ($item->worryLevel % $this->testDivider) === 0;
    }
}

class Item
{
    public $worryLevel;

    public function __construct($worryLevel)
    {
        $this->worryLevel = $worryLevel;
    }
}
