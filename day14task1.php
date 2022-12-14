<?php

$input = file_get_contents(__DIR__ . '/test.txt');
$cave = array_reduce(explode("\n", $input), function ($cave, $lineToParse) {
    /** @var Cave $cave */
    if (preg_match_all('/([0-9]+,[0-9]+)?/', $lineToParse, $matches)) {
        $startPoint = null;
        foreach (array_reduce($matches[0], function($points, $possiblePoint) {
            if (mb_strlen($possiblePoint) == 0) {
                return $points;
            }
            return array_merge($points, [new Point(explode(',', $possiblePoint), null, '#')]);
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

echo $cave;

class Cave
{
    public $points = [];
    public $lowestX = PHP_INT_MAX;
    public $highestX = 0;
    //public $lowestY = PHP_INT_MAX;
    public $lowestY = 0;
    public $highestY = 0;

    public function drawLine(Point $start, Point $end)
    {
        //echo sprintf("Drawing line with start: %s and end: %s\n", $start, $end);

        // horizontal line
        if ($start->y === $end->y) {
            for ($x = min($start->x, $end->x); $x <= max($start->x, $end->x); $x++) {
                $this->points[$x][$start->y] = new Point($x, $start->y, $start->type);
            }
        }
        // vertical line
        if ($start->x === $end->x) {
            for ($y = min($start->y, $end->y); $y <= max($start->y, $end->y); $y++) {
                $this->points[$start->x][$y] = new Point($start->x, $y, $start->type);
            }
        }
        $this->recalculate();
        echo $this;
    }

    public function __toString()
    {
        $ret = '';

        for ($y = $this->lowestY; $y <= $this->highestY; $y++) {
            $row = '';
            for ($x = $this->lowestX; $x <= $this->highestX; $x++) {
                $row .= isset($this->points[$x][$y]) ? $this->points[$x][$y]->type : '.';
            }
            $ret .= $row . "\n";
        }

        return $ret;
    }

    private function recalculate()
    {
        $this->lowestX = min(array_keys($this->points));
        $this->highestX = max(array_keys($this->points));
        foreach ($this->points as $x => $columns) {
            //$this->lowestY = min(array_merge(array_keys($columns), [$this->lowestY]));
            $this->highestY = max(array_merge(array_keys($columns), [$this->lowestY]));
        }
        //var_dump($this->lowestX, $this->highestX, $this->lowestY, $this->highestY);
    }
}

class Point
{
    public $x;
    public $y;
    public $type = '#';

    public function __construct($x, $y=null, $type = '#')
    {
        if (is_array($x)) {
            $this->x = $x[0];
            $this->y = $x[1];
        } else {
            $this->x = $x;
            $this->y = $y;
        }
        $this->type = $type;
    }

    public function __toString()
    {
        return sprintf("[%d,%d]", $this->x, $this->y);
    }
}
