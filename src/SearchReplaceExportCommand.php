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
	 * [--search-replace-]
	 * : If you need to specify any of the named parameters supported by the
	 * <code>search-replace</code> command you should prefix them with
	 * <code>--search-replace-</code>. For example if you'd like to execute
	 * a dry run you would pass that as <code>--search-replace-dry-run</code>.
	 *
	 * [--export-]
	 * : If you need to specify any of the named parameters supported by the
	 * <code>db export</code> command you should prefix them with
	 * <code>--export-</code>. For example if you'd like to specify which
	 * tables to export (<code>search-replace</code> defaults to tables with
	 * the current prefix, but <code>db export</code> defaults to all tables
	 * in the DB) you would do that as <code>--export-tables=</code> and would
	 * pass a comma separated list.
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
