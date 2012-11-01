Orth
====

ACL processing for Eurogamer.

ACL Syntax
----------

```
<predicate> [&& <predicate>]... {
	<module>/<section> : <read|write|admin|deny>
	[<module>/<section> : <read|write|admin|deny>]...
}
````

Where ```module``` and ```section``` can be wildcarded.  Predicate of ```*``` is a
special catch-all for defining default ACL for everybody.

Default access for anything is deny.

Example:

```
* {
    help/* : read
}
role = superuser {
    * : admin
}
role = editorial {
    cms/* : read
}
site = eurogamer.de && role = editorial {
    cms/eurogamer.de : write
}
```

Later rules override earlier rules.  So put more specific stuff towards the end.

The parser is very stupid, requires the exact newline usage as in the example,
and will ignore things it doesn't understand rather than throw errors.

Comments start with ```#``` and don't have to be on their own line.  Blank lines are discarded.

Usage
-------

(See test.php)

```
$orth = new Orth\Parser( fopen( "cms.acl" ) );

// can_read( $who, $role, $team, $module_being_accesses, $section_being_accessed );
// can_write( $who, $role, $team, $module_being_accesses, $section_being_accessed );
$bool = $orth->can_read( "oliver", "editorial", "eurogamer.de", "cms", "eurogamer.de" );
$bool = $orth->can_write( "oliver", "editorial", "eurogamer.de", "cms", "eurogamer.de" );
```
