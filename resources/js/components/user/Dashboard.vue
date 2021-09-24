<template>
	<Card :title="cardTitle" :marginClass="marginClass">
		<template slot="card_body">
			<div class="card-block card-dashboard">
				<bootstrap-4-datatable :id="tableId" :columns="columns" :data="tableData" :filter="filter" :per-page="perPage">
					<template slot-scope="{ row }">
						<tr>
							<td>{{ row.lead_id }}</td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					</template>
					<template slot="no-results">
						No results found.
					</template>
					<template slot="footer" slot-scope="{ rows, columns, pagination }">
						<tr>
							<td :colspan="columns.length" :rows="rows"><p class="float-left">Showing rows {{pagination.from}} to {{pagination.to - 1}} of {{pagination.of}} items.</p>
								<bootstrap-4-datatable-pager v-model="tableData" type="abbreviated"></bootstrap-4-datatable-pager>
							</td>
						</tr>
					</template>
				</bootstrap-4-datatable>
			</div>
		</template>
	</Card>
</template>

<script>
export default {
	name: "OrganisationDashboard",
	data: () => ({
		cardTitle: 'Leads Dashboard',
		tableId: 'leads_dashboard',
		marginClass: 'mb-0',
		filter: '',
		perPage: 10,
		columns: [
			{ label: 'Lead ID', field: 'lead_id', headerAlign: 'left', sortable: false },
			{ label: 'Enquirer', field: 'enquirer', headerAlign: 'left', sortable: false },
			{ label: 'Postcode', field: 'postcode', headerAlign: 'left', sortable: false },
			{ label: 'Phone', field: 'phone', headerAlign: 'left', sortable: false },
			{ label: 'Date', field: 'date', headerAlign: 'left', sortable: false },
			{ label: 'Escalation Status', field: 'escalation_status', headerAlign: 'left', sortable: false },
			{ label: 'Sale', field: 'sale', headerAlign: 'left', sortable: false },
			{ label: '', field: 'action', headerAlign: 'left', class: 'border-top-0 all', sortable: false }
		],
		tableData: []
	})
}
</script>