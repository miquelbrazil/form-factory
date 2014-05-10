<?php

class TW_JsonRefIteratorIterator extends RecursiveIteratorIterator
{

	public function __construct ( $iterator , $mode ) {
		
		parent::__construct( $iterator , $mode );
		
	}

	public function current() {
		
		$current = parent::current();
		$key = parent::key();
		
		$depth = $this->getDepth();
		
		if ( $key == 'description' && $current == 'the full name of the Client as two seperate variables' ) {
			
			echo 'Do Something.<br />';
			
			
			
			//var_dump($depth);
			
			var_dump( $this->getSubIterator($depth) );
			
			$arraySub = new ArrayObject( $this->getSubIterator($depth) );
			
			$arraySub->offsetSet( 'title' , 'Miquel' );
			
			$current = $this->getInnerIterator()->offsetGet( $key );
			
			var_dump( $this->getSubIterator($depth) );
			
			//var_dump( $this->getSubIterator($depth) );
			
			//$current = 'test';
			
			//var_dump($current);
			
		}
		
		return $current;
		
	}
	
	public function endIteration() {
		
		//var_dump( iterator_to_array($this) );
		
	}

}

?>