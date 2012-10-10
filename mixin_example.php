<?php
/**
* Example code for http://home.viper-7.com/trac/browser/PHP/mixin.php
* 
* @Author Dale Horton
* @Date 2009-12-20
*
* ----------------------------------------------------------------------------
* "THE BEER-WARE LICENSE" (Revision 42):
* <viper7@viper-7.com> wrote this file. As long as you retain this notice you
* can do whatever you want with this stuff. If we meet some day, and you think
* this stuff is worth it, you can buy me a beer in return.   Dale Horton
* ----------------------------------------------------------------------------
**/

include('mixin.php');

class Lifeform implements Mixable
{
	public $birth_date;
	public $living = TRUE;
	
	public function __construct()
	{
		$this->birth_date = time();
	}
	
	public function kill()
	{
		$this->living = FALSE;
	}
}

class Animal implements Mixable
{
	public $gender;
	
	public function __construct($gender)
	{
		$this->gender = $gender;
	}
	
	public function walk($legs)
	{
		echo "I'm walking on $legs legs<br/>";
	}
}

class Dog implements Mixable
{
	public $breed;
	public $legs = 4;
	
	public function bark()
	{
		echo 'Woof!<br/>';
	}
	
	public function walk()
	{
		// Look for a walk() method in this object's parent classes
		// (in this case, it would be Animal's walk() method), call it, 
		// and pass it the number of legs for this dog
		
		$mixin = Mixin::getMixin($this);
		$mixin->parentCall($this, 'walk', $this->legs);
	}
}

// Create our mixed class
class My_Dog extends Mixin {}

// Instantiate it
$my_dog = new My_Dog();

// Instantiate a new Lifeform object and mix it into the $my_dog instance
$my_dog->inherit('Lifeform');

// Instantiate a new Animal object passing the gender to the constructor and mix it into the $my_dog instance
$my_dog->inherit('Animal', 'Male');

// Instantiate a new Animal object and mix it into the $my_dog instance, overriding Animal's walk() method with a more specific version
$my_dog->inherit('Dog');

// Output the birth date (from the Lifeform class)
echo 'Birth Date: ' . date('r', $my_dog->birth_date) . '<br/>';

// Output the gender (from the Animal class)
echo 'Gender: ' . $my_dog->gender . '<br/>';

// Call the bark() method (from the Dog class)
$my_dog->bark();

// Call the walk() method (from the Dog class)
$my_dog->walk();

// Output the current status (from the Lifeform class)
echo 'Status: ' . ($my_dog->living?'Alive':'Dead') . '<br/>';

// Update the current status (from the Lifeform class)
echo 'Calling kill()<br/>';
$my_dog->kill();

// Output the current status (from the Lifeform class)
echo 'Status: ' . ($my_dog->living?'Alive':'Dead') . '<br/>';
