Orth
----

ACL processing for Eurogamer.

Example:

```
* {
    help/* : read
}
role = superuser {
    * : write
}
role = editorial {
    cms/* : read
}
site = eurogamer.de && role = editorial {
    cms/eurogamer.de : write
}
```
