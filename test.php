<?php

	require( "Parser.php" );

	$acl = fopen( "../../config/cms.eurogamer.net.acl", "r" );

	$orth = new Orth\Parser( $acl );

	$user = "mark";
	$user_role = "superuser";
	$user_site = "eurogamer.net";

	print "should be false: ";
	var_dump( $orth->can_write( "oliver", "editorial", "eurogamer.de", "cms", "eurogamer.net" ) );

	print "should be true: ";
	var_dump( $orth->can_write( "oliver", "editorial", "eurogamer.de", "cms", "eurogamer.de" ) );

	print "should be true: ";
	var_dump( $orth->can_write( "mark", "superuser", "eurogamer.net", "cms", "eurogamer.net" ) );

	print "should be true: ";
	var_dump( $orth->can_write( "mark", "superuser", "eurogamer.net", "cms", "eurogamer.de" ) );

?>
