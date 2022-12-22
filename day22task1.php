<?php

class Board {

    public $map = [];

    public $position;

    public $facing = 'R';

    public $width = 0;

    public $height = 0;

    public $path = [];

    public function go($instructions)
    {
        foreach ($instructions as $instruction) {
            $this->step($instruction);
        }

        return $this;
    }

    private function step($instruction)
    {
        if (is_numeric($instruction)) {
            echo sprintf("Trying to make %d steps facing %s\n", $instruction, $this->facing);
            for ($step = 0; $step < $instruction; $step++) {
                $this->updatePath();
                if (!$this->stepOne()) {
                    break;
                }
            }
            echo sprintf("Made %d steps in facing %s\n", $step, $this->facing);
        } else {
            echo sprintf("Turning %s\n", $instruction);
            $this->turn($instruction);
            $this->updatePath();
        }
    }

    private function stepOne()
    {
        switch ($this->facing) {
            case 'R':
                return $this->stepOneRight();
            case 'L':
                return $this->stepOneLeft();
            case 'U':
                return $this->stepOneUp();
            case 'D':
                return $this->stepOneDown();
        }

        return false;

    }

    private function stepOneRight()
    {
        if (($nextPosition = $this->nextRightPosition()) == $this->position) {
            return false;
        }
        $this->position = $nextPosition;
        return true;
    }

    private function stepOneLeft()
    {
        if (($nextPosition = $this->nextLeftPosition()) == $this->position) {
            return false;
        }
        $this->position = $nextPosition;
        return true;
    }

    private function stepOneUp()
    {
        if (($nextPosition = $this->nextUpPosition()) == $this->position) {
            return false;
        }
        $this->position = $nextPosition;
        return true;
    }

    private function stepOneDown()
    {
        if (($nextPosition = $this->nextDownPosition()) == $this->position) {
            return false;
        }
        $this->position = $nextPosition;
        return true;
    }

    private function nextRightPosition()
    {
        $nextColumn = $this->position[1] + 1;
        do {

            echo sprintf("Trying to go right, current position: %s, next position: %s\n", json_encode($this->position), json_encode([$this->position[0], $nextColumn]));

            if (isset($this->map[$this->position[0]][$nextColumn])) {
                // wall
                if ($this->map[$this->position[0]][$nextColumn] === '#') {
                    return $this->position;
                } elseif ($this->map[$this->position[0]][$nextColumn] === '.') { // open
                    return [$this->position[0], $nextColumn];
                }
            }
            // not defined and we're on the right edge of the current row
            if ($nextColumn >= $this->width - 1) { // already on the right edge of the current row
                $nextColumn = 0; // start from the other end
            } else {
                $nextColumn++;
            }
        } while (true);
    }

    private function nextDownPosition()
    {
        $nextRow = $this->position[0] + 1;

        do {

            echo sprintf("Trying to go down, current position: %s, next position: %s\n", json_encode($this->position), json_encode([$nextRow, $this->position[1]]));

            if (isset($this->map[$nextRow][$this->position[1]])) {
                // wall
                if ($this->map[$nextRow][$this->position[1]] === '#') {
                    return $this->position;
                } elseif ($this->map[$nextRow][$this->position[1]] === '.') { // open
                    return [$nextRow, $this->position[1]];
                }
            }
            // not defined and we're on the bottom edge of the current column
            if ($nextRow >= $this->height - 1) { // already on the bottom edge of the current column
                $nextRow = 0; // start from top
            } else {
                $nextRow++;
            }
        } while (true);
    }

    private function nextLeftPosition()
    {
        $nextColumn = $this->position[1] - 1;
        do {

            echo sprintf("Trying to go left, current position: %s, next position: %s\n", json_encode($this->position), json_encode([$this->position[0], $nextColumn]));

            if (isset($this->map[$this->position[0]][$nextColumn])) {
                // wall
                if ($this->map[$this->position[0]][$nextColumn] === '#') {
                    return $this->position;
                } elseif ($this->map[$this->position[0]][$nextColumn] === '.') { // open
                    return [$this->position[0], $nextColumn];
                }
            }
            // not defined and we're on the left edge of the current row
            if ($nextColumn <= 0) { // already on the left edge of the current row
                $nextColumn = $this->width - 1; // start from the other end
            } else {
                $nextColumn--;
            }
        } while (true);
    }

