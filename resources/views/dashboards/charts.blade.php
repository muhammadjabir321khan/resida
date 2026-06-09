@php($role = $dashboardRole ?? 'landlord')
<script>
document.addEventListener('DOMContentLoaded', function () {
	if (typeof ApexCharts === 'undefined') {
		return;
	}

	@if ($role === 'landlord' && isset($landlordChart))
		(function () {
			var el = document.getElementById('kt_dashboard_landlord_chart');
			if (!el) return;
			var h = parseInt(window.getComputedStyle(el).height, 10) || 320;
			var gray = '#99A1B7';
			var primary = '#009ef7';
			var success = '#50cd89';
			var chart = new ApexCharts(el, {
				series: [
					{ name: @json(__('Income')), data: @json($landlordChart['income']) },
					{ name: @json(__('Expense')), data: @json($landlordChart['expense']) }
				],
				chart: { fontFamily: 'inherit', type: 'area', height: h, toolbar: { show: false }, zoom: { enabled: false } },
				dataLabels: { enabled: false },
				stroke: { curve: 'smooth', width: 2 },
				xaxis: {
					categories: @json($landlordChart['labels']),
					axisBorder: { show: false },
					axisTicks: { show: false },
					labels: { style: { colors: gray, fontSize: '12px' } }
				},
				yaxis: { labels: { style: { colors: gray, fontSize: '12px' } } },
				legend: { position: 'top', horizontalAlign: 'right' },
				colors: [success, primary],
				fill: { type: 'gradient', gradient: { shadeIntensity: 0.4, opacityFrom: 0.35, opacityTo: 0.05 } },
				tooltip: { style: { fontSize: '12px' } }
			});
			chart.render();
		})();
	@endif

	@if ($role === 'admin' && isset($adminChart))
		(function () {
			var el = document.getElementById('kt_dashboard_admin_chart');
			if (!el) return;
			var h = parseInt(window.getComputedStyle(el).height, 10) || 320;
			var gray = '#99A1B7';
			var chart = new ApexCharts(el, {
				series: [{ name: @json(__('New users')), data: @json($adminChart['series']) }],
				chart: { fontFamily: 'inherit', type: 'bar', height: h, toolbar: { show: false } },
				plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
				dataLabels: { enabled: false },
				xaxis: {
					categories: @json($adminChart['labels']),
					axisBorder: { show: false },
					axisTicks: { show: false },
					labels: { style: { colors: gray, fontSize: '12px' } }
				},
				yaxis: { labels: { style: { colors: gray, fontSize: '12px' } } },
				colors: ['#7239ea'],
				tooltip: { style: { fontSize: '12px' } }
			});
			chart.render();
		})();
	@endif

	@if ($role === 'tenant' && isset($tenantChart))
		(function () {
			var el = document.getElementById('kt_dashboard_tenant_chart');
			if (!el) return;
			var labels = @json($tenantChart['labels']);
			var series = @json($tenantChart['series']);
			if (!labels.length || series.every(function (v) { return v === 0; })) {
				el.innerHTML = '<p class="text-muted text-center py-10 mb-0">' + @json(__('No installment data yet.')) + '</p>';
				return;
			}
			var chart = new ApexCharts(el, {
				series: series,
				labels: labels,
				chart: { fontFamily: 'inherit', type: 'donut', height: 280 },
				legend: { position: 'bottom' },
				colors: ['#50cd89', '#009ef7', '#f1416c', '#DBDFE9'],
				dataLabels: { enabled: true },
				plotOptions: { pie: { donut: { size: '65%' } } }
			});
			chart.render();
		})();
	@endif
});
</script>
