DirectoryWalker
================

Simple Directory Walker

 * Walks through a directory and retrieves the names of all files and directories
 * Optionally recursively walks child-directories
 * Optionally filter the retrieved list for files with comply with a list of allowed extensions

Results are cached for best performance

Please refer to the [documentation](http://jrfnl.github.io/DirectoryWalker/) for more information.


Changelog
=========

= 1.0.1 =
Added some path validation to prevent an E_WARNING when path is not a directory + added unit test for this validation

= 1.0 =
Initial release