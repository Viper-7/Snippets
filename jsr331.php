<?php
/*
Problem
Var	// int
VarBool // bol
VarReal // float
VarSet  // assign
Constraint
ProblemFactory

ConstraintLinear
ConstraintAlLDiff
ConstraintElement
ConstraintCardinality
ConstraintGlobalCardinality
ConstraintIfThen
ConstraintMax
ConstraintMin


*/
$int1 = new VarInt();
$int1->setValue(range(1,10));

$int2 = new VarInt();
$int2->setValue(range(1,10));

$res = $int1->plus($int2);
var_dump($res->getValue());

$p = new Problem();
$a = $p->variable('A', 1, 10);
$b = $p->variable('B', 1, 10);
$c = $a->plus($b);
$p->postAllDiff($c);
$p->post($c, "<", 15);



class DomainType {
	const DOMAIN_SMALL = 1;
	const DOMAIN_MIN_MAX = 2;
	const DOMAIN_SPARSE = 3;
	const DOMAIN_OTHER = 4;
}
abstract class Constraint {

}
class AllDifferentConstraint extends Constraint {
	
}

abstract class ConstrainedVariable {
	protected $problem;
	protected $name;
	protected $value;
	protected $tagobj;
	protected $domainType;
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setProblem($problem) {
		$this->problem = $problem;
	}
	
	public function getProblem() {
		return $this->problem;
	}
	
	public function setImpl($impl) {
	
	}
	
	public function getImpl() {
	
	}
	
	public function setObject($obj) {
		$this->tagobj = $obj;
	}
	
	public function getObject() {
		return $this->tagobj;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setDomainType($type) {
		$this->domainType = $type;
	}
	
	public function getDomainSize() {
		return count($this->value);
	}
}

class VarInt extends ConstrainedVariable {
	public function plus($value) {
		if(!is_object($value))
			$value = new VarInt($value);
		
		$vals = array();
		foreach($this->value as $left) {
			foreach($value->value as $right) {
				$vals[] = $left + $right;
			}
		}
		
		$var = new VarInt();
		$var->setProblem($this->problem);
		$var->setDomainType(DomainType::DOMAIN_SPARSE);
		$var->setValue($vals);
		
		return $var;
	}
	
	public function minus($value) {
	
	}
	public function multiply($value) {
	
	}
	public function divide($value) {
	
	}
	public function mod($value) {
	
	}
	public function sqr() {
	
	}
	public function power($value) {
	
	}
	public function abs() {
	
	}
}
class VarBool extends ConstrainedVariable {

}
class VarReal extends ConstrainedVariable {

}
class VarSet extends ConstrainedVariable {

}
class Problem {
	protected $domainType = DomainType::DOMAIN_SMALL;
	protected $vars = array();
	
	public function variable($name) {
		$args = func_get_args();
		
		$var = new VarInt();
		$var->setProbem($this);
		$var->setName($name);
		if(is_array($args[1])) {
			$var->setValue($args[1]);
		} else {
			$var->setValue(range($args[1], $args[2]));
			if(isset($args[3]))
				$var->setDomainType($args[3]);
		}
		
		$this->add($var);
	}
	
	public function variableArray($name, $min, $max, $size) {
		foreach(range(1, $size) as $i) {
			$var = new VarInt();
			$var->setName("name[$i]");
			$var->setValue(range($min, $max));
			$var->setDomainType(DomainType::DOMAIN_MIN_MAX);
			$this->add($var);
		}
	}
	
	public function setDomainType($type) {
		$this->domainType = $type;
	}
	
	public function add($var) {
		$name = $var->getName();
		$this->vars[$name] = $var;
	}
	
	public function getVar($name) {
		if(isset($this->vars[$name]))
			return $this->vars[$name];
	}
	
	public function getVars() {
		return $this->vars;
	}
}

TestProblem::test();

class TestProblem {
	public $problem;
	public $data;
	
	public function define() {
		$p = ProblemFactory::newProblem("Test");

		$x = $p->variable('X', 1, 10);
		$y = $p->variable('Y', 1, 10);
		$z = $p->variable('Z', 1, 10);
		$r = $p->variable('R', 1, 10);
		$vars = array($x, $y, $z, $r);

		$p->post($x, '<', $y);
		$p->post($z, '>', 4);
		$p->post($x->plus($y), '=', $z);

		$p->postAllDifferent($vars);

		$coef1 = array(3, 4, -5, 2);
		$p->post($coef1, $vars, '>', 0);
		$p->post($vars, '>=', 15);

		$coef2 = array(2, -4, 5, 1);
		$p->post($coef2, $vars, '>', $x->multiply($y));
		
		$this->problem = $p;
		$this->data = $vars;
	}
	
	public function solve() {
		$p = $this->problem;
		$solver = $p->getSolver();
		$solution = $solver->findSolution();
		return $solution;
	}
	
	public function solveMax() {
		$p = $this->problem;
		$solver = $p->getSolver();
		$solution = $solver->findOptimalSolution($solver::MAXIMIZE, $p->sum($this->data));
		return $solution;
	}
	
	public static function test() {
		$t = new TestProblem();
		$t->define();
		
		$solution = $t->solve();
		var_dump($solution);
		// X[1] Y[4] Z[5] R[6]
		
		$solution = $t->solveMax();
		var_dump($solution);
		// X[4] Y[6] Z[10] R[9] sum[29]
	}
}
