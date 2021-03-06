<?php
/**
 *  IacComparator
 *  Compare 2 variables
 *
 *  * strings: case/accent [in]sensitive, natural or computer ordering
 *  * numbers; within $decimals: eq ==, gt >, ge >=, lt <, le <=
 *    * Note: number vs string treats both as string
 *  * arrays: same keys, same key=>values, same key=>values in same position
 *  * object: ==
 *  * Multithreading each thread should use its own instance
 *
 *
 * @package iac\util
 * @author Raul Jose Santos Beranrd
 * @copyright 2015
 * @version 1.0.0
 */

/**
 * IacComparator
 * Compares 2 variables
 *
 *
 *  * strings: case/accent [in]sensitive, natural or computer ordering
 *  * numbers; within $decimals: eq ==, gt >, ge >=, lt <, le <=
 *  *  Note: number vs string treats both as string
 *  * arrays: same keys, same key=>values, same key=>values in same position
 *  * object: ==
 *  * Multithreading each thread should use its own instance
 *
 *
 * @package iac\util
 * @author Raul Jose Santos Beranrd
 * @copyright 2015
 * @version 1.0
 *
 */
class IacComparator {

    /**
     * $decimals
     * Number of decimals to use in numeric comparisons, default 2
     *
     * @var int $decimals default 2
     *
     * @see IacComparator::decimalsSetup() IacComparator::decimalsSetup() Setter for number of decimals
     */
    protected $decimals;

    /**
     * $decimals_max
     * Decimals
     *
     * @var float $decimals default 0.99
     *
     * @see IacComparator::decimalsSetup() IacComparator::decimalsSetup(), Setter for number of decimals
     */
    protected $decimals_max;

    /**
     * $haveBcMath
     * bcMath installed?
     *
     * @var bool haveBcMath true bcMath is installed, else false
     *
     */
    protected $haveBcMath;

    /**
     * $naturalSortingStrings
     * Compare using natural order
     *
     * @var bool $naturalSortingStrings true, default, comparse using strnatcmp, false, compares using stcmp
     */
    public $naturalSortingStrings;

    /**
     * $caseSesitiveStrings
     * Compare case Sesitive or case sensitive, default false
     *
     * @var bool $caseSesitiveStrings true comparse using case Sesitive, false, default, case sensitive
     */
    public $caseSesitiveStrings;

    /**
     * $accentSesitiveStrings
     * Compare accent Sesitive or accent sensitive, default false
     *
     * @var bool $accentSesitiveStrings true comparse using accent Sesitive, false, default, accent sensitive
     */
    public $accentSesitiveStrings;

    /**
     * $accented_chars
     * accented chars to substitute
     *
     * @var array $accented_chars accented chars to substitute
     *
     * @see IacComparator::unaccent() IacComparator::unaccent()
     */
    protected $accented_chars;

    /**
     * $unaccented_chars
     * accented chars to substitute with
     *
     * @var array $unaccented_chars accented chars to substitute with
     *
     * @see IacComparator::unaccent() IacComparator::unaccent()
     */
    protected $unaccented_chars;

    /**
     * $arraySamePosition
     * Compare case Sesitive or case sensitive, default false
     *
     * @var bool $arraySamePosition true order of keys in the array counts, false, default, key must exists in any order
     */
    public $arraySamePosition = false;

    /**
     * IacComparator::__construct()
     *
     * @param integer $decimals number of decimals to consider when comparing numbers, default 2
     * @param bool $caseSesitiveStrings compare strings considering case, default false
     * @param bool $accentSesitiveStrings compare strings considering accents, default false
     * @param bool $naturalSortingStrings compare strings considering natural order strnatcmp, default true
     * @param bool $arraySamePosition when comparing arrays consider key position true, or just key exists false. default false
     * @return void
     *
     * @see IacComparator::$caseSesitiveStrings IacComparator::$caseSesitiveStrings
     * @see IacComparator::$accentSesitiveStrings IacComparator::$accentSesitiveStrings
     * @see IacComparator::$naturalSortingStrings IacComparator::$naturalSortingStrings
     * @see IacComparator::$arraySamePosition IacComparator::$arraySamePosition
     * @see IacComparator::$haveBcMath IacComparator::$haveBcMath
     * @uses IacComparator::decimals_set() IacComparator::decimals_set()
     * @uses IacComparator::unaccentSetUp() IacComparator::unaccentSetUp()
     */
    public function __construct($decimals = 2,
                        $caseSesitiveStrings = false, $accentSesitiveStrings = false, $naturalSortingStrings = true,
                        $arraySamePosition = false
    ) {
        $this->decimals_set($decimals);
        $this->haveBcMath = function_exists('bccomp');
        $this->caseSesitiveStrings = $caseSesitiveStrings;
        $this->accentSesitiveStrings = $accentSesitiveStrings;
        $this->naturalSortingStrings = $naturalSortingStrings;
        $this->arraySamePosition = $arraySamePosition;
        $this->unaccentSetUp();
    }

