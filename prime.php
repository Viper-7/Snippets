<?php
	class Prime {
		private $count = '1';
		private $letters;
		private $primes;
		
		function reset() {
			$this->count = '1';
		}
		
		function perfect_root($num) {
			$sqrt = bcsqrt($num);
			for($x=0;$x < $sqrt;$x++) {
				if(bcmul($x,$x) == $num) {
					return $x;
				}
			}
			return $num;
		}
		
		function is_prime($num) {
			$sqrt = bcsqrt($num);
			$numfactors = 0;
			
			for($factor='1';bccomp($factor,$sqrt)!=1;$factor=bcadd($factor,1)) { 
				$remainder = bcmod($num,$factor);
				if($remainder == 0){
					$numfactors++;
				}
				if($numfactors == 2){
					break; // Not prime
				}
			}
			if ($numfactors < 2){ 
				return true;
			} else {
				return false;
			}
		}
		
		function next_prime() {
			while(true) {
				$this->count = bcadd($this->count,'1');
				$sqrt = bcsqrt($this->count);
				$numfactors = 0;
				for($factor=1;$factor < $sqrt;$factor++) { 
					$remainder = bcmod($this->count,$factor);
					if($remainder == 0){
						$numfactors++;
					}
					if($numfactors == 2){
						break; // Not prime
					}
				}
				if ($numfactors < 2){
					return $this->count;
				}
			}
		}
		
		function get_n_primes($num) {
			$output = array();
			for($x=0;$x<$num;$x++) {
				$output[] = $this->next_prime();
			}
			return $output;
		}
		
		function hash_to_string($hash) {
			if(!isset($this->primes)) {
				$this->letters = range('a','z');
				$this->primes = $this->get_n_primes(100);
				$this->count = '1';
			}

			$string="";

			while($hash > 1) {
				foreach($this->primes as $index => $prime) {
					if(bcmod($hash,$prime) == 0) {
						$string .= $this->letters[$index];
						$hash = bcdiv($hash,$prime);
					}
				}
			}
			
			return $string;
		}
		
		function string_to_hash($string) {
			if(!isset($this->primes)) {
				$this->letters = range('a','z');
				$this->primes = $this->get_n_primes(100);
				$this->count = '1';
			}

			$hash=1;
			$string = str_split(strtolower($string));
			
			foreach($string as $char) {
				$index = array_search($char, $this->letters);
				$hash = bcmul($hash, $this->primes[$index]);
			}
			
			return $hash;
		}		

	}
?>