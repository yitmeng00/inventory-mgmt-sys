class Dashboard {
    static initDashboard() {
        const MAIN_CONTENT = document.getElementById("ims__main-dashboard");

        fetch("db/dashboard_db.php?action=get_overview", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Overview section
                    const overviewSect = ElementFactory.createSection();
                    overviewSect.classList.add("mb-3");
                    const overviewSectRow = ElementFactory.createRow();
                    const overviewSectCol = ElementFactory.createCol();
                    const overviewSectWrapper = ElementFactory.createDiv();
                    overviewSectWrapper.classList.add(
                        "ims__overview-section-container",
                        "rounded",
                        "p-4"
                    );
                    overviewSect.appendChild(overviewSectRow);
                    overviewSectRow.appendChild(overviewSectCol);
                    overviewSectCol.appendChild(overviewSectWrapper);

                    const overviewTitleRow = ElementFactory.createTitle(
                        "Overview",
                        "h5"
                    );
                    overviewTitleRow.classList.add("mb-2");
                    const overviewContentWrapperRow =
                        ElementFactory.createRow();
                    const overviewContentWrapperCol =
                        ElementFactory.createCol();
                    const overviewContentRow = ElementFactory.createRow();
                    overviewContentWrapperRow.appendChild(
                        overviewContentWrapperCol
                    );
                    overviewContentWrapperCol.appendChild(overviewContentRow);

                    ElementFactory.createOverviewContent(
                        data.overview_contents,
                        overviewContentRow
                    );

                    overviewSectWrapper.appendChild(overviewTitleRow);
                    overviewSectWrapper.appendChild(overviewContentWrapperRow);

                    // Chart Section
                    const chartSect = ElementFactory.createSection();
                    chartSect.classList.add("mb-3");
                    const chartSectRow = ElementFactory.createRow();
                    const chartSectCol = ElementFactory.createCol();
                    chartSect.appendChild(chartSectRow);
                    chartSectRow.appendChild(chartSectCol);

                    const chartRow1 = ElementFactory.createRow();
                    chartRow1.classList.add("mb-3");
                    const chartRow2 = ElementFactory.createRow();
                    chartRow2.classList.add("mb-3");
                    const chartRow3 = ElementFactory.createRow();
                    chartRow3.classList.add("mb-3");

                    fetch("db/dashboard_db.php?action=get_product_chart", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const totalProductChart =
                                    ElementFactory.createProductChart(
                                        "Total products",
                                        "dashboard__product-chart",
                                        data.product_data
                                    );

                                chartRow1.appendChild(totalProductChart);
                            } else {
                                console.error(
                                    "Error fetching product chart data:",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching product chart data:",
                                error
                            );
                        });

                    fetch(
                        "db/dashboard_db.php?action=get_revenue_profit_chart",
                        {
                            method: "GET",
                        }
                    )
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const revenuesChart =
                                    ElementFactory.createRevenueProfitChart(
                                        "Revenues and Profits",
                                        "dashboard__revenue-profit-chart",
                                        data.revenue_profit_data
                                    );

                                chartRow1.appendChild(revenuesChart);
                            } else {
                                console.error(
                                    "Error fetching product chart data:",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching product chart data:",
                                error
                            );
                        });

                    fetch("db/dashboard_db.php?action=get_purchase_chart", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const purchasesChart =
                                    ElementFactory.createPurchaseChart(
                                        "Purchases",
                                        "dashboard__purchase-chart",
                                        data.purchase_data
                                    );

                                chartRow2.appendChild(purchasesChart);
                            } else {
                                console.error(
                                    "Error fetching product chart data:",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching product chart data:",
                                error
                            );
                        });

                    fetch("db/dashboard_db.php?action=get_sale_chart", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const salesChart =
                                    ElementFactory.createSaleChart(
                                        "Sales",
                                        "dashboard__sale-chart",
                                        data.sale_data
                                    );

                                chartRow2.appendChild(salesChart);
                            } else {
                                console.error(
                                    "Error fetching product chart data:",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching product chart data:",
                                error
                            );
                        });

                    fetch("db/dashboard_db.php?action=get_low_stock_chart", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const lowStockChart =
                                    ElementFactory.createLowStockChart(
                                        "Low stock products",
                                        "dashboard__low-stock-chart",
                                        data.low_stock_data
                                    );

                                chartRow3.appendChild(lowStockChart);
                            } else {
                                console.error(
                                    "Error fetching product chart data:",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching product chart data:",
                                error
                            );
                        });

                    fetch("db/dashboard_db.php?action=get_best_selling_chart", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const bestSellingChart =
                                    ElementFactory.createBestSellingChart(
                                        "Best-selling products",
                                        "dashboard__best-selling-chart",
                                        data.best_selling_data
                                    );

                                chartRow3.appendChild(bestSellingChart);
                            } else {
                                console.error(
                                    "Error fetching product chart data:",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching product chart data:",
                                error
                            );
                        });

                    chartSectCol.appendChild(chartRow1);
                    chartSectCol.appendChild(chartRow2);
                    chartSectCol.appendChild(chartRow3);

                    MAIN_CONTENT.appendChild(overviewSect);
                    MAIN_CONTENT.appendChild(chartSect);
                } else {
                    console.error("Error fetching data:", data.error_msg);
                }
            })
            .catch((error) => {
                console.error("Error fetching data:", error);
            });
    }
}
