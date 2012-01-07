<?php

class DateClass extends DateTime{

	public function getTimestamp(){
		return $this->format ("U");
	}

	/**
	*    This function calculates the number of days between the first and the second date. Arguments must be subclasses of DateTime
	**/

	static function differenceInDays ($firstDate, $secondDate){

		return round ( ( ( $firstDate->format("U") - $secondDate->format("U") ) ) / 86400 );
	}

	/**
	* This function returns an object of DateClass from $date in format $format. See date() for possible values for $format
	**/

	static function createFromFormat( $format, $date = null ) {

    		if( ! $date ) 
	        	return new DateClass();

		$masks = array(

			'Y' => '(?P<Y>19\d\d|20\d\d)',        
			'y' => '(?P<Y>\d\d)',        
			'm' => '(?P<m>0?[1-9]|1[012])',
			'd' => '(?P<d>0?[1-9]|[12][0-9]|3[01])',
			'-' => '[-]',
			'.' => '[\. /.]',
			':' => '[:]?',            
			' ' => '[\s]',
			'H' => '(?P<H>0[0-9]|1[0-9]|2[0-3])',
			'i' => '(?P<i>[0-5][0-9])?',
			's' => '(?P<s>[0-5][0-9])?'
		);


		$regexp = "#".strtr( $format, $masks )."#"; 
		preg_match($regexp, $date, $result);


		if ( !count( $result ) )
			return null;

		$initString = sprintf( "%s-%s-%s %s:%s:%s",
			$result['Y'],
			str_pad( $result['m'], 2, '0', STR_PAD_LEFT ),
			str_pad( $result['d'], 2, '0', STR_PAD_LEFT ),
			isset( $result['H'] ) ? $result['H'] : '00',
			isset( $result['i'] ) ? $result['i'] : '00',
			isset( $result['s'] ) ? $result['s'] : '00' );

		/*
		echo '<pre>';
		print $format."\n";
		print $date."\n";
		print $regexp."\n";
		print_r( $result );
		print $initString;
		exit;
		*/

		try {
    			return new DateClass ($initString);

		} catch( Exception $e ) {

	    		return null;
		}

	}    
}
?>
