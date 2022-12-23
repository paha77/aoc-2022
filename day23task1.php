<?php

class Crater {

    public $map = [];

    public $top = 0;
    public $bottom = 0;
    public $left = 0;
    public $right = 0;

    public $elves = [];

    private $strategies = [
        'N' => [
            [[-1,  0], [-1, 0]], // N, N
            [[-1,  1], [-1, 0]], // NE, N
            [[-1, -1], [-1, 0]], // NW, N
        ],
        'S' => [
            [[1,  0], [1, 0]], // S, S
            [[1,  1], [1, 0]], // SE, S
            [[1, -1], [1, 0]], // SW, S
        ],
        'W' => [
            [[0,  -1], [0, -1]], // W, W
            [[-1, -1], [0, -1]], // NW, W
            [[1,  -1], [0, -1]], // SW, W
        ],
        'E' => [
            [[0,  1], [0, 1]], // E, E
            [[-1, 1], [0, 1]], // NE, E
            [[1,  1], [0, 1]], // SE, E
        ],
    ];

    public function simulate($rounds)
    {
        for ($round = 1; $round <= $rounds; $round++) {

            echo sprintf("Round %d\n\n", $round);

            echo sprintf("Before:\n\n%s\n\n", $this);

            // 1st half

            // get strategy for current round
            $strategies = $this->getRoundStrategies($round);

            echo sprintf("Round strategies: %s\n", json_encode($strategies));

            // make proposals
            $proposals = [];
            foreach ($this->elves as $elf => $elfPosition) {

                // look around
                $alone = true;
                foreach ($strategies as $strategy) {
                    foreach ($this->strategies[$strategy] as $direction) {
                        $positionToCheck = $elfPosition;
                        $positionToCheck[0] += $direction[0][0];
                        $positionToCheck[1] += $direction[0][1];
                        if (in_array($positionToCheck, $this->elves)) {
                            $alone = false;
                            break 2;
                        }
                    }
                }
                if ($alone) {
                    continue;
                }

                // try to move
                foreach ($strategies as $strategy) {
                    $strategyCanBeProposed = true;
                    foreach ($this->strategies[$strategy] as $direction) {

                        // one of the surrounding areas are not free
                        $positionToCheck = $elfPosition;
                        $positionToCheck[0] += $direction[0][0];
                        $positionToCheck[1] += $direction[0][1];

                        // area not free
                        if (in_array($positionToCheck, $this->elves)) {
                            $strategyCanBeProposed = false;
                            break; // jump to next strategy
                        }
                    }
                    // looks to be a good new area to be proposed
                    if ($strategyCanBeProposed) {
                        $proposals[$elf] = $elfPosition;
                        $proposals[$elf][0] += $direction[1][0];
                        $proposals[$elf][1] += $direction[1][1];

                        // jump to next elf
                        continue 2;
                    }
                }
            }

            // filter out duplicated proposals
            foreach ($elves = array_keys($proposals) as $elfA) {
                foreach ($elves as $elfB) {
                    if ($elfA === $elfB) {
                        continue;
                    }
                    // collision, delete both proposals
                    if (isset($proposals[$elfA]) && isset($proposals[$elfB]) && $proposals[$elfA] === $proposals[$elfB]) {
                        unset($proposals[$elfA]);
                        unset($proposals[$elfB]);
                    }
                }
            }

            // second round: make moves
            foreach ($proposals as $elf => $newPosition) {
                $this->extendMap($newPosition);
                $this->elves[$elf] = $newPosition;
            }

            echo $this . "\n\n***************************************\n\n";
        }

        return $this;
    }

    private function getRoundStrategies($round)
    {
        $roundStrategies = [];
        $keys = array_keys($this->strategies);
        $keyCount = count($keys);
        $diff = ($round-1) % $keyCount;

        for ($i=0; $i<count($keys);$i++) {
            $key = ($i + $diff) % $keyCount;
            $roundStrategies[] = $keys[$key];
        }

        return $roundStrategies;
    }

    public function calculateDimensions()
    {
        foreach ($this->map as $rowKey => $columns) {
            $this->top = min($this->top, $rowKey);
            $this->bottom = max($this->bottom, $rowKey);
            foreach ($columns as $columnKey => $area) {
                $this->left = min($this->left, $columnKey);
                $this->right = max($this->right, $columnKey);
            }
        }

        return $this;
    }


    private function extendMap($newPosition)
    {
        $this->top = min($this->top, $newPosition[0]);
        $this->bottom = max($this->bottom, $newPosition[0]);
        $this->left = min($this->left, $newPosition[1]);
        $this->right = max($this->right, $newPosition[1]);

        for ($row = $this->top; $row <= $this->bottom; $row++) {
            for ($column = $this->left; $column <= $this->right; $column++) {
                if (!isset($this->map[$row][$column])) {
                    $this->map[$row][$column] = '.';
                }
            }
        }
    }

    public function findElves()
    {
        foreach ($this->map as $row => $columns) {
            foreach ($columns as $column => $area) {
                if ($area === '#') {
                    $this->elves[] = [$row, $column];
                    $this->map[$row][$column] = '.';
                }
            }
        }

        return $this;
    }

    public function __toString()
    {
        $ret = '';

        for ($row = $this->top; $row <= $this->bottom; $row++) {
            for ($column = $this->left; $column <= $this->right; $column++) {
                $ret .= in_array([$row, $column], $this->elves) ? '#' : '.';
            }
            $ret .= "\n";
        }

        $ret .= sprintf("\nElves: %d\n", count($this->elves));
        $ret .= sprintf("Size: %d x %d\n", $this->bottom - $this->top + 1, $this->right - $this->left + 1);
        $ret .= sprintf("Empty ground: %d\n\n", $this->calculateEmptyGround());

        return $ret;
    }

    public function calculateEmptyGround()
    {
        return ($this->bottom - $this->top + 1) * ($this->right - $this->left + 1) - count($this->elves);
    }
}

//$input = file_get_contents(__DIR__ . '/test.txt');
$input = file_get_contents(__DIR__ . '/input1.txt');

$crater = array_reduce(explode("\n", $input), function($crater, $line) {
    $crater->map[] = str_split($line);
    return $crater;
}, new Crater());

$crater
    ->calculateDimensions()
    ->findElves()
    ->simulate(10)
;

// warning: getting rid of empty edges is not implemented yet, I had luck with my input, only the first line was empty
// after 10 rounds so I could do the math. :)