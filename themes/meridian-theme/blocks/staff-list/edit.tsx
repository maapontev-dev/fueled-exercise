import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import type { BlockEditProps } from '@wordpress/blocks';
import { PanelBody, Placeholder, RangeControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import { StaffCardPreview, toStaffCardProps, type StaffRecord } from '../shared/staff-card-preview';

export const BlockEdit = ({
	attributes,
	setAttributes,
}: BlockEditProps<Record<string, unknown>>) => {
	const blockProps = useBlockProps();
	const maxPosts = Number(attributes.maxPosts) || 0;

	const staff = useSelect(
		(select) =>
			(select('core') as any).getEntityRecords('postType', 'staff_member', {
				per_page: -1,
				orderby: 'title',
				order: 'asc',
				_embed: true,
			}) as StaffRecord[] | null,
		[],
	);

	const departments = useSelect(
		(select) =>
			(select('core') as any).getEntityRecords('taxonomy', 'staff_department', {
				per_page: -1,
				hide_empty: true,
				orderby: 'name',
				order: 'asc',
			}) as Array<{ id: number; name: string; slug: string }> | null,
		[],
	);

	const controls = (
		<InspectorControls>
			<PanelBody title={__('Directory', 'meridian-theme')}>
				<RangeControl
					label={__('Number of staff to show', 'meridian-theme')}
					help={__(
						'0 shows everyone. A smaller number turns this into a teaser that links to the full team page.',
						'meridian-theme',
					)}
					value={maxPosts}
					onChange={(value) => setAttributes({ maxPosts: value ?? 0 })}
					min={0}
					max={12}
				/>
			</PanelBody>
		</InspectorControls>
	);

	if (staff === null) {
		return (
			<>
				{controls}
				<div {...blockProps}>
					<Placeholder icon="grid-view" label={__('Staff List', 'meridian-theme')}>
						<Spinner />
					</Placeholder>
				</div>
			</>
		);
	}

	if (staff.length === 0) {
		return (
			<>
				{controls}
				<div {...blockProps}>
					<Placeholder
						icon="grid-view"
						label={__('Staff List', 'meridian-theme')}
						instructions={__(
							'No staff members have been published yet.',
							'meridian-theme',
						)}
					/>
				</div>
			</>
		);
	}

	const visible = maxPosts > 0 ? staff.slice(0, maxPosts) : staff;

	return (
		<>
			{controls}
			<div {...blockProps}>
				{maxPosts === 0 && departments && departments.length > 0 ? (
					<div
						className="meridian-staff-list__filter"
						aria-label={__('Department filter preview', 'meridian-theme')}
					>
						<label
							className="meridian-staff-list__filter-label"
							htmlFor="meridian-staff-list-department-preview"
						>
							{__('Department', 'meridian-theme')}
						</label>
						<select
							className="meridian-staff-list__filter-select"
							id="meridian-staff-list-department-preview"
							disabled
						>
							<option>{__('All departments', 'meridian-theme')}</option>
							{departments.map((department) => (
								<option key={department.id}>{department.name}</option>
							))}
						</select>
					</div>
				) : null}
				<div className="meridian-staff-list">
					{visible.map((member) => (
						<StaffCardPreview key={member.id} {...toStaffCardProps(member)} />
					))}
				</div>
			</div>
		</>
	);
};
