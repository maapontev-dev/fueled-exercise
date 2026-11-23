(function (blocks, blockEditor, components, data, element, i18n) {
	const { createElement, Fragment, RawHTML } = element;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, Placeholder, SelectControl, Spinner } = components;
	const { useSelect } = data;
	const { __ } = i18n;

	function Edit({ attributes, setAttributes }) {
		const testimonials = useSelect(
			(select) =>
				select('core').getEntityRecords('postType', 'testimonial', {
					per_page: -1,
					orderby: 'title',
					order: 'asc',
					status: 'publish',
				}),
			[],
		);
		const testimonialId = attributes.testimonialId || 0;
		const selected = (testimonials || []).find(
			(testimonial) => testimonial.id === testimonialId,
		);
		const options = [
			{ label: __('Choose a testimonial', 'meridian-testimonial-block'), value: 0 },
			...(testimonials || []).map((testimonial) => ({
				label: testimonial.title?.rendered || __('(no name)', 'meridian-testimonial-block'),
				value: testimonial.id,
			})),
		];

		let preview;

		if (!Array.isArray(testimonials)) {
			preview = createElement(
				Placeholder,
				{
					icon: 'format-quote',
					label: __('Testimonial', 'meridian-testimonial-block'),
				},
				createElement(Spinner),
			);
		} else if (selected) {
			const attribution = [
				selected.meta?.meridian_testimonial_role,
				selected.meta?.meridian_testimonial_company,
			]
				.filter(Boolean)
				.join(', ');

			preview = createElement(
				'section',
				{
					className: 'meridian-testimonial wp-block-group alignfull meridian-quote',
					style: {
						paddingTop: 'var(--wp--preset--spacing--40-64)',
						paddingBottom: 'var(--wp--preset--spacing--40-64)',
					},
				},
				createElement(
					'blockquote',
					{ className: 'meridian-testimonial__quote' },
					createElement(RawHTML, null, selected.content?.rendered || ''),
					createElement('cite', null, attribution),
				),
			);
		} else {
			preview = createElement(Placeholder, {
				icon: 'format-quote',
				label: __('Testimonial', 'meridian-testimonial-block'),
				instructions:
					testimonialId && testimonials.length
						? __(
								'This testimonial is no longer published. Choose another testimonial.',
								'meridian-testimonial-block',
							)
						: __('Choose a testimonial to display.', 'meridian-testimonial-block'),
			});
		}

		return createElement(
			Fragment,
			null,
			createElement(
				InspectorControls,
				null,
				createElement(
					PanelBody,
					{ title: __('Testimonial', 'meridian-testimonial-block') },
					createElement(SelectControl, {
						label: __('Choose a testimonial', 'meridian-testimonial-block'),
						value: testimonialId,
						options,
						onChange: (value) => setAttributes({ testimonialId: Number(value) }),
					}),
				),
			),
			createElement('section', useBlockProps(), preview),
		);
	}

	blocks.registerBlockType('meridian/testimonial', {
		edit: Edit,
		save() {
			return null;
		},
	});
})(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.data,
	window.wp.element,
	window.wp.i18n,
);
