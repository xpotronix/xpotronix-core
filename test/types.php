<?php
// PHP program to illustrate gettype() function
  
$var1 = true; // boolean value 
$var2 = 3; // integer value
$var3 = 5.6; // double value
$var4 = "Abc3462"; // string value
$var5 = array(1, 2, 3); // array value
$var6 = new stdClass; // object value
$var7 = NULL; // null value
$var8 = tmpfile(); // resource value
  
echo gettype($var1)."\n";
echo gettype($var2)."\n";
echo gettype($var3)."\n";
echo gettype($var4)."\n";
echo gettype($var5)."\n";
echo gettype($var6)."\n";
echo gettype($var7)."\n";
echo gettype($var8)."\n";
  
?>
