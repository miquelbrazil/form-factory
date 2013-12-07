<?php

class TW_JsonForma extends TW_JsonSchema
{

	private $html_form = '';
	
	public $forma_fields = array();
	
	public $fields = array();
	
	
	public function render() {
		
		if ( property_exists( $this->json , 'schema' ) ) {
		
			if ( property_exists( $this->json->schema , 'properties' ) ) {
			
				$forma_iter = $this->setupIterator( $this->json->schema->properties );
				
				foreach( $forma_iter as $k => $v ) {
				
					if ( is_object($v) && property_exists( $v , 'type' ) ) {
						
						if ( $v->type !== 'object' ) {
						
							// set fieldpath variable
							
							$field_path = $this->buildFieldPath( $forma_iter->getDepth() , $forma_iter );
							
							// render field based on $v
							
							$this->setField( $field_path , $this->fields , $k , $v  );
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