<?php
passthru('clear');

error_reporting(0);

echo "################################################################################\n"
   . "## The University of Kansas                                                   ##\n"
   . "## EECS 660, Fundamentals of Algorithms, Spring 2013                          ##\n"
   . "##                                                                            ##\n"
   . "## Homework 5 Program (select algorithm) for Bill Parrott                     ##\n"
   . "################################################################################\n\n";

parse_arguments($argv);

if (VERIFY_ALGORITHM) {
    verify_algorithm();
} else {
    run_tests();
}

function run_tests() {
    mt_srand(time());

    $testsizes = array(
        25000,
        50000,
        100000,
        200000,
    );

    $kvals = array();
    $times = array();

    foreach ($testsizes as $datasize => $size) {
        echo "#### Data Size: {$size}\n";

        for ($i = 1; $i <= 10; $i++) {
            echo "## Test #{$i}\n";

            $data = generateData($size);
            $k = getRandomKValue($size);
            $kvals[$datasize][] = $k;

            // Use 1-based array indexing to make the algorithm simpler
            array_unshift($data, NULL);

            echo "k = {$k}\n";

            $time_start = gettime();

            $kthitem = select($data, 1, $size, $k);

            $time_end = gettime();
            $time = $time_end - $time_start;

            $times[$datasize][] = $time;

            echo "The k-th smallest item is: {$kthitem}\n";
            echo "Finding it took $time seconds.\n\n";
        }
    }

    for ($i = 0; $i < count($times); $i++) {
        $sum = 0;
        for ($j = 0; $j < 10; $j++) {
            $sum += $times[$i][$j];
        }
        $average = $sum / 10;

        echo "The average time for data sets of size {$testsizes[$i]} was: {$average}\n";
    }
}

function select(array &$A, $left, $right, $k) {
    $n = $right - $left + 1;
    if ($n <= CUTOFF || $n < GROUP_SIZE) {
        insertion_sort($A, $left, $right);
        return $k + $left - 1;
    }

    $numgroups = ceil($n / GROUP_SIZE);
    $start  = $left;
    for ($m = 1; $m <= $numgroups; $m++) {
        $finish = $start + GROUP_SIZE - 1;
        if ($finish > $right) {
            $finish = $right;
        }

        insertion_sort($A, $start, $finish);

        $median = floor(($finish - $start) / 2) + $start;
        swap($A, $left + $m - 1, $median);

        $start = $finish + 1;
    }

    $pivot = select($A, $left, $left + $numgroups - 1, floor(1 + ($numgroups / 2)));
    $p     = partition($A, $left, $right, $pivot);

    if ($k <= $p - $left) {
        return select($A, $left, $p - 1, $k);
    }

    $p = partition($A, $p, $right, $p, true);
    if ($k + $left < $p) {
        return $p;
    }

    return select($A, $p + 1, $right, $k - $p + $left - 1);
}

function partition(array &$A, $start, $finish, $pivot, $findequal = false) {
    $pivotval = $A[$pivot];
    swap($A, $finish, $pivot);
    $pivotidx = $start;

    for ($i = $start; $i < $finish; $i++) {
        if ($A[$i] < $pivotval || ($A[$i] == $pivotval && $findequal)) {
            swap($A, $i, $pivotidx++);
        }
    }
    swap($A, $pivotidx, $finish);
    return $pivotidx;
}

function insertion_sort(array &$A, $start, $finish) {
    for ($i = $start; $i <= $finish; $i++) {
        $ival = $A[$i];
        $hole = $i;

        while ($hole > $start && $ival < $A[$hole - 1]) {
            swap($A, $hole, $hole - 1);
            $hole--;
        }

        $A[$hole] = $ival;
    }
}

function swap(array &$A, $x, $y) {
    $tmp   = $A[$x];
    $A[$x] = $A[$y];
    $A[$y] = $tmp;
}

