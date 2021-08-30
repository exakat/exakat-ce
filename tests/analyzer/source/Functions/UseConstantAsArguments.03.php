<?php

namespace A\B\C;

// integer
error_reporting(0);

// wrong constant
error_reporting(T_SEMICOLON);

error_reporting(E_ALL);

error_reporting(E_ALL ^ E_NOTICE);

error_reporting(E_PARSE | E_NOTICE | E_ERROR | E_WARNING);

error_reporting(\E_PARSE | \E_NOTICE | E_ERROR | E_WARNING);

error_reporting(E_PARSE | E_NOTICE | T_SEMICOLON | E_WARNING);


get_html_translation_table(HTML_ENTITIES);
get_html_translation_table(\HTML_ENTITIES);
get_html_translation_table($x);
get_html_translation_table($x->b);
get_html_translation_table(f());
get_html_translation_table(C::a());

get_html_translation_table(T_COMMENT);
get_html_translation_table(HTML_ENTITIES | HTML_ENTITIES);
get_html_translation_table(null);

?>