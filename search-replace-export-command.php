<?php

namespace WP_CLI\SearchReplaceExport;

use WP_CLI;

if ( ! class_exists( '\WP_CLI' ) ) {
	return;
}

$sre_autoloader = __DIR__ . '/vendor/autoload.php';

if ( file_exists( $sre_autoloader ) ) {
	require_once $sre_autoloader;
}

WP_CLI::add_command( 'search-replace-export', SearchReplaceExportCommand::class );
