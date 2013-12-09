<?php

class TW_JsonForma extends TW_JsonSchema
{

	private $html_form = '';
	
	public $forma_fields = array();
	
	public $fields = array();
	
	
	public function render() {
			
		/** assumes 'form' property exists in JSON Forma object */
		foreach( $this->json->form as $field_meta ) {
			
			$field_path = $this->setFieldPath( $field_meta );
			
			$f = $this->getFieldData( $field_path , $this->json );
			
			if ( is_object( $f ) ) {
				
				if ( $this->hasProperties( $f ) ) {
					
					$f_iter = $this->setupIterator( $f->properties );
					
					foreach( $f_iter as $k => $v ) {
					
						if ( $this->isField( $v ) ) {
						
							$field_loc = $this->setFieldPath( $field_path , $k , $f_iter->getDepth() , $f_iter );
							
							$this->setField( $field_loc , $this->fields , $k , $v  );
						}
					}
					
				} else {
					
					$field = array( "name" => $field_path[ count($field_path)-1 ] , "def" => $f , "params" => $field_meta );
					
					var_dump($field);
					
					$field_html = $this->getField( $field );
					
					echo $field_html;
					
					$this->setField( $field_path , $this->fields , $field_path[ count($field_path)-1 ] , $f  );
				}
				
			} else {
				
				echo '<p>Field is not an object.</p>';
			}
		}
	}
	
	
	private function getField( $field ) {
		
		$f_html = '';
	
		/** create a isFieldDef() function to determine that all parts are present */
	
		$field_type = $this->getField_type( $field );
		
		switch( $field_type ) {
		
			case 'text':
				$f_html = $this->getField_text( $field );
				break;
			
			case 'checkbox':
				$f_html = '<p>Render checkboxes</p>';
				break;
				
			case 'select':
				$f_html = '<p>Render select field</p>';
				break;
			
			default:
				$f_html = '<p>I don\'t understand this field type.</p>';
		}
				
		return $f_html;
	}

	
	private function getField_type( $field ) {
	
		$field_type = '';
		
		if ( property_exists( $field['params'] , 'type' ) ) {
			
			/** include error-checking to ensure type valid */
			$field_type = $field['params']->type;
			
		} else {
			
			if ( property_exists( $field['def'] , 'type' ) ) {
			
				$type = $field['def']->type;
				
				if ( $type == 'string' || $type == 'number' || $type == 'integer' ) {
					
					if ( property_exists( $field['def'] , 'enum' ) ) {
						
						$field_type = 'select';
						
					} else {
					
						$field_type = 'text';
					}
					
				} elseif ( $type == 'boolean' || $type == 'array' ) {
					
					$field_type = 'checkbox';
				}
			}
		}
		
		if ( empty( $field_type ) ) {
			
			$field_type = '<p>Field type could not be determined.</p>';
		}
		
		return $field_type;
	}
	
	
	private function getField_text( $field ) {
	
		$html = array();
		
		if ( $this->showLabel( $field['params'] ) ) {
			
			$html['label'] = $this->getField_label( $field );
		}
		
		return '<p>Render text field</p>';
	}
	
	
	private function showLabel( $f_params ) {
	
		if ( property_exists( $f_params , 'label' ) ) {
			
			if ( !$f_params->label ) {
				
				return false;
			}
		}
		
		return true;
	}
	
	
	private function getField_label( $field ) {
	
		$html = '';
		
		return $html;
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
	
	
	private function setFieldPath( $f_meta , $field_key = '' , $depth = 0 , $json = '{}'  ) {
		
		if ( is_object( $f_meta ) ) {
					
			if ( property_exists( $f_meta , 'key' ) ) {
				
				$f_path = explode( '.' , $f_meta->key );
			}
			
		} elseif ( is_string( $f_meta ) ) {
		
			if ( $f_meta === '*' ) {
				
				$f_path = array();
				
			} else {
			
				$f_path = explode( '.' , $f_meta );
			}
			
		} elseif ( is_array( $f_meta ) ) {
			
			$f_path = $f_meta;
			
			$d = 0;
			do {
			
				if ( $d ) {
					$d = $d + 1;
				}
				
				$f_path[] = $json->getSubIterator( $d )->key();
				$d = $d + 1;
				
			} while ( $d < $depth );
			
			if ( $f_path[ count( $f_path ) - 1 ] !== $field_key ) {
				
				$f_path[] = $field_key;
			}
		
		} else {
			
			return false;
		}
		
		return $f_path;
	}
	
	
	private function getFieldData( $field_path , $json_s ) {
		
		if ( $this->hasSchema( $json_s ) ) {
		
			$f_data = $json_s->schema;
			
			// return this is path is equal to *
			
			foreach( $field_path as $pointer ) {
				
				if ( $this->hasProperties( $f_data ) ) {
					
					$f_data = $f_data->properties->$pointer;
				}
			}
			
		} else {
			
			echo '<p>Can\'t find schema object.</p>';
		}
		
		return $f_data;
	}
}

?>