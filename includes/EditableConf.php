<?php

class EditableConf {
	private $default;
	private $newValue;
	private $type;
	private $name;
	private $text;

	// Special type 'textarea' exists. It's the same as string but will
	// render as textarea in the form
	public function __construct( $name, $default, $text, $type = null ) {
		if ( !$type ) {
			$type = gettype( $default );
		}

		$this->name = $name;
		$this->default = $default;
		$this->text = $text;
		$this->type = $type;
		$this->newValue = $default;
	}

	public function setNewValue( $newValue ) {
		$type = $this->type == 'textarea' ? 'string' : $this->type;
		settype( $newValue , $type );
		$this->newValue = $newValue;
	}

	public function isDefault() {
		return $this->default === $this->newValue;
	}

	public function exportDefault() {
		$GLOBALS[$this->name] = $this->default;
	}

	public function exportNewValue() {
		$GLOBALS[$this->name] = $this->newValue;
	}

	public function getName() {
		return $this->name;
	}

	public function getType() {
		return $this->type;
	}

	public function getDefault() {
		return $this->default;
	}

	public function getText() {
		return $this->text;
	}

	public function getNewValue() {
		return $this->newValue;
	}
}
