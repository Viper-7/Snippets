<?php
namespace V7F\Template;

#	Errors
class H2o_Error extends \Exception {}
class ParseError extends H2o_Error {}
class TemplateNotFound extends H2o_Error {}
class TemplateSyntaxError extends H2o_Error {}

?>