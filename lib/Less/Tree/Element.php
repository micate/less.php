<?php

//less.js : lib/less/tree/element.js

class Less_Tree_Element extends Less_Tree{

	public $combinator;
	public $value = '';
	public $index;
	public $currentFileInfo;
	public $type = 'Element';

	public $value_is_object = false;

	static $_outputMap = array(
		''  => '',
		' ' => ' ',
		':' => ' :',
		'+' => ' + ',
		'~' => ' ~ ',
		'>' => ' > ',
		'|' => '|',
        '^' => ' ^ ',
        '^^' => ' ^^ '
	);


	public function __construct($combinator, $value, $index = null, $currentFileInfo = null ){

		$this->value = $value;
		$this->value_is_object = is_object($value);

		$this->combinator = $combinator;
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
	}

	function accept( $visitor ){
		if( $this->value_is_object ){ //object or string
			$this->value = $visitor->visitObj( $this->value );
		}
	}

	public function compile($env){

		if( !$this->value_is_object ){
			return $this;
		}

		return new Less_Tree_Element($this->combinator, $this->value->compile($env), $this->index, $this->currentFileInfo );
	}

    /**
     * @see Less_Tree::genCSS
     */
	public function genCSS( $output ){
		$output->add( $this->toCSS(), $this->currentFileInfo, $this->index );
	}

	public function toCSS(){

		if( $this->value_is_object ){
			$value = $this->value->toCSS();
		}else{
			$value = $this->value;
		}


		if( $value === '' && $this->combinator && $this->combinator === '&' ){
			return '';
		}

		return Less_Tree_Element::$_outputMap[$this->combinator] . $value;
	}

}
