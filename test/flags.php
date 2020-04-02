<?php

include( "../constants.inc.php" );


print DS_ANY . "\n";
print DS_NORMALIZED . "\n";
print DS_RECURSIVE . "\n";
print DS_BLANK . "\n";
print DS_DEFAULTS . "\n";

$flags = DS_ANY|DS_NORMALIZED|DS_RECURSIVE|DS_BLANK|DS_DEFAULTS;

print( "antes: $flags\n" );

$flags = $flags ^ DS_BLANK;

print( "despues: $flags\n" );





?>
