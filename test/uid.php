<?

for ( $i = 0; $i < 1000; $i++ ) {


	print sha1(microtime(true).mt_rand(10000,90000))."\n";
	// print get_key()."\n";

}


function get_key($bit_length = 128){
    $fp = @fopen('/dev/random','rb');
    if ($fp !== FALSE) {
        $key = substr(base64_encode(@fread($fp,($bit_length + 7) / 8)), 0, (($bit_length + 5) / 6)  - 2);
        @fclose($fp);
        return $key;
    }
    return null;
}
?>
