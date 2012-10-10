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

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_prime.h"

/* If you declare any globals in php_prime.h uncomment this:
ZEND_DECLARE_MODULE_GLOBALS(prime)
*/

/* True global resources - no need for thread safety here */
static int le_prime;

/* {{{ prime_functions[]
 *
 * Every user visible function must have an entry in prime_functions[].
 */
zend_function_entry prime_functions[] = {
	PHP_FE(perfect_root, NULL)
	PHP_FE(is_prime, NULL)
	{NULL, NULL, NULL}	/* Must be the last line in prime_functions[] */
};
/* }}} */

/* {{{ prime_module_entry
 */
zend_module_entry prime_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
	STANDARD_MODULE_HEADER,
#endif
	"prime",
	prime_functions,
	PHP_MINIT(prime),
	PHP_MSHUTDOWN(prime),
	NULL,		/* Replace with NULL if there's nothing to do at request start */
	NULL,	/* Replace with NULL if there's nothing to do at request end */
	PHP_MINFO(prime),
#if ZEND_MODULE_API_NO >= 20010901
	"0.1", /* Replace with version number for your extension */
#endif
	STANDARD_MODULE_PROPERTIES
};
/* }}} */

#ifdef COMPILE_DL_PRIME
ZEND_GET_MODULE(prime)
#endif

/* {{{ PHP_INI
 */
/* Remove comments and fill if you need to have entries in php.ini
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("prime.global_value",      "42", PHP_INI_ALL, OnUpdateLong, global_value, zend_prime_globals, prime_globals)
    STD_PHP_INI_ENTRY("prime.global_string", "foobar", PHP_INI_ALL, OnUpdateString, global_string, zend_prime_globals, prime_globals)
PHP_INI_END()
*/
/* }}} */

/* {{{ php_prime_init_globals
 */
/* Uncomment this function if you have INI entries
static void php_prime_init_globals(zend_prime_globals *prime_globals)
{
	prime_globals->global_value = 0;
	prime_globals->global_string = NULL;
}
*/
/* }}} */

/* {{{ PHP_MINIT_FUNCTION
 */
PHP_MINIT_FUNCTION(prime)
{
	/* If you have INI entries, uncomment these lines 
	REGISTER_INI_ENTRIES();
	*/
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MSHUTDOWN_FUNCTION
 */
PHP_MSHUTDOWN_FUNCTION(prime)
{
	/* uncomment this line if you have INI entries
	UNREGISTER_INI_ENTRIES();
	*/
	return SUCCESS;
}
/* }}} */

/* {{{ PHP_MINFO_FUNCTION
 */
PHP_MINFO_FUNCTION(prime)
{
	php_info_print_table_start();
	php_info_print_table_header(2, "prime support", "wootah");
	php_info_print_table_end();

	/* Remove comments if you have entries in php.ini
	DISPLAY_INI_ENTRIES();
	*/
}
/* }}} */


/* {{{ PHP_FUNCTION(perfect_root)
 */
PHP_FUNCTION(perfect_root)
{
  long a, b, c, d, i;

  if (ZEND_NUM_ARGS() != 1) {
    WRONG_PARAM_COUNT;
  }
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &a) == FAILURE) {
    return;
  }
  
  i = 0;
  c = a;
  b = sqrt(a);
  
  if(b < a && b > 0) {
    while(i <= b) {
 	i++;
 	d = i * i;
	if(d == a) {
	  c = i;
	}
    }
  }
  
  RETVAL_LONG(c);
}
/* }}} */


/* {{{ PHP_FUNCTION(is_prime)
 */
PHP_FUNCTION(is_prime)
{
  long invar, a;
  double d;
  long factors;
  
  if (ZEND_NUM_ARGS() != 1) {
    WRONG_PARAM_COUNT;
  }
  if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "l", &invar) == FAILURE) {
    return;
  }
  
  a = 1;
  d = ceil(sqrt((double)invar));
  factors = 0;
  
  while(a <= d && d != invar) {
	if((invar % a) == 0) {
		factors++;
	}
	if(factors > 1) {
		break;
	}
	a++;
  }
  
  RETVAL_BOOL(factors < 2);
}
/* }}} */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
