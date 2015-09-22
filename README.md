# IacComparator

> IacComparator.php

Compares 2 variables / objects

* strings: case/accent [in]sensitive,  natural or computer ordering
* numbers; within $decimals: eq ==,  gt >,  ge >=,  lt <,  le <=
* arrays: same keys,  same key=>values,  same key=>values in same position
* object: ==

# Usage
```
$cmp = new IacComparator(
    2,  // $decmails: number of decimals to consider when comparing numbers
    false,  // $caseSensitiveStrings: case sensitive when comparing strings
    false,  // $accentSensitiveStrings: case sensitive when comparing strings
    true,   // $naturalSortingStrings: natural string comparison (strnatcmp) ture,  or standard compare (strcmp)
    false // $arraySamePosition: comparing arrays the keys should bee in the same position: true,  on false key in any position
);

$cmp->eq($a, $b); // are $a,  $b equal considerint
$cmp->gt($a, $b); // $a Greater than $b ?
$cmp->ge($a, $b); // $a Greater or equal than $b ?
$cmp->lt($a, $b); // $a less than $b ?
$cmp->le($a, $b); // $a less or equal than $b ?

$cmp->cmp($a, $b);
    // returns -1,  0,  1
    // If numeric arguments consider decimals in comparison,
    // if strings case/accent sensitivity and natrual/normal

$cmp->strcmp($a, $b);
    // returns -1,  0,  1 case/accent sensitivity and natrual/normal

$cmp->eqArray($array1, $array2);
    // Both arrays have same key=>values,  recursively,  keys may be in any position in the arrays

$cmp->eqArrayIdentical($array1, $array2);
    // Both arrays have same key=>values,  recursively,  keys may be in the same position in both arrays

$cmp->arrays_have_same_keys($array1, $array2, false)
    // Both arrays have same keys,  recursively,
    // keys may be in any position in the arrays if $cmp->arraySamePosition=false;
    // keys may be in the same position in both arrays if $cmp->arraySamePosition=true;

$cmp->arrays_have_same_keys($array1, $array2, true)
    // Both arrays have same keys,  only check the first level of the array,  exclude 'sub-arrays'
    // keys may be in any position in the arrays if $cmp->arraySamePosition=false;
    // keys may be in the same position in both arrays if $cmp->arraySamePosition=true;
```

* Array keys are always compared case and accent sesitive
* See docs,  for more details
* Recomends bcMath extension

# License: MIT

