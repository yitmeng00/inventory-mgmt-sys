document.addEventListener("DOMContentLoaded", async () => {
    await Promise.all([
        loadOverview(),
        loadProductChart(),
        loadRevenueChart(),
        loadPurchaseChart(),
        loadSaleChart(),
        loadLowStockChart(),
        loadBestSellingChart(),
    ]);
});

async function loadOverview() {
    try {
        const data = await API.get("/db/dashboard_db.php?action=get_overview");
        if (!data.success) return;
        const d = data.data;
        const set = (id, val, isCurrency = false) => {
            const el = document.getElementById(id);
            if (el)
                IMS.animateCounter(
                    el,
                    parseFloat(val || 0),
                    isCurrency ? 1800 : 1400,
                );
        };
        set("stat-total-stock", d.total_stock);
        set("stat-low-stock", d.low_stock);
        set("stat-out-of-stock", d.out_of_stock);
        set("stat-purchases", d.total_purchases, true);
        set("stat-sales", d.total_sales, true);
    } catch (e) {
        console.error("Overview error", e);
    }
}

const CHART_COLORS = [
    "#6366f1",
    "#10b981",
    "#f59e0b",
    "#ef4444",
    "#3b82f6",
    "#8b5cf6",
    "#ec4899",
    "#14b8a6",
];

function makeChart(id, type, labels, datasets, options = {}) {
    const ctx = document.getElementById(id)?.getContext("2d");
    if (!ctx) return;
    const { plugins: customPlugins = {}, ...restOptions } = options;
    return new Chart(ctx, {
        type,
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            ...restOptions,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { boxWidth: 12, font: { size: 11 } },
                },
                tooltip: {
                    callbacks: {
                        label: (c) =>
                            " " +
                            (c.dataset.label ? c.dataset.label + ": " : "") +
                            c.formattedValue,
                    },
                },
                ...customPlugins,
            },
        },
    });
}

async function loadProductChart() {
    try {
        const data = await API.get(
            "/db/dashboard_db.php?action=get_product_chart",
        );
        if (!data.success || !data.data.length) return;
        makeChart(
            "product-chart",
            "bar",
            data.data.map((d) => d.category_name),
            [
                {
                    label: "Products",
                    data: data.data.map((d) => d.total),
                    backgroundColor: CHART_COLORS,
                    borderRadius: 6,
                },
            ],
            {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        );
    } catch (e) {
        console.error(e);
    }
}

async function loadRevenueChart() {
    try {
        const data = await API.get(
            "/db/dashboard_db.php?action=get_revenue_profit_chart",
        );
        if (!data.success) return;
        makeChart(
            "revenue-chart",
            "line",
            data.data.map((d) => d.month),
            [
                {
                    label: "Revenue",
                    data: data.data.map((d) => d.revenue),
                    borderColor: "#6366f1",
                    backgroundColor: "rgba(99,102,241,0.1)",
                    tension: 0.4,
                    fill: true,
                },
                {
                    label: "Profit",
                    data: data.data.map((d) => d.profit),
                    borderColor: "#10b981",
                    backgroundColor: "rgba(16,185,129,0.1)",
                    tension: 0.4,
                    fill: true,
                },
            ],
            { scales: { y: { beginAtZero: true } } },
        );
    } catch (e) {
        console.error(e);
    }
}

async function loadPurchaseChart() {
    try {
        const data = await API.get(
            "/db/dashboard_db.php?action=get_purchase_chart",
        );
        if (!data.success) return;
        makeChart(
            "purchase-chart",
            "line",
            data.data.map((d) => d.month),
            [
                {
                    label: "Purchases",
                    data: data.data.map((d) => d.value),
                    borderColor: "#3b82f6",
                    backgroundColor: "rgba(59,130,246,0.1)",
                    tension: 0.4,
                    fill: true,
                },
            ],
            {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        );
    } catch (e) {
        console.error(e);
    }
}

async function loadSaleChart() {
    try {
        const data = await API.get(
            "/db/dashboard_db.php?action=get_sale_chart",
        );
        if (!data.success) return;
        makeChart(
            "sale-chart",
            "line",
            data.data.map((d) => d.month),
            [
                {
                    label: "Sales",
                    data: data.data.map((d) => d.value),
                    borderColor: "#10b981",
                    backgroundColor: "rgba(16,185,129,0.1)",
                    tension: 0.4,
                    fill: true,
                },
            ],
            {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        );
    } catch (e) {
        console.error(e);
    }
}

async function loadLowStockChart() {
    try {
        const data = await API.get(
            "/db/dashboard_db.php?action=get_low_stock_chart",
        );
        if (!data.success || !data.data.length) return;
        makeChart(
            "low-stock-chart",
            "doughnut",
            data.data.map((d) => d.label),
            [
                {
                    data: data.data.map((d) => d.value),
                    backgroundColor: CHART_COLORS,
                    borderWidth: 2,
                },
            ],
        );
    } catch (e) {
        console.error(e);
    }
}

async function loadBestSellingChart() {
    try {
        const data = await API.get(
            "/db/dashboard_db.php?action=get_best_selling_chart",
        );
        if (!data.success || !data.data.length) return;
        makeChart(
            "best-selling-chart",
            "bar",
            data.data.map((d) => d.label),
            [
                {
                    label: "Units Sold",
                    data: data.data.map((d) => d.value),
                    backgroundColor: CHART_COLORS,
                    borderRadius: 6,
                },
            ],
            {
                indexAxis: "y",
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        );
    } catch (e) {
        console.error(e);
    }
}
