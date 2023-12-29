class Dashboard {
    static initDashboard() {
        const MAIN_CONTENT = document.getElementById("ims__main-dashboard");

        // Overview section
        const overviewSect = ElementFactory.createSection();
        overviewSect.classList.add("border", "border-black", "mb-3");
        const overviewSectRow = ElementFactory.createRow();
        overviewSectRow.classList.add("p-3");
        const overviewSectCol = ElementFactory.createCol();
        overviewSect.appendChild(overviewSectRow);
        overviewSectRow.appendChild(overviewSectCol);

        const overviewTitleRow = ElementFactory.createTitle("Overview", "h4");
        const overviewContentRow = ElementFactory.createRow();
        overviewContentRow.classList.add("gap-3");

        const overviewContents = [
            {
                title: "TOTAL AVAILABLE STOCKS",
                value: "7879",
                icon: null,
                percentage: null,
            },
            {
                title: "LOW IN STOCK",
                value: "3",
                icon: null,
                percentage: null,
            },
            {
                title: "OUT OF STOCK",
                value: "5",
                icon: null,
                percentage: null,
            },
            {
                title: "TOTAL PURCHASES",
                value: "7879",
                icon: "fa-caret-up",
                percentage: "3.2%",
            },
            {
                title: "TOTAL SALES",
                value: "7879",
                icon: "fa-caret-up",
                percentage: "3.2%",
            },
            {
                title: "TOTAL REVENUES",
                value: "7879",
                icon: "fa-caret-up",
                percentage: "3.2%",
            },
        ];

        ElementFactory.createOverviewContent(
            overviewContents,
            overviewContentRow
        );

        overviewSectCol.appendChild(overviewTitleRow);
        overviewSectCol.appendChild(overviewContentRow);

        // Chart Section
        const chartSect = ElementFactory.createSection();
        chartSect.classList.add("mb-3");
        const chartSectRow = ElementFactory.createRow();
        const chartSectCol = ElementFactory.createCol();
        chartSect.appendChild(chartSectRow);
        chartSectRow.appendChild(chartSectCol);

        const chartRow1Contents = [
            {
                title: "Total products",
                id: "dashboard__product-chart",
                type: "bar",
            },
            {
                title: "Month to date purchases",
                id: "dashboard__purchase-chart",
                type: "bar",
            },
        ];

        const chartRow2Contents = [
            {
                title: "Month to date sales",
                id: "dashboard__sale-chart",
                type: "line",
            },
            {
                title: "Month to date revenue",
                id: "dashboard__revenue-chart",
                type: "line",
            },
        ];

        const chartRow3Contents = [
            {
                title: "Low stock products",
                id: "dashboard__low-stock-chart",
                type: "bar",
            },
            {
                title: "Best-selling products",
                id: "dashboard__best-selling-chart",
                type: "pie",
            },
        ];

        ElementFactory.createChartSection(chartRow1Contents, chartSectCol);
        ElementFactory.createChartSection(chartRow2Contents, chartSectCol);
        ElementFactory.createChartSection(chartRow3Contents, chartSectCol);

        MAIN_CONTENT.appendChild(overviewSect);
        MAIN_CONTENT.appendChild(chartSect);
    }
}
