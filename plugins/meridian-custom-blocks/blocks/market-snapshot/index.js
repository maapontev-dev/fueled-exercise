(function (blocks, blockEditor, element, i18n) {
	const { createElement } = element;
	const { useBlockProps } = blockEditor;
	const { __ } = i18n;

	blocks.registerBlockType('meridian/market-snapshot', {
		edit() {
			const items = [
				__('Dow Jones', 'meridian-custom-blocks'),
				__('S&P 500', 'meridian-custom-blocks'),
				__('Nasdaq', 'meridian-custom-blocks'),
			];

			return createElement(
				'section',
				useBlockProps({
					className: 'meridian-markets__editor-preview',
				}),
				createElement(
					'div',
					{
						className: 'meridian-markets__grid',
					},
					items.map(function (label) {
						return createElement(
							'div',
							{ className: 'meridian-markets__item', key: label },
							createElement('span', { className: 'meridian-markets__label' }, label),
							createElement('span', { className: 'meridian-markets__value' }, '—'),
							createElement(
								'span',
								{ className: 'meridian-markets__change' },
								__('Market snapshot', 'meridian-custom-blocks'),
							),
						);
					}),
				),
			);
		},
		save() {
			return null;
		},
	});
})(window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.i18n);