    /**
     * IacComparator::decimals_set()
     * Decimals to use in numeric comparisons setter
     *
     * @param int $decimals number of decimals to use in numeric comparisons
     * @return void
     *
     * @uses IacComparator::$decimals IacComparator::$decimals
     * @uses IacComparator::$decimals_max IacComparator::$decimals_max
     */
    public function decimals_set($decimals) {
        $this->decimals = $decimals;
        $this->decimals_max = (float)('0.'.str_repeat('9',$decimals));
    }

    /**
     * IacComparator::useBcMath()
     * (Un)forces use of bccomp
     *
     * @param bool $use
     * @return void
     *
     * @see IacComparator::$haveBcMath IacComparator::$haveBcMath
     */
    public function useBcMath($use) {
        $this->haveBcMath = $use;
    }

    /**
     * IacComparator::eq()
     * Compares 2 variables, arrays must have same keys and values in any order.
     *
     *
     *  * numbers uses considers $decimals
     *  * strings use $caseSesitiveStrings, $accentSesitiveStrings and $naturalSortingStrings
     *  * arrays use  $arraySamePosition
     *
     *
     * @param mixed $obj1
     * @param mixed $obj2
     * @return bool true variables are equal, false are different
     *
     * @see IacComparator::$caseSesitiveStrings IacComparator::$caseSesitiveStrings
     * @see IacComparator::$accentSesitiveStrings IacComparator::$accentSesitiveStrings
     * @see IacComparator::$naturalSortingStrings IacComparator::$naturalSortingStrings
     * @see IacComparator::$arraySamePosition IacComparator::$arraySamePosition
     * @uses IacComparator::$decimals_max IacComparator::$decimals_max
     * @uses IacComparator::arrayIdentical() IacComparator::arrayIdentical()
     * @uses IacComparator::arrayEqual() IacComparator::arrayEqual()
     * @uses IacComparator::bccomp() IacComparator::bccomp()
     * @uses IacComparator::strcmp() IacComparator::strcmp()
     */
    public function eq($obj1, $obj2) {
        if(is_array($obj1) && is_array($obj2))
            if($this->arraySamePosition)
                return  $this->arrayIdentical($obj1, $obj2);
            else
                return  $this->arrayEqual($obj1, $obj2);

        if(is_numeric($obj1) && is_numeric($obj2)) {
            if($this->haveBcMath)
                return $this->bccomp($obj1,$obj2) === 0;
            return abs($obj1 - $obj2) <= $this->decimals_max;
        }

        if(is_object($obj1) || is_object($obj2))
            return $obj1 == $obj2;

        return $this->strcmp($obj1,$obj2) === 0;
    }

