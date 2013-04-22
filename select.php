<?php
passthru('clear');

echo "################################################################################\n"
   . "## The University of Kansas                                                   ##\n"
   . "## EECS 660, Fundamentals of Algorithms, Spring 2013                          ##\n"
   . "##                                                                            ##\n"
   . "## Homework 5 Program (select algorithm) for Bill Parrott                     ##\n"
   . "################################################################################\n\n";

parse_arguments($argv);

/*

function select(i, j, k:integer)
    // returns the kth smallest element in A[i..j]
    // r is the size of the subgroups

    n <- j - i + 1
    if n <= CUTOFF, then use insertion sort and return A[i + k - 1]
    else
        for m <- 1 to floor(n / r) do
            put the medians of the groups of size r into A[i], A[i+1], ...

        pivot <- select(i, i + floor(n/r) - 1, floor(1 + floor(n/r) / 2)) // recursive call
        p <- partition(i, j, pivot)
        if k <= p - i, then return (select(i, p-1, k))
        else
            partition A[p] to A[j] to find keys equal to the pivot
            if k is not in the range of keys equal to the pivot
                return (select(p,j,k-p+i)) // p is the index of the first key not equal to the pivot

*/

$numbers = file_get_contents(dirname(__FILE__) . '/' . INPUT_FILE);
$array   = preg_split("/\s+/", $numbers);

if (K_VALUE > count($array)) {
    echo "ERROR: The k-value you specify must lie within the bounds of the input array.\n";
    exit;
}

// Use 1-based array indexing to make the algorithm simpler
array_unshift($array, '-1');

$kthitem = select($array, 1, count($array), K_VALUE);
echo "The k-th item in the array is: {$kthitem}\n";

function select(array $A, $i, $j, $k) {
    $n = $j - $i + 1;
    if ($n <= CUTOFF) {
        $A = insertion_sort($A, $i, $j);
        return $A[$k];
    }

    $numgroups = floor($n / GROUP_SIZE);

    for ($m = 1; $m <= $numgroups; $m++) {
        //put the medians of the groups of size r into A[i], A[i+1], ...
    }

    $pivot = select($A, $i, $i + $numgroups - 1, floor(1 + ($numgroups / 2)));
    $p = partition($A, $i, $j, $pivot);

    if ($k <= $p - $i) {
        return select($A, $i, $p - 1, $k);
    }

//    partition A[p] to A[j] to find keys equal to the pivot
//    if k is not in the range of keys equal to the pivot
//        return (select(p,j,k-p+i)) // p is the index of the first key not equal to the pivot
    
}

function partition(array $A, $i, $j, $pivot) {
}

function insertion_sort(array $A, $i, $j) {
    for ($x = $i; $x < $j; $x++) {
        $ival = $A[$x];
        $hole = $x;

        while ($hole > $i && $ival < $A[$hole - 1]) {
            swap($A, $hole, $hole - 1);
            $hole--;
        }

        $A[$hole] = $ival;
    }

    return $A;
}

function swap(&$A, $x, $y) {
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
                if (empty($argv[$i + 1]) || !is_numeric($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--c\" parameter, you must follow it with an integer to use\nas the cutoff.\n";
                    exit;
                }
                define('CUTOFF', $argv[$i + 1]);
                $i++;
                break;
            case '--g':
                if (empty($argv[$i + 1]) || !is_numeric($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--g\" parameter, you must follow it with an integer to use\nas the group size.\n";
                    exit;
                }
                define('GROUP_SIZE', $argv[$i + 1]);
                $i++;
                break;
            case '--f':
                if (empty($argv[$i + 1]) || !is_string($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--f\" parameter, you must follow it with a string to use\nas the input file name.\n";
                    exit;
                }
                define('INPUT_FILE', $argv[$i + 1]);
                $i++;
                break;
            case '--k':
                if (empty($argv[$i + 1]) || !is_numeric($argv[$i + 1])) {
                    echo "ERROR: If you use the \"--k\" parameter, you must follow it with an integer to use\nas the index to retrieve.\n";
                    exit;
                }
                define('K_VALUE', $argv[$i + 1]);
                $i++;
                break;
        }
    }

    if (!defined('CUTOFF')) {
        define('CUTOFF',     100);
    }
    if (!defined('GROUP_SIZE')) {
        define('GROUP_SIZE', 7);
    }
    if (!defined('INPUT_FILE')) {
        define('INPUT_FILE', 'select.txt');
    }
    if (!defined('K_VALUE')) {
        define('K_VALUE',    0);
    }

    if (K_VALUE < 1) {
        echo "ERROR: You must specify a k-value with the \"--k\" parameter when executing the script.\n";
        exit;
    }
}