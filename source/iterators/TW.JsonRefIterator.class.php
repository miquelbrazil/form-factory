<?php

class TW_JsonRefIterator extends RecursiveArrayIterator
{

	public function __construct( $json ) {
		
		parent::__construct( $json );
		
	}

}

?>