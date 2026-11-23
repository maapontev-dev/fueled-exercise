<?php
/**
 * Plugin Name: Meridian Custom Blocks
 * Description: Adds a set of dynamic blocks custom build for the Meridian Theme.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.3
 * Author: Meridian
 * License: GPL-2.0-or-later
 * Text Domain: meridian-custom-blocks
 *
 * @package MeridianCustomBlocks
 */

/**
 * Register custom blocks.
 *
 * @return void
 */
function meridian_custom_blocks_register() {
	register_block_type( __DIR__ . '/blocks/testimonial' );
	register_block_type( __DIR__ . '/blocks/market-snapshot' );
}
add_action( 'init', 'meridian_custom_blocks_register' );
