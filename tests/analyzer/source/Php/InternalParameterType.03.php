<?php

	// all good, even the missing arguments
	preg_match('/\bnull\b/i', $typehints);

	// 3 is not a valid type
    preg_match(3, $typehints);

	// 3rd arg is good
	preg_match('/\bnull\b/i', $typehints, []);

	// 3rd arg is bad
	preg_match('/\bnull\b/i', $typehints, "");

	// 4th arg is good
	preg_match('/\bnull\b/i', $typehints, $r, 3);

	// 4th arg is bad
	preg_match('/\bnull\b/i', $typehints, $r, "");

?>