    /**
     * unaccent()
     * Remove diatrics from letters
     *
     * @param string $str
     * @param bool $utf8
     * @param bool $test_else on true force else for testing, internal
     * @return string with without accents
     *
     * @see IacComparator::http://www.evaisse.net/2008/php-translit-remove-accent-unaccent-21001
     * @uses IacComparator::$accented_chars IacComparator::$accented_chars
     * @uses IacComparator::$unaccented_chars IacComparator::$unaccented_chars
     */
    function unaccent($str, $utf8 = true,$test_else=true) {
        $str = (string) $str;
        if(is_null($utf8)) {
            if(function_exists('mb_detect_encoding') && $test_else) {
                $utf8 = (strtolower(mb_detect_encoding($str)) == 'utf-8');
            } else {
                $length = strlen($str);
                $utf8 = true;

                for ($i = 0; $i < $length; $i++) {
                    $c = ord($str[$i]);
                    if ($c < 0x80) $n = 0; // 0bbbbbbb
                    elseif (($c & 0xE0) == 0xC0) $n = 1; // 110bbbbb
                    elseif (($c & 0xF0) == 0xE0) $n = 2; // 1110bbbb
                    elseif (($c & 0xF8) == 0xF0) $n = 3; // 11110bbb
                    elseif (($c & 0xFC) == 0xF8) $n = 4; // 111110bb
                    elseif (($c & 0xFE) == 0xFC) $n = 5; // 1111110b
                    else return false; // Does not match any model
                    for ($j = 0; $j < $n; $j++) { // n bytes matching 10bbbbbb follow ?
                        if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80)) {
                            $utf8 = false;
                            break;
                        }
                    }
                }
            }

        }

        if(!$utf8)
            $str = utf8_encode($str);
        return str_replace($this->accented_chars, $this->unaccented_chars, $str);
    }


    /**
     * IacComparator::unaccentSetUp()
     * Sets up $unaccented_chars & $accetnted_chars for substitution in unaccent, called by constructor
     *
     * @return void
     *
     * @uses IacComparator::$accented_chars IacComparator::$accented_chars
     * @uses IacComparator::$unaccented_chars IacComparator::$unaccented_chars
     */
    protected function unaccentSetUp() {
        $transliteration = array(
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ý'=>'y',
            'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u',
            'ä'=>'a','ë'=>'e','ï'=>'i','ö'=>'o','ü'=>'u','ÿ'=>'y',
            'â'=>'a','ê'=>'e','î'=>'i','ô'=>'o','û'=>'u',

            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ý'=>'Y',
            'À'=>'A','È'=>'E','Ì'=>'I','Ò'=>'O','Ù'=>'U',
            'Ä'=>'A','Ë'=>'E','Ï'=>'I','Ö'=>'O','Ü'=>'U',
            'Â'=>'A','Ê'=>'E','Î'=>'I','Ô'=>'O','Û'=>'U','Ϋ' => 'Y',

            'ñ'=>'n','Ñ'=>'N',
            'ß' => 'ss','ẞ' => 'SS',
            'ç' => 'c', 'Ç' =>'C'
            ,'Ø' => 'O',
            'ş' => 's','Ș' => 'S',
            'Ț' => 'T',
            'ğ' => 'g', 'Ğ' => 'G',
        );
        $this->accented_chars = array_keys($transliteration);
        $this->unaccented_chars = array_values($transliteration);
    }

    /**
     * IacComparator::cmp()
     * Returns -1: numStr1 < numStr2, 0: numStr1 == numStr2, 1: numStr1 > numStr2, considering:
     *
     * * If both parameters are numeric compares considering $decimals
     * * If either parameter is string uses applies case/accent snesitive settings
     *
     * @param mixed $numStr1 string or number
     * @param mixed $numStr2 string or number
     * @return int < 0: numStr1 < numStr2, 0: numStr1 == numStr2, >0: numStr1 > numStr2
     *
     * @uses IacComparator::bccomp() IacComparator::bccomp()
     * @uses IacComparator::strcmp() IacComparator::strcmp()
     * @uses IacComparator::$decimals IacComparator::$decimals
     *
     */
    public function cmp($numStr1,$numStr2) {
        if(!is_numeric($numStr1) || !is_numeric($numStr2))
            return $this->strcmp($numStr1,$numStr2);

        if($this->haveBcMath)
            return $this->bccomp($numStr1,$numStr2);

       $numStr1 = round($numStr1,$this->decimals);
       $numStr2 = round($numStr2,$this->decimals);
       if($numStr1 === $numStr2)
            return 0;
       return $numStr1 < $numStr2 ? -1 : 1;
    }

    /**
     * IacComparator::bccomp()
     * bccomp call protecting -0 , using $this->decmials as scale
     *
     * @param float $num1
     * @param float $num2
     * @return < 1 $num1 < $num2, 0 $num1 == $num2, 1 $num1 > $num2
     *
     * @see IacComparator::decimals_set IacComparator::decimals_set
     * @uses IacComparator::$decimals IacComparator::$decimals
     */
    public function bccomp($num1,$num2) {
        if(substr($num1,0,3)==='-0.')
            $num1 = substr($num1,1);
        if(substr($num2,0,3)==='-0.')
            $num2 = substr($num2,1);
        return bccomp($num1,$num2,$this->decimals);
    }

    /**
     * IacComparator::strcmp()
     * strnatcmp function considering case/accent [in]sensitive and natural order or normal order
     *
     * @param string $str1
     * @param string $str2
     * @return int < 0: str1 < str2, 0: str1 == str2, >0: str1 > str2
     *
     * @uses IacComparator::$caseSesitiveStrings IacComparator::$caseSesitiveStrings
     * @uses IacComparator::$accentSesitiveStrings IacComparator::$accentSesitiveStrings
     * @uses IacComparator::$naturalSortingStrings IacComparator::$naturalSortingStrings
     */
    public function strcmp($str1,$str2) {
        if($this->naturalSortingStrings) {
            // use natural order
            if($this->accentSesitiveStrings)
                if($this->caseSesitiveStrings)
                    return strnatcasecmp($this->unaccent($str1), $this->unaccent($str2));
                else
                    return strnatcmp($this->unaccent($str1), $this->unaccent($str2));
            // accent Sesitive
            if($this->caseSesitiveStrings)
                return strnatcasecmp($str1, $str2);
            return strnatcmp($str1, $str2);
        }

        // strcmp, normal order
        if($this->accentSesitiveStrings)
            if($this->caseSesitiveStrings)
                return strcasecmp($this->unaccent($str1), $this->unaccent($str2));
            else
                return strcmp($this->unaccent($str1), $this->unaccent($str2));
        if($this->caseSesitiveStrings)
            return strcasecmp($str1, $str2);
        return strcmp($str1, $str2);
    }

    /**
     * IacComparator::gt()
     * Greater than
     *
     * * If both parameters are numbers considers $decimals
     * * If either parameter is string uses $strcmp
     *
     * @param mixed $numStr1 number or string to compare, left hand side
     * @param mixed $numStr2 number or string to compare, right hand side
     * @return bool true $numStr1 > $numStr2, false otherwise
     *
     *
     * @uses IacComparator::cmp() IacComparator::cmp()
     */
    public function gt($numStr1, $numStr2) {
        return $this->cmp($numStr1, $numStr2) > 0;
    }

    /**
     * IacComparator::ge()
     * Greater or equal than
     * If both parameters are numbers considers $decimals
     * If either parameter is string uses $strcmp
     *
     * @param mixed $numStr1 number or string to compare, left hand side
     * @param mixed $numStr2 number or string to compare, right hand side
     * @return bool true $numStr1 >= $numStr2, false otherwise
     *
     *
     * @uses IacComparator::cmp() IacComparator::cmp()
     */
    public function ge($numStr1, $numStr2) {
        return $this->cmp($numStr1, $numStr2) >= 0;
    }

    /**
     * IacComparator::lt()
     * Less than
     * If both parameters are numbers considers $decimals
     * If either parameter is string uses $strcmp
     *
     * @param mixed $numStr1 number or string to compare, left hand side
     * @param mixed $numStr2 number or string to compare, right hand side
     * @return bool true $numStr1 < $numStr2, false otherwise
     *
     *
     * @uses IacComparator::cmp() IacComparator::cmp()
     */
    public function lt($numStr1, $numStr2) {
        return $this->cmp($numStr1, $numStr2) < 0;
    }

    /**
     * IacComparator::le()
     * Less or equal than
     * If both parameters are numbers considers $decimals
     * If either parameter is string uses $strcmp
     *
     * @param mixed $numStr1 number or string to compare, left hand side
     * @param mixed $numStr2 number or string to compare, right hand side
     * @return bool true $numStr1 <= $numStr2, false otherwise
     *
     *
     * @uses IacComparator::cmp() IacComparator::cmp()
     */
    public function le($numStr1, $numStr2) {
        return $this->cmp($numStr1, $numStr2) <= 0;
    }

    /**
     * IacComparator::eqArray()
     * Compares 2 arrays so the have the same key=>value, keys may be in any position in the arrays
     *
     * @param array $array1
     * @param array $array2
     * @return bool true equal arrays same key=value (keys in any position), false otherwise
     *
     * @uses IacComparator::arrayEqual() IacComparator::arrayEqual()
     */
    public function eqArray($array1,$array2) {
        $arraySamePosition = $this->arraySamePosition;
        $this->arraySamePosition = false;
        $ret = $this->arrayEqual($array1,$array2);
        $this->arraySamePosition = $arraySamePosition;
        return $ret;
    }

    /**
     * IacComparator::eqArrayIdentical()
     * Compares 2 arrays so the have the same key=>value, keys in the same position
     *
     * @param array $array1
     * @param array $array2
     * @return bool true equal arrays same key=value (keys in same position), false otherwise
     *
     * @uses IacComparator::arrayIdentical() IacComparator::arrayIdentical()
     */
    public function eqArrayIdentical($array1,$array2) {
        $arraySamePosition = $this->arraySamePosition;
        $this->arraySamePosition = true;
        $ret = $this->arrayIdentical($array1,$array2);
        $this->arraySamePosition = $arraySamePosition;
        return $ret;
    }

    /**
     * IacComparator::arrays_have_same_keys()
     * Determine if 2 arrays have the same keys
     *
     * @param array $array1
     * @param array $array2
     * @param bool $firstLevelOnly true only first level, false recursevly
     * @return bool true the have the same keys, false have different keys
     *
     * @see IacComparator::$arraySamePosition IacComparator::$arraySamePosition
     * @uses IacComparator::arrays_identical_keys() IacComparator::arrays_identical_keys()
     */
    public function arrays_have_same_keys($array1, $array2,$firstLevelOnly=false) {
        if($this->arraySamePosition)
            return $this->arrays_identical_keys($array1, $array2,$firstLevelOnly);

        if(!is_array($array1) || !is_array($array2))
            return false;
        $diferentKeys = array_merge(array_diff_key($array1, $array2), array_diff_key($array2, $array1));
        if(!empty( $diferentKeys ))
            return false;

        foreach($array1 as $key=>$value) {
            if(is_array($value))
                if(is_array($array2[$key]))
                    return $this->arrays_have_same_keys($value, $array2[$key] );
        }
        return true;
    }

    /**
     * IacComparator::arrays_identical_keys()
     * Determine if 2 arrays have the same keys, in the same position
     *
     * @param array $array1
     * @param array $array2
     * @param bool $firstLevelOnly
     * @return bool true have same keys in same position, false otherwise
     */
    protected function arrays_identical_keys($array1, $array2,$firstLevelOnly=false) {
        if(!is_array($array1) || !is_array($array2))
            return false;
        $diferentKeys = array_merge(array_diff_key($array1, $array2), array_diff_key($array2, $array1));
        if(!empty( $diferentKeys ))
            return false;

        reset($array2);
        $key2 = key($array2);
        foreach($array1 as $key=>$value) {
            if($key !== $key2)
                return false;
            next($array2);
            $key2 = key($array2);
        }

        if($firstLevelOnly)
            return true;

        foreach($array1 as $key=>$value) {
            if(is_array($value) && !is_array($array2[$key]))
                return false;
            if(!is_array($value) && is_array($array2[$key]))
                return false;
            if(is_array($value) && is_array($array2[$key]))
                if(!$this->arrays_identical_keys($value, $array2[$key], false) )
                    return false;
        }
        return true;
    }

    /**
     * IacComparator::arrayEqual()
     * Determine if arrays are equal
     *  arrays must have same keys and values in any order
     *
     * @param array $array1
     * @param array $array2
     * @return bool true if equal keys in any order, false if different
     *
     * @uses IacComparator::eq() IacComparator::eq() IacComparator::eq to compare values
     */
    protected function arrayEqual($array1, $array2) {
        if(is_array($array1) && is_array($array2)) {
            $diferentKeys = array_merge(array_diff_key($array1, $array2), array_diff_key($array2, $array1));
            if(!empty( $diferentKeys ))
                return false;
        }
        if((is_array($array1) && !is_array($array2)) || (is_array($array2) && !is_array($array1)) )
            return false;
        foreach($array1 as $key=>$value)
            if(!$this->eq($value, $array2[$key]))
                return false;
        return true;
    }

    /**
     * IacComparator::arrayIdentical()
     * Determine if arrays are identical
     *  arrays must have same keys and values in exactly the same order
     *
     * @param array $array1
     * @param array $array2
     * @return bool true if equal and keys in the same position, false if different
     *
     * @uses IacComparator::eq() IacComparator::eq() IacComparator::eq to compare values
     */
    protected function arrayIdentical($array1, $array2) {
        if(is_array($array1) && is_array($array2)) {
            $diferentKeys = array_merge(array_diff_key($array1, $array2), array_diff_key($array2, $array1));
            if(!empty( $diferentKeys ))
                return false;
        }
        if((is_array($array1) && !is_array($array2)) || (is_array($array2) && !is_array($array1)) )
            return false;
        $current2 = reset($array2);
        foreach($array1 as $key=>$value) {
            if($key != key($array2))
                return false;
            if(!$this->eq($value, $current2 ))
                return false;
            $current2 = next($array2);
        }
        return true;
    }

}
