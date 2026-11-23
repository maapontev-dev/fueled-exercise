<?php
/**
 * Staff List block markup.
 *
 * Renders current staff as a grid of cards. Each card is produced by the same
 * presenter the Staff Card block uses, so a person looks identical in both
 * places. With a positive `maxPosts` the block is a teaser — it shows that many
 * people and links to the full `/team/` directory when more exist.
 *
 * @package MeridianTheme
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var string               $content    Block content.
 * @var WP_Block             $block      Block instance.
 */

$meridian_raw     = $attributes['maxPosts'] ?? 0;
$meridian_max     = is_numeric( $meridian_raw ) ? max( 0, (int) $meridian_raw ) : 0;
$meridian_limited = $meridian_max > 0;

$meridian_archive_url = get_post_type_archive_link( 'staff_member' );
$meridian_filter      = '';
$meridian_department  = '';
$meridian_on_archive  = is_post_type_archive( 'staff_member' );
$meridian_terms       = [];

// Teasers and other listings are deliberately not filtered. The department
// control belongs only to the staff directory archive.
if ( ! $meridian_limited && $meridian_on_archive ) {
	$meridian_staff_ids = get_posts(
		[
			'post_type'      => 'staff_member',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		]
	);

	if ( ! empty( $meridian_staff_ids ) ) {
		$meridian_terms = get_terms(
			[
				'taxonomy'   => 'staff_department',
				'object_ids' => $meridian_staff_ids,
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			]
		);
	}

	if ( is_array( $meridian_terms ) ) {
		$meridian_requested_department = isset( $_GET['department'] ) ? sanitize_key( wp_unslash( $_GET['department'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- A read-only filter.

		foreach ( $meridian_terms as $meridian_term ) {
			if ( $meridian_term instanceof WP_Term && $meridian_requested_department === $meridian_term->slug ) {
				$meridian_department = $meridian_term->slug;
				break;
			}
		}

		$meridian_filter_options = sprintf(
			'<option value="">%1$s</option>',
			esc_html__( 'All departments', 'meridian-theme' )
		);

		foreach ( $meridian_terms as $meridian_term ) {
			if ( ! $meridian_term instanceof WP_Term ) {
				continue;
			}

			$meridian_filter_options .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $meridian_term->slug ),
				selected( $meridian_department, $meridian_term->slug, false ),
				esc_html( $meridian_term->name )
			);
		}

		$meridian_filter = sprintf(
			'<form class="meridian-staff-list__filter" method="get" action="%1$s">' .
				'<label class="meridian-staff-list__filter-label" for="staff-department">%2$s</label>' .
				'<select class="meridian-staff-list__filter-select" id="staff-department" name="department">%3$s</select>' .
			'</form>',
			esc_url( $meridian_archive_url ? $meridian_archive_url : home_url( '/team/' ) ),
			esc_html__( 'Department', 'meridian-theme' ),
			$meridian_filter_options
		);
	}
}

$meridian_query = new WP_Query(
	[
		'post_type'           => 'staff_member',
		'post_status'         => 'publish',
		'posts_per_page'      => $meridian_limited ? $meridian_max : -1,
		'orderby'             => 'title',
		'order'               => 'ASC',
		// Only need the total when limiting, to decide whether to show the "view all" link.
		'no_found_rows'       => ! $meridian_limited,
		'ignore_sticky_posts' => true,
		'tax_query'           => '' !== $meridian_department
			? [
				[
					'taxonomy' => 'staff_department',
					'field'    => 'slug',
					'terms'    => [ $meridian_department ],
				],
			]
			: [],
	]
);

if ( ! $meridian_query->have_posts() ) {
	?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
		<?php echo $meridian_filter; ?>
		<p class="meridian-staff-list__no-results" role="status">
			<?php esc_html_e( 'No staff members found.', 'meridian-theme' ); ?>
		</p>
	</div>
	<?php
	return;
}

$meridian_cards = '';

foreach ( $meridian_query->posts as $meridian_member ) {
	if ( $meridian_member instanceof WP_Post ) {
		$meridian_cards .= \MeridianTheme\People\StaffCard::render( $meridian_member->ID );
	}
}

if ( '' === $meridian_cards ) {
	return;
}

// In teaser mode, link to the full directory when more staff exist than are shown.
$meridian_more = '';
if ( $meridian_limited && $meridian_query->found_posts > $meridian_query->post_count ) {
	if ( $meridian_archive_url ) {
		$meridian_more = sprintf(
			'<p class="meridian-staff-list__more"><a class="meridian-staff-list__more-link" href="%1$s">%2$s</a></p>',
			esc_url( $meridian_archive_url ),
			esc_html__( 'View the full team →', 'meridian-theme' )
		);
	}
}
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core-generated attribute string. ?>>
	<?php echo $meridian_filter; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built with escaped values above. ?>
	<div class="meridian-staff-list">
		<?php echo $meridian_cards; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- StaffCard::render() escapes every dynamic value. ?>
	</div>
	<?php echo $meridian_more; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from esc_url() and esc_html__() above. ?>
</div>
