<?php

class RsvpItem{
	public $name = "";
	public $prename = "";
	public $attending = false;
	public $vegitarian = false;
	public $comment = "";

	function RsvpItem(){
		$this->name = "";
		$this->prename = "";
		$this->attending = false;
		$this->vegitarian = false;
		$this->comment = "";
	}

	public function isAttending(){
		return ($this->attending == "yes") ?  1 :  0;
	}

	public function isVegitarian(){
		return ($this->vegitarian == "yes") ? 1 :  0;
	}

	public function printit(){
		var_dump($this);
	}

	public function isValid(){
		if(empty($this->name) || empty($this->prename)){
			return false;
		}
		return true;
	}

}



?>