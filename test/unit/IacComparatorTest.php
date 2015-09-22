<?php
/**
 * @coversDefaultClass IacComparator
 */

class IacComparatorTest extends PHPUnit_Framework_TestCase  {
    protected $cmp;

    public function setUp() {
        $this->cmp = new IacComparator();
    }

    public function tearDown() {
        $this->cmp = NULL;
    }

    /**
     * IacComparatorTest::test_eq()
     * Tests eq
     *
     * @testdox eq: are 2 varibales equal
     *
     * @covers IacComparator::eq
     * @covers IacComparator::bccomp
     *
     * @covers IacComparator::unaccent
     * @covers IacComparator::unaccentSetUp
     * @covers IacComparator::__construct
     * @covers IacComparator::decimals_set
     * @covers IacComparator::useBcMath
     * @uses   IacComparator::strcmp
     * @uses   IacComparator::arrayEqual
     * @uses   IacComparator::arrayIdentical
     *
     */
    public function test_eq() {
        $array = array("apple"=>"manzana","orange"=>"naranja","watermelon"=>"sandia");
        $array2 = array("orange"=>"naranja","watermelon"=>"sandia","apple"=>"manzana");
        $this->cmp->arraySamePosition = false;
        $this->assertEquals($this->cmp->eq($array, $array2), true, "Equal arrays" );
        $this->cmp->arraySamePosition = true;
        $this->assertEquals($this->cmp->eq($array, $array2), false, "Identical arrays" );
        $this->cmp->arraySamePosition = false;

        $this->assertEquals($this->cmp->eq(3.01, 3.011), true,"Equal numbers, more decimal digits" );

        $this->assertEquals($this->cmp->eq(3.99, 3.99), true,"Equal numbers, within epsilon" );

        $this->assertEquals($this->cmp->eq(3.002, 3.001), true, "Greater than, within epsilon" );
        $this->assertEquals($this->cmp->eq(3.001, 3.002), true, "Less than, within epsilon" );

        $this->assertEquals($this->cmp->eq(3.001, 3.001), true,"Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->eq(3.002, 3.001), true, "Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->eq(3.001, 3.002), true, "Less than, more decimal digits" );

        $this->assertEquals($this->cmp->eq(-3.001, -3.001), true,"Negative Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->eq(-3.001, -3.002), true, "Negative Greater than, within epsilon" );
        $this->assertEquals($this->cmp->eq(-3.002, -3.001), true, "Negative Less than, within epsilon" );

        $this->assertEquals($this->cmp->eq(-3.999, -3.999), true,"Negative Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->eq(-3.999, -3.9998), true, "Negative Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->eq(-3.9998, -3.999), true, "Negative Less than, more decimal digits" );

        $this->cmp->useBcMath(false);
        $this->assertEquals($this->cmp->eq(-3.9998, -3.999), true, "No bccmath Less than, more decimal digits" );

        $this->assertEquals($this->cmp->eq("abcd", "abcd"), true, "strings" );
        $this->assertEquals($this->cmp->eq("abcd", 3), false, "strings vs numbers" );

        $obj1 = new IacComparator();
        $obj2 = new IacComparator();
        $this->assertEquals($this->cmp->eq($obj1, $obj2), true, "Equal same objects" );
        $obj2->decimals_set(9);
        $this->assertEquals($this->cmp->eq($obj1, $obj2), false, "Same oject difrent properties" );
        $this->assertEquals($this->cmp->eq($obj1, $this), false, "Diferent objects" );
    }

    /**
     * IacComparatorTest::test_unaccent()
     * Tests unaccent
     *
     * @testdox unaccent: remove accents, diactrics, from letters
     *
     * @covers IacComparator::unaccent
     * @covers IacComparator::unaccentSetUp
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     */
    public function test_unaccent() {
        $ac = "áéíóúý ÁÉÍÓÚÝ àèìòù ÀÈÌÒÙ äëïöüÿ ÄËÏÖÜ âêîôû ÂÊÎÔÛ ñÑ";
        $un = "aeiouy AEIOUY aeiou AEIOU aeiouy AEIOU aeiou AEIOU nN";
        $this->assertEquals($this->cmp->unaccent($ac), $un, "Diactrics letters" );
        $this->assertEquals($this->cmp->unaccent($ac,null), $un, "Diactrics letters" );
        //$this->assertEquals($this->cmp->unaccent($ac,null,false), $un, "Diactrics letters" );
        //$this->assertEquals($this->cmp->unaccent($un,null,false), $un, "Diactrics letters" );
    }

    /**
     * IacComparatorTest::test_strcmp()
     * Tests strcmp
     *
     * @testdox strcmp: get < 0,0,> 0 in comparing strings, considerint string comparison settings
     *
     * @covers IacComparator::strcmp
     * @covers IacComparator::unaccentSetUp
     * @covers IacComparator::unaccent
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     *
     */
    public function test_strcmp() {
        $this->assertEquals($this->cmp->strcmp("99","100")<0, true, "Natural order, default" );
        $this->assertEquals($this->cmp->strcmp("A Blue moon","A blue moon"), -1, "case sensitive default" );
        $this->assertEquals($this->cmp->strcmp("A Blue moon","A Blúe moon"), -1, "accent sensitive default" );

        $this->cmp->naturalSortingStrings = false;
        $this->cmp->caseSesitiveStrings = false;
        $this->cmp->accentSesitiveStrings = false;
        $this->assertEquals($this->cmp->strcmp("A blue moon","A blue moon"), 0, "Same order, natural no" );
        $this->assertEquals($this->cmp->strcmp("A blue moon","A rlue moon")<0, true, "Lower order, natural no" );
        $this->assertEquals($this->cmp->strcmp("A rlue moon","A blue moon")>0, true, "Larger order, natural no" );
        $this->assertEquals($this->cmp->strcmp("99","100")>0, true, "Ascci order, accent Sesitive" );

        $this->cmp->caseSesitiveStrings = true;
        $this->assertEquals($this->cmp->strcmp("A Blue MOON","A blue moon"), 0, "Same order, natural no, case Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Blue MOON","A rlue moon")<0, true, "Lower order, natural no, case Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Rlue MOON","A blue moon")>0, true, "Larger order, natural no case Sesitive" );

