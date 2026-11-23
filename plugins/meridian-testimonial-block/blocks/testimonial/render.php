<?php

/**
 * Render the selected testimonial.
 *
 * @package MeridianTestimonialBlock
 *
 * @var array{testimonialId?: int} $attributes Block attributes.
 */

$testimonial_id = $attributes['testimonialId'] ?? 0;

if (! $testimonial_id) {
	return;
}

$testimonial = get_post($testimonial_id);

if (
	! $testimonial instanceof WP_Post
	|| 'testimonial' !== $testimonial->post_type
	|| 'publish' !== $testimonial->post_status
) {
	return;
}

$quote = wp_kses_post(apply_filters('the_content', $testimonial->post_content));

if ('' === trim(wp_strip_all_tags($quote))) {
	return;
}

$role        = get_post_meta( $testimonial_id, 'meridian_testimonial_role', true );
$company     = get_post_meta( $testimonial_id, 'meridian_testimonial_company', true );
$attribution = array_filter(
	[
		is_string( $role ) ? trim( $role ) : '',
		is_string( $company ) ? trim( $company ) : '',
	]
);
?>
<section <?php echo get_block_wrapper_attributes(['class' => 'meridian-testimonial wp-block-group alignfull meridian-quote', 'style' => 'padding-top:var(--wp--preset--spacing--40-64);padding-bottom:var(--wp--preset--spacing--40-64)']); ?>>
	<blockquote class="wp-block-quote alignwide meridian-quote__inner">
		<?php if ( ! empty( $quote ) ) : ?>
			<p class="has-heading-3-font-size"><?php echo $quote; ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $attribution ) ) : ?>
			<cite><?php echo esc_html( implode( ', ', $attribution ) ); ?></cite>
		<?php endif; ?>
	</blockquote>
</section>