    private function nextUpPosition()
    {
        $nextRow = $this->position[0] - 1;

        do {

            echo sprintf("Trying to go up, current position: %s, next position: %s\n", json_encode($this->position), json_encode([$nextRow, $this->position[1]]));

            if (isset($this->map[$nextRow][$this->position[1]])) {
                // wall
                if ($this->map[$nextRow][$this->position[1]] === '#') {
                    return $this->position;
                } elseif ($this->map[$nextRow][$this->position[1]] === '.') { // open
                    return [$nextRow, $this->position[1]];
                }
            }
            // not defined and we're on the top edge of the current column
            if ($nextRow <= 0) { // already on the top edge of the current column
                $nextRow = $this->height - 1; // start from bottom
            } else {
                $nextRow--;
            }
        } while (true);
    }

    private function turn($direction) {
        switch ($this->facing . $direction) {
            case 'RR':
                $this->facing = 'D';
                return;
            case 'RL':
                $this->facing = 'U';
                return;
            case 'DR':
                $this->facing = 'L';
                return;
            case 'DL':
                $this->facing = 'R';
                return;
            case 'LR':
                $this->facing = 'U';
                return;
            case 'LL':
                $this->facing = 'D';
                return;
            case 'UR':
                $this->facing = 'R';
                return;
            case 'UL':
                $this->facing = 'L';
                return;
        }
    }

    private function updatePath()
    {
        switch ($this->facing) {
            case 'R':
                $this->path[$this->position[0]][$this->position[1]] = '>';
                break;
            case 'L':
                $this->path[$this->position[0]][$this->position[1]] = '<';
                break;
            case 'U':
                $this->path[$this->position[0]][$this->position[1]] = '^';
                break;
            case 'D':
                $this->path[$this->position[0]][$this->position[1]] = 'v';
                break;
        }
    }

    public function setStartPosition()
    {
        foreach ($this->map as $row => $columns) {
            foreach ($columns as $column => $tile) {
                if ($tile === '.') {
                    $this->position = [$row, $column];
                    break 2;
                }
            }
        }
        return $this;
    }

    public function calculateDimensions()
    {
        $this->height = count($this->map);
        $this->width = array_reduce($this->map, fn($width, $row) => max($width, count($row)), 0);

        return $this;
    }

    public function __toString()
    {
        $ret = '';

        foreach ($this->map as $row => $columns) {
            foreach ($columns as $column => $tile) {
                if ($this->position == [$row, $column]) {
                    switch ($this->facing) {
                        case 'R':
                            $ret .= '>';
                            break;
                        case 'L':
                            $ret .= '<';
                            break;
                        case 'U':
                            $ret .= '^';
                            break;
                        case 'D':
                            $ret .= 'v';
                            break;
                    }
                } elseif (isset($this->path[$row][$column])) {
                    $ret .= $this->path[$row][$column];
                } else {
                    $ret .= $tile;
                }
            }
            $ret .= "\n";
        }

        $ret .= sprintf("\nPassword: %d\n", $this->getPassword());

        return $ret;
    }

    public function getPassword()
    {
        return 1000 * ($this->position[0]+1) + 4 * ($this->position[1]+1) + $this->getFacingScore();
    }

    private function getFacingScore()
    {
        switch ($this->facing) {
            case 'R':
                return 0;
            case 'L':
                return 2;
            case 'U':
                return 3;
            case 'D':
                return 1;
        }
    }
}

//$input = file_get_contents(__DIR__ . '/test.txt');
$input = file_get_contents(__DIR__ . '/input1.txt');

$board = array_reduce(array_slice($lines = explode("\n", $input), 0, -2), function($board, $line) {
    $board->map[] = str_split($line);
    return $board;
}, new Board());

if (!preg_match_all('(([0-9]+)|(R|L)?)', $lines[count($lines)-1], $matches)) {
    throw new Exception('No steps could be read');
}

//var_dump($board->map[0]); die;

echo $board
    ->calculateDimensions()
    ->setStartPosition()
    ->go(array_filter($matches[0], fn($item) => mb_strlen($item) > 0))
;
