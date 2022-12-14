<?php

$input = file_get_contents(__DIR__ . '/test.txt');
//$input = file_get_contents(__DIR__ . '/input1.txt');
$cave = array_reduce(explode("\n", $input), function ($cave, $lineToParse) {
    /** @var Cave $cave */
    if (preg_match_all('/([0-9]+,[0-9]+)?/', $lineToParse, $matches)) {
        $startPoint = null;
        foreach (array_reduce($matches[0], function($points, $possiblePoint) {
            if (mb_strlen($possiblePoint) == 0) {
                return $points;
            }
            return array_merge($points, [explode(',', $possiblePoint)]);
        }, []) as $point) {
            if (is_null($startPoint)) {
                $startPoint = $point;
            } else {
                $endPoint = $point;
                $cave->drawLine($startPoint, $endPoint);
                $startPoint = $endPoint;
            }
        }
    }

    return $cave;
}, new Cave());

//$m = memory_get_usage();
echo $cave->pour() . "\n\n";
//$m2 = memory_get_usage();
//var_dump($m2 - $m);
echo $cave;


class Cave
{
    public $points = [];
    public $sandPouringPoint;

    public function __construct()
    {
        $this->sandPouringPoint = [500, 0];
    }

    public function pour()
    {
        $sandsPoured = 0;
        do {
            $this->points[$this->sandPouringPoint[0]][$this->sandPouringPoint[1]] = 'o';
            if (!$this->fall($this->sandPouringPoint)) {
                break;
            }
            $sandsPoured++;
        } while (true);

        return $sandsPoured;
    }

    public function fall(array $point): bool
    {
        $newX = $point[0];
        $newY = $point[1];
        if ($this->__canFallDown($point)) {
            $newY++;
        } elseif ($this->__canFallDownAndLeft($point)) {
            $newX--;
            $newY++;
        }  elseif ($this->__canFallDownAndRight($point)) {
            $newX++;
            $newY++;
        }

        // in rest
        if ($newX === $point[0] && $newY === $point[1]) {
            return true;
        }

        // start to fall one step
        unset($this->points[$point[0]][$point[1]]);

        // point is over the edge
        if ($newY > $this->highestY() || $newX < $this->lowestX() || $newX > $this->highestX() ) {
            return false;
        }

        $this->points[$newX][$newY] = 'o';

        // keep falling
        // echo sprintf("Fall: %d, %d\n", $newX, $newY);
        return $this->fall([$newX, $newY]);
    }

    private function __canFallDown(array $point): bool
    {
        return !isset($this->points[$point[0]][$point[1] + 1]);
    }

    private function __canFallDownAndLeft(array $point): bool
    {
        return !isset($this->points[$point[0] - 1][$point[1] + 1]);
    }

    private function __canFallDownAndRight(array $point): bool
    {
        return !isset($this->points[$point[0] + 1][$point[1] + 1]);
    }

    public function drawLine($start, $end)
    {
        // horizontal line
        if ($start[1] === $end[1]) {
            for ($x = min($start[0], $end[0]); $x <= max($start[0], $end[0]); $x++) {
                $this->points[$x][$start[1]] = '#';
            }
        }
        // vertical line
        if ($start[0] === $end[0]) {
            for ($y = min($start[1], $end[1]); $y <= max($start[1], $end[1]); $y++) {
                $this->points[$start[0]][$y] = '#';
            }
        }
        $this->__rearrange();
    }

    public function __toString()
    {
        $lowestY = $this->lowestY();
        $ret = sprintf(
            "[Dimensions: %d x %d | Sand: %d | Lowest X: %d Highest X: %d Lowest Y: %d Highest Y: %d]\n",
            ($highestX = $this->highestX()) - ($lowestX = $this->lowestX()) + 1,
            ($highestY = $this->highestY()) + 1,
            $this->countSand(),
            $lowestX,
            $highestX,
            $lowestY,
            $highestY
        );

        for ($y = $lowestY; $y <= $highestY; $y++) {

            $row = '';

            if ($y === $lowestY) {
                $row .= ' ';
                for ($i = 0; $i <= $highestX - $lowestX; $i++) {
                    $row .= $i%10;
                }
                $row .= "\n";
            }

            $row .= $y%10;

            for ($x = $lowestX; $x <= $highestX; $x++) {
                $row .= $this->points[$x][$y] ?? '.';
            }
            $ret .= $row . "\n";
        }

        return $ret . "\n";
    }

    public function countSand()
    {
        return array_reduce($this->points, function ($count, $rows) {
            return $count + array_reduce($rows, function ($count, $point) {
                return $count + ($point === 'o' ? 1 : 0);
            }, 0);
        });
    }

    private function __rearrange()
    {
        ksort($this->points);
        foreach ($this->points as $x => $rows) {
            ksort($this->points[$x]);
        }
    }

    private function lowestX()
    {
        return min(array_keys($this->points));
    }

    private function highestX()
    {
        return max(array_keys($this->points));
    }

    private function lowestY(): int
    {
        return 0;
    }

    private function highestY(): int
    {
        return array_reduce($this->points, function ($highestY, $rows) {
            return max($highestY, max(array_keys($rows)));
        }, 0);
    }
}
