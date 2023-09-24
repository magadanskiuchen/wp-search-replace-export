<?php

namespace WP_CLI\SearchReplaceExport;

use WP_CLI;
use WP_CLI_Command;

class SearchReplaceExportCommand extends WP_CLI_Command {
	private $subcommand_assoc_args = [];

	/**
	 * An alternative to the --export argument to the bundled search-replace command
	 *
	 * ## EXAMPLES
	 *
	 *     # Searches for a string, replaces it and exports the result
	 *     $ wp search-replace-export foo bar export.sql
	 *     Success: search, replace and export the result
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
