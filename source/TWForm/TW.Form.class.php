<?php

class TW_Form
{

	private static $singleton = null;
	
	private $json_form;
	private static $html_form = '';
	
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
		
		echo self::$html_form;
		
	}
	
	
	public static function triageType( $action , $json ) {
	
		echo '<div style="background-color : lime ; padding : 10px;"><p>Triaging Form</p>';
		var_dump( $json );
		echo '</div>';
		
		$demo = new TW_JsonExtended( $json );
		
		var_dump($demo->json);
		
		$action = array( 
			'search' => array( 'method' => 'value' , 'lookup' => 'type' ),
			'callback' => array( 'TW_Form' , 'handleFieldType' )
		);
		
		$demo->iterate( $action );
		
	}
	
	public static function handleFieldType( $action , $json ) {
		
		echo 'Do something!';
		var_dump($json);
		
		switch ( $json['type'] ) {
			
			case 'object':
				echo 'Rendering a fieldset.<br />';
				
				if ( empty( self::$html_form ) ) {
					
					self::$html_form = '<fieldset><legend>' . $json['forma']['title'] . '</legend>';    // assume fieldset is being created for the first time.
					
				} else {
					
					self::$html_form .= '</fieldset><fieldset><legend>' . $json['forma']['title'] . '</legend>';
					
				}
				
				$test = new TW_JsonExtended( $json['forma']['properties'] );
				
				$object_action = array( 
					'search' => array( 'method' => 'value' , 'lookup' => 'type' ),
					'callback' => array( 'TW_Form' , 'handleFieldType' )
				);
				
				$test->iterate( $object_action );
				
				break;
			
			case 'string':
				echo 'Rendering a text field.<br />';
				
				self::$html_form .= $json['forma']['title'] . ': <input type="text" id="" name="" placeholder="" value="" /><br />';
				
				break;
				
			default:
				echo 'I haven\'t learned how to deal with this field type yet<br />';
		}
		
	}

}

?>