function show_help() {
    echo "To change the default options when running the program, the following\narguments can be passed during execution:\n\n"
       . "--c <number>                Specify a different number for the cutoff\n"
       . "                            Default: 100\n\n"
       . "--f <string>                Specify a different input file name\n"
       . "                            Default: select.txt\n\n"
       . "--g <number>                Specify a different number for the group size\n"
       . "                            Default: 7\n\n"
       . "--help                      Shows this help screen\n\n"
       . "--k <number>                Specify a k-value of the index to search for\n"
       . "                            Default: none\n";
}

function parse_arguments($argv) {
    array_shift($argv);

    $i = 0;
    for ($i = 0; $i < count($argv); $i++) {
        switch ($argv[$i]) {
            case '--help':
                show_help();
                exit;
                break;
            case '--c':
                if (!isset($argv[$i + 1]) || !is_numeric($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--c\" parameter, you must follow it with an integer to use\nas the cutoff.\n";
                    exit;
                }
                define('CUTOFF', $argv[$i + 1]);
                $i++;
                break;
            case '--g':
                if (!isset($argv[$i + 1]) || !is_numeric($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--g\" parameter, you must follow it with an integer to use\nas the group size.\n";
                    exit;
                }
                define('GROUP_SIZE', $argv[$i + 1]);
                $i++;
                break;
            case '--f':
                if (!isset($argv[$i + 1]) || !is_string($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--f\" parameter, you must follow it with a string to use\nas the input file name.\n";
                    exit;
                }
                define('INPUT_FILE', $argv[$i + 1]);
                $i++;
                break;
            case '--k':
                if (!isset($argv[$i + 1]) || !is_numeric($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--k\" parameter, you must follow it with an integer to use\nas the index to retrieve.\n";
                    exit;
                }
                define('K_VALUE', $argv[$i + 1]);
                $i++;
                break;
            case '--verify':
                define('VERIFY_ALGORITHM', true);
                break;
        }
    }

    if (!defined('VERIFY_ALGORITHM')) {
        define('VERIFY_ALGORITHM', false);
        define('K_VALUE', 1);
    }
    if (!defined('CUTOFF')) {
        define('CUTOFF', 100);
    }
    if (!defined('GROUP_SIZE')) {
        define('GROUP_SIZE', 7);
    }
    if (!defined('INPUT_FILE')) {
        define('INPUT_FILE', 'select.txt');
    }
    if (!defined('K_VALUE')) {
        define('K_VALUE', 0);
    }

    if (K_VALUE < 1) {
        echo "ERROR: You must specify a k-value with the \"--k\" parameter when executing the script.\n";
        exit;
    }
}

function verify_algorithm() {
    $numbers = file_get_contents(dirname(__FILE__) . '/' . INPUT_FILE);
    $array   = preg_split("/\s+/", $numbers);

    if (K_VALUE > count($array)) {
        echo "ERROR: The k-value you specify must lie within the bounds of the input array.\n";
        exit;
    }

    // Use 1-based array indexing to make the algorithm simpler
    array_unshift($array, NULL);

    echo 'Searching for the k-th smallest item, k = ' . K_VALUE . ', in file ' . INPUT_FILE . ".\n";
    echo 'Using r = ' . GROUP_SIZE . ' and cutoff = ' . CUTOFF . "\n\n";
    $kthitem = select($array, 1, count($array) - 1, K_VALUE);
    echo "The k-th item in the array is: {$array[$kthitem]}\n";
}

function gettime() {
    return microtime(true);
}

function generateData($size = 0) {
    if ($size == 0) {
        echo 'ERROR: This should never happen. It\'s kind of like dividing by zero.' . "\n";
        exit;
    }

    $data = array();

    for ($i = 1; $i <= $size; $i++) {
        $data[] = mt_rand(1, $size);
    }

    return $data;
}

function getRandomKValue($size = 0) {
    if ($size == 0) {
        echo 'ERROR: This should never happen. It\'s kind of like dividing by zero.' . "\n";
        exit;
    }

    return mt_rand(1, $size);
}

function printarr(array $a) {
    for ($i = 1; $i < count($a); $i++) {
        echo $a[$i] . ' ';
    }
    echo "\n";
}