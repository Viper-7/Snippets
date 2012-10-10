<?php
class CMS7_Attribute
{
	public function __toString()
	{
		if(isset($this->value))
			return $this->value;
	}
}