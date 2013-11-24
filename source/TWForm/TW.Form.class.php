<?php

class TW_Form
{

	private static $singleton = null;
	
	private $json_form;
	private $html_form;
	
	private function __construct() {
		
	}
	
	
	public static function getSingleton() {
		
		if ( !isset( self::$singleton ) ) {
			
			self::$singleton = new TW_Form();
		}
		
		return self::$singleton;
	}
	
	
	public function renderForm( $forma ) {
		
		$forma = new TW_JsonExtended( $forma );
		
		$action = array( 
			'search' => array( 'method' => 'key' , 'lookup' => 'schema' ),
			'callback' => array( 'TW_Form' , 'triageType' )
		);
		
		$forma->iterate( $action );
		
	}
	
	
	public static function triageType( $action , $json ) {
	
		echo '<div style="background-color : lime ; padding : 10px;"><p>Triaging Form</p>';
		var_dump( $json );
		echo '</div>';
		
	}

}

?>