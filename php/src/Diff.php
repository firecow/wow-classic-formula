<?
declare(strict_types=1);

namespace App;

class Diff
{

    const UNMODIFIED = 0;
    const DELETED = 1;
    const INSERTED = 2;

    public static function compare($string1, $string2, $compareCharacters = false)
    {

        $start = 0;
        if ($compareCharacters) {
            $sequence1 = $string1;
            $sequence2 = $string2;
            $end1 = strlen($string1) - 1;
            $end2 = strlen($string2) - 1;
        } else {
            $sequence1 = preg_split('/\R/', $string1);
            $sequence2 = preg_split('/\R/', $string2);

            $end1 = count(is_array($sequence1) ? $sequence1 : []) - 1;
            $end2 = count(is_array($sequence2) ? $sequence2 : []) - 1;
        }

        // skip any common prefix
        while ($start <= $end1 && $start <= $end2 && $sequence1[$start] == $sequence2[$start]) {
            $start++;
        }

        // skip any common suffix
        while ($end1 >= $start && $end2 >= $start && $sequence1[$end1] == $sequence2[$end2]) {
            $end1--;
            $end2--;
        }

        // compute the table of longest common subsequence lengths
        $table = self::computeTable($sequence1, $sequence2, $start, $end1, $end2);

        // generate the partial diff
        $partialDiff = self::generatePartialDiff($table, $sequence1, $sequence2, $start);

        // generate the full diff
        $diff = array();
        for ($index = 0; $index < $start; $index++) {
            $diff[] = array($sequence1[$index], self::UNMODIFIED);
        }
        while (count($partialDiff) > 0) {
            $diff[] = array_pop($partialDiff);
        }
        for ($index = $end1 + 1; $index < ($compareCharacters ? strlen($sequence1) : count($sequence1)); $index++) {
            $diff[] = array($sequence1[$index], self::UNMODIFIED);
        }

        // return the diff
        return $diff;

    }

    public static function compareFiles($file1, $file2, $compareCharacters = false)
    {
        // return the diff of the files
        return self::compare(file_get_contents($file1), file_get_contents($file2), $compareCharacters);
    }

    private static function computeTable(array $sequence1, array $sequence2, int $start, int $end1, int $end2)
    {
        // determine the lengths to be compared
        $length1 = $end1 - $start + 1;
        $length2 = $end2 - $start + 1;

        // initialise the table
        $table = array(array_fill(0, $length2 + 1, 0));

        // loop over the rows
        for ($index1 = 1; $index1 <= $length1; $index1++) {

            // create the new row
            $table[$index1] = array(0);

            // loop over the columns
            for ($index2 = 1; $index2 <= $length2; $index2++) {

                // store the longest common subsequence length
                if ($sequence1[$index1 + $start - 1] == $sequence2[$index2 + $start - 1]) {
                    $table[$index1][$index2] = $table[$index1 - 1][$index2 - 1] + 1;
                } else {
                    $table[$index1][$index2] = max($table[$index1 - 1][$index2], $table[$index1][$index2 - 1]);
                }
            }
        }

        // return the table
        return $table;
    }

    private static function generatePartialDiff($table, $sequence1, $sequence2, $start)
    {
        //  initialise the diff
        $diff = array();

        // initialise the indices
        $index1 = count($table) - 1;
        $index2 = count($table[0]) - 1;

        // loop until there are no items remaining in either sequence
        while ($index1 > 0 || $index2 > 0) {

            // check what has happened to the items at these indices
            if ($index1 > 0 && $index2 > 0 && $sequence1[$index1 + $start - 1] == $sequence2[$index2 + $start - 1]) {

                // update the diff and the indices
                $diff[] = array($sequence1[$index1 + $start - 1], self::UNMODIFIED);
                $index1--;
                $index2--;

            } elseif ($index2 > 0 && $table[$index1][$index2] == $table[$index1][$index2 - 1]) {

                // update the diff and the indices
                $diff[] = array($sequence2[$index2 + $start - 1], self::INSERTED);
                $index2--;

            } else {

                // update the diff and the indices
                $diff[] = array($sequence1[$index1 + $start - 1], self::DELETED);
                $index1--;

            }

        }

        // return the diff
        return $diff;
    }

}
