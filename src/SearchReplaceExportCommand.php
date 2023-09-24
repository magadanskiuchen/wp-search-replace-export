<?php

namespace WP_CLI\SearchReplaceExport;

use WP_CLI;
use WP_CLI_Command;

class SearchReplaceExportCommand extends WP_CLI_Command {

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
		WP_CLI::success( 'search, replace and export the result' );
	}
}
