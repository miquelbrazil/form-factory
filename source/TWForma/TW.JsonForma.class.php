<?php

class TW_JsonForma extends TW_JsonSchema
{

	private $html_form = '';
	
	public $forma_fields = array();
	
	public $fields = array();
	
	
	public function render() {
	
		$fields_p = $this->getField_paths( $this->json->form->fields );
		
		$fields_l = $this->getField_limits( $fields_p );
			
		/** assumes 'form' property exists in JSON Forma object */
		foreach( $this->json->form->fields as $field_meta ) {
		
			$field_id = $this->getField_id( $field_meta );
			
			$field_path_def = $fields_p[ $field_id ]['def'];
			
			if ( empty( $fields_p[ $field_id ]['dest'] ) ) {
				
				$field_path_dest = $field_path_def;
				
			} else {
				
				$field_path_dest = $fields_p[ $field_id ]['dest'];
			}
			
			$f = $this->getFieldData( $field_path_def , $this->json );
			
			if ( is_object( $f ) ) {
				
				if ( $this->hasProperties( $f ) ) {
					
					$f_iter = $this->setupIterator( $f->properties );
					
					foreach( $f_iter as $k => $v ) {
					
						if ( $this->isField( $v ) ) {
						
							$field_loc = $this->setFieldPath( $field_path_dest , $k , $f_iter->getDepth() , $f_iter );
							
							if ( !in_array( implode( '.' , $field_loc) , $fields_l ) ) {
							
								$field = array( "name" => $k , "def" => $v , "params" => $field_meta );
								
								$field_html = $this->getField( $field );
								
								$this->setField( $field_loc , $this->fields , $k , $field_html  );
							}
						}
					}
					
				} else {
					
					$field = array( "name" => $field_path_def[ count($field_path_def)-1 ] , "def" => $f , "params" => $field_meta );
					
					$field_html = $this->getField( $field );
					
					$this->setField( $field_path_dest , $this->fields , $field_path_def[ count($field_path_def)-1 ] , $field_html  );
				}
				
			} else {
				
				echo '<p>Field should be an object.</p>';
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
	
	
	private function getField_paths( $fields ) {
		
		$field_paths = array();
		
		foreach( $fields as $field ) {
			
			$key = $this->getField_id( $field );
			
			$definition_path = $this->setFieldPath( $field );
			
			/*$c = count( $path );
			
			if ( $c == 1 ) {
				
				$key = $path[0];
				
			} elseif ( $c > 1 ) {
				
				$key = $path[ $c - 1 ];
			}*/
			
			$field_paths[ $key ][ 'def' ] = $definition_path;
			
			if ( is_object( $field ) ) {
				
				if ( property_exists( $field , 'regroup' ) ) {
					
					$destination_path = $this->setFieldPath( $field->regroup );
					
					if ( $destination_path ) {
						
						$field_paths[ $key ][ 'dest' ] = $destination_path;
					}
				} else {
					
					$field_paths[ $key ][ 'dest' ] = array();
				}
				
			} else {
				
				$field_paths[ $key ][ 'dest' ] = array();
			}
		}
		
		return $field_paths;
	}
	
	
	private function getField_limits( $field_paths ) {
		
		$field_limits = array();
		
		foreach ( $field_paths as $path ) {
			
			$c = count( $path['def'] );
			
			if ( $c > 1 ) {
				
				$field_limits[] = implode( '.' , $path['def'] );
			}
		}
		
		return $field_limits;
	}
	
	
	/** get the name/id of the field to find it's path */
	private function getField_id ( $field ) {
		
		$f_id = '';
		
		if ( is_object( $field ) ) {
					
			if ( property_exists( $field , 'key' ) ) {
				
				$f_id = $field->key;
			}
			
		} elseif ( is_string( $field ) ) {
		
			if ( $field === '*' ) {
				
				$f_id = 'all';
				
			} else {
			
				$f_id = $field;
			}
			
		}
		
		return $f_id;		
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
		
		$id = $field['name'];
		
		if ( $this->showLabel( $field['params'] ) ) {
			
			$html['label'] = $this->getField_label( $field );
		}
		
		$html['field'] = '<input type="text" id="' . $id . '" name="' . $id . '" />';
		
		return implode( $html );
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
		
		$for_id = $field['name'];
		
		if ( property_exists( $field['def'] , 'title' ) ) {
			
			$label = $field['def']->title;
			
		} else {
			
			$label = $field['name'];
		}
		
		
		$html = '<label for="' . $for_id . '">' . $label . '</label>';
		
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