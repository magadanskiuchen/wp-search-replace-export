magadanskiuchen/wp-search-replace-export
========================================

The command uses both `search-replace` and `db export` behind the scenes.

It first runs a search-replace action based on the passed parameters, then it exports the database and finally runs the search-replace in reverse.

**CAUTION:** if for some reason you have strings in your database that match the "replace" argument, the reverse search-replace will convert that to the "search" argument.

## OPTIONS

* `<search>`
	
	This is simply forwarded to the `search-replace` command as the `old` parameter.

* `<replace>`

	This is forwarded to the `search-replace` command as the `new` parameter.

* `[<filename>]`
	
	This optional argument is forwarded to the `db export` command

* `[<table>...]`
	
	This optional argument is forwarded to the `search-repace` command

* `[--search-replace-]`
	
	If you need to specify any of the named parameters supported by the `search-replace` command you should prefix them with `--search-replace-`. For example if you'd like to execute a dry run you would pass that as `--search-replace-dry-run`.

* `[--export-]`
	
	If you need to specify any of the named parameters supported by the `db export` command you should prefix them with `--export-`.
	
	For example if you'd like to specify which tables to export (`search-replace` defaults to tables with the current prefix, but `db export` defaults to all tables in the DB) you would do that as `--export-tables=` and would pass a comma separated list.

## EXAMPLES

```sh
# Searches for "foo", replaces it with "bar" and exports the result in the export.sql file
$ wp search-replace-export foo bar export.sql

# Searches for the current siteurl, replaces it with http://production.example.com/ and exports this in a file with automaticaly-generated name by the db export command
$ wp search-replace-export $(wp option get siteurl) http://production.example.com/

# Searches for "foo", replaces is with "bar" and makes sure to export only tables with the active prefix for the instance
$ wp search-replace-export foo bar --export-tables=$(wp db tables --all-tables-with-prefix=$(wp config get table_prefix) --format=csv)

# Searches for "foo", replaces if with "bar" and GZIPs the export
$ wp search-replace-export foo bar - | gzip dump.sql.gz
```

## Installing

Installing this package requires WP-CLI. Update to the latest stable release with wp cli update.

Once you've done so, you can install this package with `wp package install https://github.com/magadanskiuchen/wp-search-replace-export`.
