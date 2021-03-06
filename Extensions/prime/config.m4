dnl $Id$
dnl config.m4 for extension dnl e

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary. This file will not work
dnl without editing.

dnl If your extension references something external, use with:

dnl PHP_ARG_WITH(prime, for prime support,
dnl Make sure that the comment is aligned:
dnl [  --with-prime             Include prime support])

dnl Otherwise use enable:

PHP_ARG_ENABLE(prime, whether to enable prime support,
Make sure that the comment is aligned:
[  --enable-prime           Enable prime support])

if test "$PHP_PRIME" != "no"; then
  dnl Write more examples of tests here...

  dnl # --with-prime -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/prime.h"  # you most likely want to change this
  dnl if test -r $PHP_PRIME/$SEARCH_FOR; then # path given as parameter
  dnl   PRIME_DIR=$PHP_PRIME
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for prime files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       PRIME_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$PRIME_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the prime distribution])
  dnl fi

  dnl # --with-prime -> add include path
  dnl PHP_ADD_INCLUDE($PRIME_DIR/include)

  dnl # --with-prime -> check for lib and symbol presence
  dnl LIBNAME=prime # you may want to change this
  dnl LIBSYMBOL=prime # you most likely want to change this 

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $PRIME_DIR/lib, PRIME_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_PRIMELIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong prime lib version or lib not found])
  dnl ],[
  dnl   -L$PRIME_DIR/lib -lm -ldl
  dnl ])
  dnl
  dnl PHP_SUBST(PRIME_SHARED_LIBADD)

  PHP_NEW_EXTENSION(prime, prime.c, $ext_shared)
fi
