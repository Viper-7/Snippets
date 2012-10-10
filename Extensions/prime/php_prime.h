/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2007 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  +----------------------------------------------------------------------+
*/

/* $Id: header,v 1.16.2.1.2.1 2007/01/01 19:32:09 iliaa Exp $ */

#ifndef PHP_PRIME_H
#define PHP_PRIME_H

extern zend_module_entry prime_module_entry;
#define phpext_prime_ptr &prime_module_entry

#ifdef PHP_WIN32
#define PHP_PRIME_API __declspec(dllexport)
#else
#define PHP_PRIME_API
#endif

#ifdef ZTS
#include "TSRM.h"
#endif

PHP_MINIT_FUNCTION(prime);
PHP_MSHUTDOWN_FUNCTION(prime);
PHP_RINIT_FUNCTION(prime);
PHP_RSHUTDOWN_FUNCTION(prime);
PHP_MINFO_FUNCTION(prime);

PHP_FUNCTION(is_prime);

/* 
  	Declare any global variables you may need between the BEGIN
	and END macros here:     

ZEND_BEGIN_MODULE_GLOBALS(prime)
	long  global_value;
	char *global_string;
ZEND_END_MODULE_GLOBALS(prime)
*/

/* In every utility function you add that needs to use variables 
   in php_prime_globals, call TSRMLS_FETCH(); after declaring other 
   variables used by that function, or better yet, pass in TSRMLS_CC
   after the last function argument and declare your utility function
   with TSRMLS_DC after the last declared argument.  Always refer to
   the globals in your function as PRIME_G(variable).  You are 
   encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/

#ifdef ZTS
#define PRIME_G(v) TSRMG(prime_globals_id, zend_prime_globals *, v)
#else
#define PRIME_G(v) (prime_globals.v)
#endif

#endif	/* PHP_PRIME_H */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
