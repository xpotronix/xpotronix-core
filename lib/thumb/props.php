<?

/**
 * @package xpotronix
 * @version 2.0 - Areco 
 * @copyright Copyright &copy; 2003-2011, Eduardo Spotorno
 * @author Eduardo Spotorno
 *
 * Licensed under GPL v3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

include_once '../../xpthumb.class.php';

$x = new xpthumb;

$x->load();


foreach ( $x->props() as $name => $property ) {
    echo "{$name} => {$property}<br />\n";
}

?>
