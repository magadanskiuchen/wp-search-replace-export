<?php

namespace WP_CLI\SearchReplaceExport;

use WP_CLI;
use WP_CLI_Command;

class SearchReplaceExportCommand extends WP_CLI_Command {
	private $subcommand_assoc_args = [];

	/**
	 * An alternative to the --export argument to the bundled search-replace command
	 *
	 * The command uses both <code>search-replace</code> and <code>db export</code> behind the scenes.
	 * It first runs a search-replace action based on the passed parameters,
	 * then it exports the database and finally runs the search-replace in reverse.
	 * <strong>CAUTION:</strong> if for some reason you have strings in your
	 * database that match the "replace" argument, the reverse search-replace
	 * will convert that to the "search" argument.
	 *
	 * If you need to specify any of the named parameters supported by the
	 * <code>search-replace</code> command you should prefix them with
	 * <code>--search-replace-</code>. For example if you'd like to execute
	 * a dry run you would pass that as <code>--search-replace-dry-run</code>.
	 *
	 * If you need to specify any of the named parameters supported by the
	 * <code>db export</code> command you should prefix them with
	 * <code>--export-</code>. For example if you'd like to specify which
	 * tables to export (<code>search-replace</code> defaults to tables with
	 * the current prefix, but <code>db export</code> defaults to all tables
	 * in the DB) you would do that as <code>--export-tables=</code> and would
	 * pass a comma separated list.
	 *
	 * ## OPTIONS
	 *
	 * <search>
	 * : This is simply forwarded to the <code>search-replace</code> command
	 * as the <code>old</code> parameter.
	 *
	 * <replace>
	 * : This is forwarded to the <code>search-replace</code> command
	 * as the <code>new</code> parameter.
	 *
	 * [<filename>]
	 * : This optional argument is forwarded to the <code>db export</code> command
	 *
	 * [<table>...]
	 * : This optional argument is forwarded to the <code>search-repace</code>
	 * command
	 *
	 * [--search-replace-dry-run]
	 * : Run the entire search/replace operation and show report, but don’t save changes to the database.
	 *
	 * [--search-replace-network]
	 * : Search/replace through all the tables registered to $wpdb in a multisite install.
	 *
	 * [--search-replace-all-tables-with-prefix]
	 * : Enable replacement on any tables that match the table prefix even if not registered on $wpdb.
	 *
	 * [--search-replace-all-tables]
	 * : Enable replacement on ALL tables in the database, regardless of the prefix, and even if not registered on $wpdb. Overrides –network and –all-tables-with-prefix.
	 *
	 * [--search-replace-skip-tables=<tables>]
	 * : Do not perform the replacement on specific tables. Use commas to specify multiple tables. Wildcards are supported, e.g. <code>'wp_*options'</code> or <code>'wp_post*'</code>.
	 *
	 * [--search-replace-skip-columns=<columns>]
	 * : Do not perform the replacement on specific columns. Use commas to specify multiple columns.
	 *
	 * [--search-replace-include-columns=<columns>]
	 * : Perform the replacement on specific columns. Use commas to specify multiple columns.
	 *
	 * [--search-replace-precise]
	 * : Force the use of PHP (instead of SQL) which is more thorough, but slower.
	 *
	 * [--search-replace-recurse-objects]
	 * : Enable recursing into objects to replace strings. Defaults to true; pass –no-recurse-objects to disable.
	 *
	 * [--search-replace-verbose]
	 * : Prints rows to the console as they’re updated.
	 *
	 * [--search-replace-regex]
	 * : Runs the search using a regular expression (without delimiters). Warning: search-replace will take about 15-20x longer when using –regex.
	 *
	 * [--search-replace-regex-flags=<regex-flags>]
	 * : Pass PCRE modifiers to regex search-replace (e.g. ‘i’ for case-insensitivity).
	 *
	 * [--search-replace-regex-delimiter=<regex-delimiter>]
	 * : The delimiter to use for the regex. It must be escaped if it appears in the search string. The default value is the result of chr(1).
	 *
	 * [--search-replace-regex-limit=<regex-limit>]
	 * : The maximum possible replacements for the regex per row (or per unserialized data bit per row). Defaults to -1 (no limit).
	 *
	 * [--search-replace-format=<format>]
	 * : Render output in a particular format.
	 *     ---
	 *     default: table
	 *     options:
	 *     – table
	 *     – count
	 *     ---
	 *
	 * [--search-replace-report]
	 * : Produce report. Defaults to true.
	 *
	 * [--search-replace-report-changed-only]
	 * : Report changed fields only. Defaults to false, unless logging, when it defaults to true.
	 *
	 * [--search-replace-log[=<file>]]
	 * : Log the items changed. If <file> is not supplied or is “-“, will output to STDOUT. Warning: causes a significant slow down, similar or worse to enabling –precise or –regex.
	 *
	 * [--search-replace-before_context=<num>]
	 * : For logging, number of characters to display before the old match and the new replacement. Default 40. Ignored if not logging.
	 *
	 * [--search-replace-after_context=<num>]
	 * : For logging, number of characters to display after the old match and the new replacement. Default 40. Ignored if not logging.
	 *
	 * [--export-dbuser=<value>]
	 * : Username to pass to mysqldump. Defaults to DB_USER.
	 *
	 * [--export-dbpass=<value>]
	 * : Password to pass to mysqldump. Defaults to DB_PASSWORD.
	 *
	 * [--export-<field>=<value>]
	 * : Extra arguments to pass to mysqldump. Refer to mysqldump docs.
	 *
	 * [--export-tables=<tables>]
	 * : The comma separated list of specific tables to export. Excluding this parameter will export all tables in the database.
	 *
	 * [--export-exclude_tables=<tables>]
	 * : The comma separated list of specific tables that should be skipped from exporting. Excluding this parameter will export all tables in the database.
	 *
	 * [--export-include-tablespaces]
	 * : Skips adding the default –no-tablespaces option to mysqldump.
	 *
	 * [--export-defaults]
	 * : Loads the environment’s MySQL option files. Default behavior is to skip loading them to avoid failures due to misconfiguration.
	 *
	 * ## EXAMPLES
	 *
	 *     # Searches for "foo", replaces it with "bar" and exports the result in the export.sql file
	 *     $ wp search-replace-export foo bar export.sql
	 *     Success: search, replace and export the result
	 *
	 *     # Searches for the current siteurl, replaces it with http://production.example.com/ and exports this in a file with automaticaly-generated name by the db export command
	 *     $ wp search-replace-export $(wp option get siteurl) http://production.example.com/
	 *     Success: search, replace and export the result
	 *
	 *     # Searches for "foo", replaces is with "bar" and makes sure to export only tables with the active prefix for the instance
	 *     $ wp search-replace-export foo bar --export-tables=$(wp db tables --all-tables-with-prefix=$(wp config get table_prefix) --format=csv)
	 *     Success: search, replace and export the result
	 *
	 *     # Searches for "foo", replaces if with "bar" and GZIPs the export
	 *     $ wp search-replace-export foo bar - | gzip dump.sql.gz
	 *
	 * @when before_wp_load
	 *
	 * @param array $args       Indexed array of positional arguments.
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	public function __invoke( $args, $assoc_args ) {
		list($search, $replace, $filename, $table) = $this->parse_args($args);
		$this->subcommand_assoc_args = $this->parse_assoc_args($assoc_args);

		WP_CLI::runcommand("search-replace $search $replace $table --quiet " . $this->get_command_args('search-replace')); // do the search-replace

		WP_CLI::runcommand("db export $filename --quiet " . $this->get_command_args('export')); // export the updated DB

		WP_CLI::runcommand("search-replace $replace $search $table --quiet "  . $this->get_command_args('search-replace')); // invert the search-replace

		WP_CLI::success( 'search, replace and export the result' );
	}

	private function get_subcommands() {
		return ['search-replace', 'export'];
	}

	private function parse_args($args) {
		$search = $args[0];
		$replace = $args[1];
		$filename = $args[2] ?? '';
		$table = isset($args[3]) ? implode(' ', array_slice($args, 3)) : '';

		return array($search, $replace, $filename, $table);
	}

	private function parse_assoc_args($assoc_args) {
		$parsed_args = [];

		foreach ($assoc_args as $arg => $value) {
			foreach ($this->get_subcommands() as $command) {
				if (strpos($arg, $command) === 0) {
					if (!isset($parsed_args[$command])) {
						$parsed_args[$command] = array();
					}

					$parsed_args[$command][str_replace($command, '-', $arg)] = $value;
				}
			}
		}

		return $parsed_args;
	}

	private function format_assoc_array_as_args($assoc_array) {
		$args = '';

		foreach ($assoc_array as $key => $value) {
			$args .= ' ' . $key . '=' . $value;
		}

		return $args;
	}

	private function get_command_args($command) {
		if (!isset($this->subcommand_assoc_args[$command])) {
			return;
		}

		return $this->format_assoc_array_as_args($this->subcommand_assoc_args[$command]);
	}
}
