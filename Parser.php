<?php

namespace Orth;

class Parser {

	public $rules;

	public function __construct( $acl ) {

		$this->rules = array();

		if( $acl instanceof string ) {
			$this->parse( $acl );
		} else {
			// assume it's a stream handle or file or summin'
			$this->parse( stream_get_contents( $acl ) );
		}


	}

	public function parse( $acl ) {
		// "match", "rules"
		$state = "match";
		$lines = preg_split( "/\n/", $acl );
		$acl = "";
		foreach( $lines as $l ) {
			// strip comments
			$l = preg_replace( "/\#.*$/", "", $l );
			// skip empty lines
			if( preg_match( "/^\s*$/", $l ) ) {
				continue;
			}
			$acl .= $l . "\n";
		}
		$m = array();
		if( preg_match_all( "/(?P<match>[^\{\}]*)\{(?P<perms>[^\{\}]*)\}/xsm", $acl, $m ) ) {
			$perms = $m[ "perms" ];
			foreach( $m[ "match" ] as $index => $match ) {
				$rule = array( "conditions" => array(), "permissions" => array() );
				// parse conditions
				if( trim( $match ) == "*" ) {
					// condition is default allow
					// so just don't have any conditions
				} else {
					foreach( preg_split( "/\s*&&\s*/", trim( $match ) ) as $pred ) {
						$m2 = array();
						if( preg_match( "/(role|site|user)\s*=\s*([^\s]+)/", $pred, $m2 ) ) {
							$rule[ "conditions" ][ $m2[1] ] = $m2[2];
						}
					}
				}
				// parse permissions
				// one per line
				foreach( preg_split( "/\n/", $perms[ $index ] ) as $perm ) {
					$m2 = array();
					if( preg_match( "/([^\s]+)\s*:\s*(read|write|admin|deny)/", trim( $perm ), $m2 ) ) {
						$rule[ "permissions" ][ $m2[1] ] = $m2[2];
					}
				}

				$this->rules[] = $rule;
			}
		}
	}

	public function glob2preg( $glob ) {
		$p = "#" . preg_replace( "/\*/", "[^\s]+", $glob ) . "#";
		return $p;
	}

	public function can_read( $user_name, $user_role, $user_site, $module, $module_site ) {
		if( $this->get_access( $user_name, $user_role, $user_site, $module, $module_site ) != "deny" ) {
			return true;
		}
		return false;
	}
	public function can_write( $user_name, $user_role, $user_site, $module, $module_site ) {
		$access = $this->get_access( $user_name, $user_role, $user_site, $module, $module_site );
		if( $access == "write" || $access == "admin" ) {
			return true;
		}
		return false;
	}
	public function can_admin( $user_name, $user_role, $user_site, $module, $module_site ) {
		$access = $this->get_access( $user_name, $user_role, $user_site, $module, $module_site );
		if( $access == "admin" ) {
			return true;
		}
		return false;
	}
	public function get_access( $user_name, $user_role, $user_site, $module, $module_site ) {
		$access_level = "deny";
		foreach( $this->rules as $r ) {
			if( isset( $r[ "conditions" ][ "user" ] ) ) {
				if( $r[ "conditions" ][ "user" ] != $user_role ) {
					// this rule won't match
					continue;
				}
			}
			if( isset( $r[ "conditions" ][ "role" ] ) ) {
				if( $r[ "conditions" ][ "role" ] != $user_role ) {
					// this rule won't match
					continue;
				}
			}
			if( isset( $r[ "conditions" ][ "site" ] ) ) {
				if( !preg_match( $this->glob2preg( $r[ "conditions" ][ "site" ] ), $user_site ) ) {
					// this rule won't match
					continue;
				}
			}
			$to_match = $module . "/" . $module_site;
			foreach( $r[ "permissions" ] as $spec => $level ) {
				if( preg_match( $this->glob2preg( $spec ), $to_match ) ) {
					$access_level = $level;
				}
			}
		}
		return $access_level;
	}
}

//EOF
