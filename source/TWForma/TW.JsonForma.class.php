<?php

class TW_JsonForma extends TW_JsonSchema
{

	private $html_form = '';
	
	public $forma_fields = array();
	
	public $fields = array();
	
	public function render() {
	
		$field_path = array();
		
		if ( property_exists( $this->json , 'schema' )   ) {
		
			if ( property_exists( $this->json->schema , 'properties' ) ) {
				
				$forma_fields = $this->json->schema->properties;
				
				$forma_fields = new RecursiveArrayIterator( $forma_fields );
				
				$forma_iter = new RecursiveIteratorIterator( $forma_fields , 1 );
				
				foreach( $forma_iter as $k => $v ) {
				
					$depth = $forma_iter->getDepth();
				
					unset($field_path);
					
					$local_fields =& $this->fields;
					
					if ( is_object($v) && property_exists( $v , 'type' ) ) {
						
						if ( $v->type !== 'object' ) {
						
							if ( $depth ) {
								
								$d = $depth - 1;
								
							} else {
								
								//echo '<p>Depth equaled 0.</p>';
								$d = $depth;
								
							}
							
							//var_dump($d);
							
							do {
							
								if ( $d ) {
									
									$d = $d - 1;
									
								}
								//var_dump($d);
								$field_path[] = $forma_iter->getSubIterator($d)->key();
								$d = $d - 1;
								//var_dump($d);
								
							} while ( $d > 0 );
							
							//$a = 'field_path';
							
							echo '<p>The current depth is: ' . $depth . '</p>';
							echo 'The field path is: ';
							$field_path = array_reverse( $field_path );
							var_dump($field_path);
							
							foreach ( $field_path as $path ) {
								
								if ( !array_key_exists( $path , $local_fields ) ) {
									
									$local_fields[ $path ] = array();
									
								}
								
								$local_fields = $local_fields[ $path ];
								
							} 
							
							var_dump($k , $v);
							
						}
					}
				}
				
			} else {
				
				echo '<p>The JSON Schema doesn\'t have any properties.</p>';
				
			}
			
		} else {
			
			echo '<p>JSON Schema doesn\'t have a schema object.</p>';
			
		}
		
	}
	
	
	public static function triageType( $action , $json ) {
	
		echo '<div style="background-color : lime ; padding : 10px;"><p>Triaging Form</p>';
		var_dump( $json );
		echo '</div>';
		
		$demo = new TW_JsonExtended( $json , 'object' );
		
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
				
				$test = new TW_JsonExtended( $json['forma']['properties'] , 'object' );
				
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