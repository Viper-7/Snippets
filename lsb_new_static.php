<pre><?php
    class Foo {
        public static function test() {
            $a = new self();
            var_dump($a); // Gives Foo, this class, even when called from Foo
        }        
        
        public static function test2() {
            $a = new static(); // Gives Bar, the 'late static binding' class
            var_dump($a);
        }
    }

    class Bar extends Foo {
        public static function test3() {
            $a = new self();
            var_dump($a); // Gives Bar, this class
        }

        public static function test4() {
            parent::test(); // Gives Foo, even though we're calling from a child
        }        

        public static function test5() {
            parent::test2(); // Gives Bar, because its using the late static binding context
        }
    }

    $obj = Bar::test();
    $obj = Bar::test3();
    $obj = Bar::test4();
    $obj = Bar::test5();
?>