        $this->cmp->accentSesitiveStrings = true;
        $this->assertEquals($this->cmp->strcmp("A Blúe MOON","A blue moon"), 0, "Same order, natural no case accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Blúe MOON","A rlue moon")<0, true, "Lower order, natural no case accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Rlúe MOON","A blue moon")>0, true, "Larger order, natural no case accent Sesitive" );

        $this->cmp->caseSesitiveStrings = false;
        $this->assertEquals($this->cmp->strcmp("A blúe moon","A blue moon"), 0, "Same order, natural no accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A blúe moon","A rlue moon")<0, true, "Lower order, natural no accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A rlúe moon","A blue moon")>0, true, "Larger order, natural no accent Sesitive" );

        $this->cmp->naturalSortingStrings = true;
        $this->cmp->caseSesitiveStrings = false;
        $this->cmp->accentSesitiveStrings = false;
        $this->assertEquals($this->cmp->strcmp("A blue moon","A blue moon"), 0, "Same order, natural" );
        $this->assertEquals($this->cmp->strcmp("A blue moon","A rlue moon")<0, true, "Lower order, natural" );
        $this->assertEquals($this->cmp->strcmp("A rlue moon","A blue moon")>0, true, "Larger order, natural" );
        $this->assertEquals($this->cmp->strcmp("99","100")<0, true, "Natural order, sensitive" );

        $this->cmp->caseSesitiveStrings = true;
        $this->assertEquals($this->cmp->strcmp("A Blue MOON","A blue moon"), 0, "Same order, natural, case Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Blue MOON","A rlue moon")<0, true, "Lower order, natural, case Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Rlue MOON","A blue moon")>0, true, "Larger order, natural, case Sesitive" );
        $this->assertEquals($this->cmp->strcmp("99","100")<0, true, "Natural order, case Sesitive" );

        $this->cmp->accentSesitiveStrings = true;
        $this->assertEquals($this->cmp->strcmp("A Blúe MOON","A blue moon"), 0, "Same order, natural, case accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Blúe MOON","A rlue moon")<0, true, "Lower order, natural, case accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A Rlúe MOON","A blue moon")>0, true, "Larger order, natural, case accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("99","100")<0, true, "Natural order, case accent Sesitive" );

        $this->cmp->caseSesitiveStrings = false;
        $this->assertEquals($this->cmp->strcmp("A blúe moon","A blue moon"), 0, "Same order, natural, accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A blúe moon","A rlue moon")<0, true, "Lower order, natural, accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("A rlúe moon","A blue moon")>0, true, "Larger order, natural, accent Sesitive" );
        $this->assertEquals($this->cmp->strcmp("99","100")<0, true, "Natural order, accent Sesitive" );


    }

    /**
     * IacComparatorTest::test_cmp()
     * Tests cmp
     *
     * @testdox cmp: get < 0,0,> 0 in comparing numbers or strings, considering number of decimals
     *
     * @covers IacComparator::cmp
     * @covers   IacComparator::strcmp
     * @covers IacComparator::bccomp
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @covers IacComparator::unaccentSetUp
     * @covers IacComparator::unaccent
     * @covers IacComparator::useBcMath
     */
    public function test_cmp() {
        $this->cmp->decimals_set(2);
        $this->assertEquals($this->cmp->cmp("99",100)<0, true, "Mixed strings, numbers." );
        $this->assertEquals($this->cmp->cmp(100,"99")>0, true, "Mixed strings, numbers." );

        $this->assertEquals($this->cmp->cmp(100,99)>0, true, "100 > 99" );

        $this->assertEquals($this->cmp->cmp(3.01, 3.01), 0,"Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->cmp(3.02, 3.01)>0, true, "Greater than, within epsilon" );
        $this->assertEquals($this->cmp->cmp(3.01, 3.02)<0, true, "Less than, within epsilon" );

        $this->assertEquals($this->cmp->cmp(3.001, 3.001), 0,"Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->cmp(3.002, 3.001)==0, true, "Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->cmp(3.001, 3.002)==0, true, "Less than, more decimal digits" );

        $this->assertEquals($this->cmp->cmp(-3.01, -3.01), 0,"Negative Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->cmp(-3.01, -3.02)>0, true, "Negative Greater than, within epsilon" );
        $this->assertEquals($this->cmp->cmp(-3.02, -3.01)<0, true, "Negative Less than, within epsilon" );

        $this->assertEquals($this->cmp->cmp(-3.001, -3.001), 0,"Negative Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->cmp(-3.001, -3.002)==0, true, "Negative Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->cmp(-3.002, -3.001)==0, true, "Negative Less than, more decimal digits" );

        $this->assertEquals($this->cmp->cmp(0.0001, 0.0002), 0, "Near zero Less than, more decimal digits" );

        $this->assertEquals($this->cmp->cmp(0.003, 0.001), 0, "Near zero Less than 0.003 vs 0.001, more decimal digits" );
        $this->assertEquals($this->cmp->cmp(0.001, 0.003), 0, "Near zero Less than 0.001 vs 0.003, more decimal digits" );

        $this->assertEquals($this->cmp->cmp(0.001, -0.002), 0, "Near zero Negative/positive Less than, more decimal digits" );
        $this->assertEquals($this->cmp->cmp(-0.001, 0.002), 0, "Near zero Negative/positive Less than, more decimal digits" );

        $this->cmp->useBcMath(false);
        $this->assertEquals($this->cmp->cmp(99,100)<0, true, "No bcmath 99 < 100" );
        $this->assertEquals($this->cmp->cmp(3.144,3.142), 0, "No bcmath equal" );
    }



    /**
     * IacComparatorTest::test_gt()
     * Tests gt
     *
     * @testdox gt: is first numbers or strings greater than second,
     *
     * @covers IacComparator::gt
     * @covers IacComparator::cmp
     * @covers IacComparator::bccomp
     * @uses   IacComparator::strcmp
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @uses IacComparator::unaccentSetUp
     * @uses IacComparator::unaccent
     * @uses IacComparator::strcmp
     */
     public function test_gt() {
        $this->assertEquals($this->cmp->gt(3.01, 3.01), false,"Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->gt(3.02, 3.01), true, "Greater than, within epsilon" );
        $this->assertEquals($this->cmp->gt(3.01, 3.02), false, "Less than, within epsilon" );

        $this->assertEquals($this->cmp->gt(3.001, 3.001), false,"Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->gt(3.002, 3.001), false, "Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->gt(3.001, 3.002), false, "Less than, more decimal digits" );

        $this->assertEquals($this->cmp->gt(-3.01, -3.01), false,"Negative Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->gt(-3.01, -3.02), true, "Negative Greater than, within epsilon" );
        $this->assertEquals($this->cmp->gt(-3.02, -3.01), false, "Negative Less than, within epsilon" );

        $this->assertEquals($this->cmp->gt(-3.001, -3.001), false,"Negative Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->gt(-3.001, -3.002), false, "Negative Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->gt(-3.002, -3.001), false, "Negative Less than, more decimal digits" );

        $this->assertEquals($this->cmp->gt("abc","abc"), false, "Equal strings" );
     }

    /**
     * IacComparatorTest::test_ge()
     * Tests ge
     *
     * @testdox ge: is first numbers or strings greater or eqaul than second,
     *
     * @covers IacComparator::ge
     * @covers IacComparator::cmp
     * @covers IacComparator::bccomp
     * @uses   IacComparator::strcmp
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @uses IacComparator::unaccentSetUp
     * @uses IacComparator::unaccent
     * @uses IacComparator::strcmp
     */
     public function test_ge() {
        $this->assertEquals($this->cmp->ge(3.01, 3.01), true,"Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->ge(3.02, 3.01), true, "Greater than, within epsilon" );
        $this->assertEquals($this->cmp->ge(3.01, 3.02), false, "Less than, within epsilon" );

        $this->assertEquals($this->cmp->ge(3.001, 3.001), true,"Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->ge(3.002, 3.001), true, "Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->ge(3.001, 3.002), true, "Less than, more decimal digits" );

        $this->assertEquals($this->cmp->ge(-3.01, -3.01), true,"Negative Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->ge(-3.01, -3.02), true, "Negative Greater than, within epsilon" );
        $this->assertEquals($this->cmp->ge(-3.02, -3.01), false, "Negative Less than, within epsilon" );

        $this->assertEquals($this->cmp->ge(-3.001, -3.001), true,"Negative Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->ge(-3.001, -3.002), true, "Negative Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->ge(-3.002, -3.001), true, "Negative Less than, more decimal digits" );

        $this->assertEquals($this->cmp->ge("abc","abc"), true, "Equal strings" );
     }

    /**
     * IacComparatorTest::test_lt()
     * Tests lt
     *
     * @testdox lt: is first numbers or strings less than second,
     *
     * @covers IacComparator::lt
     * @covers IacComparator::bccomp
     * @covers IacComparator::cmp
     * @uses   IacComparator::strcmp
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @uses IacComparator::unaccentSetUp
     * @uses IacComparator::unaccent
     * @uses IacComparator::strcmp
     */
     public function test_lt() {
        $this->assertEquals($this->cmp->lt(5, 5), false,"Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->lt(3.02, 3.01), false, "Greater than, within epsilon" );
        $this->assertEquals($this->cmp->lt(3.01, 3.02), true, "Less than, within epsilon" );

        $this->assertEquals($this->cmp->lt(3.001, 3.001), false,"Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->lt(3.002, 3.001), false, "Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->lt(3.001, 3.002), false, "Less than, more decimal digits" );

        $this->assertEquals($this->cmp->lt(-3.01, -3.01), false,"Negative Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->lt(-3.01, -3.02), false, "Negative Greater than, within epsilon" );
        $this->assertEquals($this->cmp->lt(-3.02, -3.01), true, "Negative Less than, within epsilon" );

        $this->assertEquals($this->cmp->lt(-3.001, -3.001), false,"Negative Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->lt(-3.001, -3.002), false, "Negative Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->lt(-3.002, -3.001), false, "Negative Less than, more decimal digits" );

        $this->assertEquals($this->cmp->lt("abc","abc"), false, "Equal strings" );
     }

    /**
     * IacComparatorTest::test_le()
     * Tests le
     *
     * @testdox le: is first numbers or strings less or eqaul than second,
     *
     * @covers IacComparator::le
     * @covers IacComparator::bccomp
     * @covers IacComparator::cmp
     * @uses   IacComparator::strcmp
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @uses IacComparator::unaccentSetUp
     * @uses IacComparator::unaccent
     * @uses IacComparator::strcmp
     */
     public function test_le() {
        $this->assertEquals($this->cmp->le(3.01, 3.01), true,"Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->le(3.02, 3.01), false, "Greater than, within epsilon" );
        $this->assertEquals($this->cmp->le(3.01, 3.02), true, "Less than, within epsilon" );

        $this->assertEquals($this->cmp->le(3.001, 3.001), true,"Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->le(3.002, 3.001), true, "Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->le(3.001, 3.002), true, "Less than, more decimal digits" );

        $this->assertEquals($this->cmp->le(-3.01, -3.01), true,"Negative Equal numbers, within epsilon" );
        $this->assertEquals($this->cmp->le(-3.01, -3.02), false, "Negative Greater than, within epsilon" );
        $this->assertEquals($this->cmp->le(-3.02, -3.01), true, "Negative Less than, within epsilon" );

        $this->assertEquals($this->cmp->le(-3.001, -3.001), true,"Negative Equal numbers, more decimal digits" );
        $this->assertEquals($this->cmp->le(-3.001, -3.002), true, "Negative Greater than, more decimal digits" );
        $this->assertEquals($this->cmp->le(-3.002, -3.001), true, "Negative Less than, more decimal digits" );

        $this->assertEquals($this->cmp->le("abc","abc"), true, "Equal strings" );

     }

    /**
     * IacComparatorTest::test_eqArrayIdentical()
     * Tests eqArrayIdentical & arrayIdentical
     *
     * @testdox eqArrayIdentical: Arrays have same keys and values, keys in the same position
     *
     * @covers IacComparator::eqArrayIdentical
     * @covers IacComparator::arrayIdentical
     *
     * @covers IacComparator::eq
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @uses IacComparator::unaccentSetUp
     * @uses IacComparator::unaccent
     * @uses IacComparator::strcmp
     */
     public function test_arrayIdentical() {
        $array1 = array("apple"=>"manzana","orange"=>"naranja","watermelon"=>"sandia");
        $array2 = array("apple"=>"manzana","watermelon"=>"sandia","orange"=>"naranja");

        $this->assertEquals($this->cmp->eqArrayIdentical($array1, $array1), true, "Equal arrays" );

        $this->cmp->arraySamePosition = false; // check it resets it
        $this->assertEquals($this->cmp->eqArrayIdentical($array1, $array2), false, "Equal arrays, diferent order" );
        $this->assertEquals($this->cmp->arraySamePosition, false, "Resets same position to previous value" );

        $this->assertEquals($this->cmp->eqArrayIdentical( array($array1,$array2,$array2), array($array1,$array2,$array2)), true, "Equal arrays" );
        $this->assertEquals($this->cmp->eqArrayIdentical( array($array1,$array2,$array2), array($array1,$array2,$array1)), false, "Equal arrays, diferent order subarray" );

     }

    /**
     * IacComparatorTest::test_eqArray()
     * Tests eqArray & arrayEqual
     *
     * @testdox eqArray: Arrays have same keys and values, keys in any position
     *
     * @covers IacComparator::eqArray
     * @covers IacComparator::arrayEqual
     *
     * @covers IacComparator::eq
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @uses IacComparator::unaccentSetUp
     * @uses IacComparator::unaccent
     * @uses IacComparator::strcmp
     */
     public function test_eqArray() {
        $array1 = array("apple"=>"manzana","orange"=>"naranja","watermelon"=>"sandia");
        $array2 = array("apple"=>"manzana","watermelon"=>"sandia","orange"=>"naranja");

        $this->assertEquals($this->cmp->eqArray($array1, $array1), true, "Equal arrays" );

        $this->cmp->arraySamePosition = true; // check it resets it
        $this->assertEquals($this->cmp->eqArray($array1, $array2), true, "Equal arrays, diferent order" );
        $this->assertEquals($this->cmp->arraySamePosition, true, "Resets same position to previous value" );

        $this->assertEquals($this->cmp->eqArray( array($array1,$array2,$array2), array($array1,$array2,$array2)), true, "Equal arrays" );
        $this->assertEquals($this->cmp->eqArray( array($array1,$array2,$array2), array($array1,$array2,$array1)), true, "Equal arrays, diferent order subarray" );
     }

    /**
     * IacComparatorTest::test_arrays_have_same_keys()
     * Tests arrays_have_same_keys
     *
     * @testdox arrays_have_same_keys: Arrays have same keys and values, keys in any position
     *
     * @dataProvider arrays_have_same_keysProvider
     *
     * @covers IacComparator::arrays_have_same_keys
     * @covers IacComparator::arrays_identical_keys
     * @uses IacComparator::__construct
     * @uses IacComparator::decimals_set
     * @uses IacComparator::unaccentSetUp
     */
    public function test_arrays_have_same_keys($obj1, $obj2, $firstLevelOnly, $SamePosition, $expected) {
        $this->cmp->arraySamePosition = $SamePosition;
        $this->assertEquals($this->cmp->arrays_have_same_keys($obj1, $obj2, $firstLevelOnly), $expected );
    }

        public function arrays_have_same_keysProvider() {
            return array(
               '1 Same numeric keys' => array( array(1,2,3,4),array(1,2,3,5)
                    ,false ,false
                    ,true  ),
                '1.identical Same numeric keys, same position' => array( array(1,2,3,4),array(1,2,3,5)
                    ,false ,true
                    ,true  ),

                '2 Asociative arrays diferent keys' => array(
                    array('a'=>1,'b'=>2,'xc'=>3), array('a'=>1,'b'=>2,'c'=>3)
                    ,false ,false
                    ,false ),

                '2.identical Asociative arrays diferent keys' => array(
                    array('a'=>1,'b'=>2,'c'=>3), array('a'=>1,'xb'=>2,'c'=>3)
                    ,false ,true
                    ,false ),

                '3 Asociative arrays same key  diferent order dont care order of keys' => array(
                    array('a'=>1,'c'=>3,'b'=>2), array('a'=>1,'b'=>2,'c'=>3)
                    ,false ,false
                    ,true ),

                '3.identical Asociative arrays same key  diferent order  care order of keys' => array(
                    array('a'=>1,'c'=>3,'b'=>2), array('a'=>1,'b'=>2,'c'=>3)
                    ,false ,true
                    ,false ),

                '4 Asociative arrays same subarrays, dont care order of keys' => array(
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    false ,false
                    ,true ),

                '4.identical Asociative arrays same subarrays, care order of keys' => array(
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    false ,true
                    ,true ),

                '5 First level only Asociative arrays same subarrays, dont care order of keys' => array(
                    array('a'=>array(1,2,3,"b"=>array("a"=>"A","b"=>"B"),4)),
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    true ,false
                    ,true ),

                '5.identical First level only Asociative arrays same subarrays, care order of keys' => array(
                    array('a'=>array(1,2,3,"b"=>array("a"=>"A","b"=>"B"),4)),
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    true ,true
                    ,true ),

                '6 Asociative arrays subarray different key order, dont care order of keys' => array(
                    array('a'=>array(1,2,3,"b"=>array("a"=>"A","b"=>"B"),4)),
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    false ,false
                    ,true ),

                '6.identical Asociative arrays subarray different key order,  care order of keys' => array(
                    array('a'=>array(1,2,3,"b"=>array("a"=>"A","b"=>"B"),4)),
                    array('a'=>array(1,2,3,4,"b"=>array("a"=>"A","b"=>"B"))),
                    false ,true
                    ,false ),

            );
        }
}
?>