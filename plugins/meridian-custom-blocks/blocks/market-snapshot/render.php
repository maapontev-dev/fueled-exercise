<?php
/**
 * Render the market snapshot.
 *
 * @package MeridianCustomBlocks
 */

if ( ! function_exists( 'meridian_market_snapshot_fetch' ) ) {
	function meridian_market_snapshot_fetch() {
		$cache_key       = 'meridian_market_snapshot_data';
		$cached_snapshot = get_transient( $cache_key );

		if ( is_array( $cached_snapshot ) && ! empty( $cached_snapshot ) ) {
			return $cached_snapshot;
		}

		$indices = [
			'dow' => [
				'label' => 'Dow Jones',
				'url'   => 'https://query1.finance.yahoo.com/v8/finance/chart/%5EDJI',
			],
			'sp500' => [
				'label' => 'S&P 500',
				'url'   => 'https://query1.finance.yahoo.com/v8/finance/chart/%5EGSPC',
			],
			'nasdaq' => [
				'label' => 'Nasdaq',
				'url'   => 'https://query1.finance.yahoo.com/v8/finance/chart/%5EIXIC',
			],
		];

		$snapshot = [];

		foreach ( $indices as $key => $index ) {
			$response = wp_remote_get(
				$index['url'],
				[
					'timeout' => 3,
					'headers' => [
						'Accept' => 'application/json',
						'User-Agent' => 'Meridian-Website/1.0',
					],
				]
			);

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$status = (int) wp_remote_retrieve_response_code( $response );
			if ( 200 !== $status ) {
				continue;
			}

			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			$meta = $data['chart']['result'][0]['meta'] ?? null;

			if ( ! is_array( $meta ) ) {
				continue;
			}

			$price  = isset( $meta['regularMarketPrice'] ) ? (float) $meta['regularMarketPrice'] : null;
			$close  = isset( $meta['chartPreviousClose'] ) ? (float) $meta['chartPreviousClose'] : null;

			if ( null === $price || null === $close || 0.0 === $close ) {
				continue;
			}

			$change = $price - $close;
			$pct    = ( $change / $close ) * 100;

			$snapshot[ $key ] = [
				'label'  => $index['label'],
				'price'  => $price,
				'change' => $change,
				'pct'    => $pct,
			];
		}

		if ( ! empty( $snapshot ) ) {
			set_transient( $cache_key, $snapshot, 10 * MINUTE_IN_SECONDS );
			return $snapshot;
		}
	}
}

if ( ! function_exists( 'meridian_market_snapshot_format_value' ) ) {
	function meridian_market_snapshot_format_value( $value ) {
		return number_format_i18n( (float) $value, 2 );
	}
}

$snapshot = meridian_market_snapshot_fetch();
?>
<div <?php echo get_block_wrapper_attributes( [ 'class' => 'meridian-markets__grid', 'role' => 'presentation' ] ); ?>>
	<?php foreach ( [ 'dow', 'sp500', 'nasdaq' ] as $key ) : ?>
		<?php
		$labels = [
			'dow'    => 'Dow Jones',
			'sp500'  => 'S&P 500',
			'nasdaq' => 'Nasdaq',
		];
		$item = $snapshot[ $key ] ?? null;
		?>
		<div class="meridian-markets__item">
			<span class="meridian-markets__label"><?php echo esc_html( $labels[ $key ] ); ?></span>
			<?php if ( is_array( $item ) ) : ?>
				<?php
				$change = (float) $item['change'];
				$pct    = (float) $item['pct'];
				$direction = $change > 0 ? 'is-up' : ( $change < 0 ? 'is-down' : '' );
				$arrow = $change > 0 ? '↑' : ( $change < 0 ? '↓' : '—' );
				$change_text = sprintf(
					'%s %s (%s%%)',
					$arrow,
					meridian_market_snapshot_format_value( abs( $change ) ),
					number_format_i18n( abs( $pct ), 2 )
				);
				?>
				<span class="meridian-markets__value"><?php echo esc_html( meridian_market_snapshot_format_value( $item['price'] ) ); ?></span>
				<span class="meridian-markets__change <?php echo esc_attr( $direction ); ?>">
					<?php echo esc_html( $change_text ); ?>
				</span>
			<?php else : ?>
				<span class="meridian-markets__value">—</span>
				<span class="meridian-markets__change">Market data unavailable</span>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>
