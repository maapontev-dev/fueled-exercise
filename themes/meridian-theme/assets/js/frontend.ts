import '../css/frontend.css';

const initializeStaffDepartmentFilter = (): void => {
	document
		.querySelectorAll<HTMLSelectElement>('.meridian-staff-list__filter-select')
		.forEach((select) => {
			select.addEventListener('change', () => {
				const form = select.closest<HTMLFormElement>('form');

				if (!form) {
					return;
				}

				if (!select.value) {
					window.location.assign(form.action);
					return;
				}

				form.submit();
			});
		});
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initializeStaffDepartmentFilter);
} else {
	initializeStaffDepartmentFilter();
}
