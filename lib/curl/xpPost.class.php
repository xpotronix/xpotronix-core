<?

for( $i = 0; $i < 100; $i++ ) {
	
	print "Post # $i, ";
	post_test();

}

function post_test() {

$your_username="username";
$your_password="password";

$params = array();

$params['m'] = 'dtActorTipo';
$params['a'] = 'store';
	$params['x'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<container name="dtActorTipo">
<dtActorTipo uiid="ext-record-487" ID="1" new="0">
<aro_group_id>11</aro_group_id>
</dtActorTipo>
<dtActorTipo uiid="ext-record-488" ID="10" new="0">
<container name="dtActor"/>
<container name="dtActorSubTipo"/>
<container name="dtProcesoActor"/>
</dtActorTipo>
</container>';

$headers  =  array( "application/xhtml+xml" );

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/jurisbook/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 4);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_USERPWD, $your_username.':'.$your_password);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $params ));

$data = curl_exec($ch);

if (curl_errno($ch)) print curl_error($ch);
else curl_close($ch);

// echo $data;


}


?>

