class ElementFactory {
    static createSection = () => {
        const section = document.createElement("section");
        section.classList.add("container-fluid");

        return section;
    };

    static createRow = () => {
        const row = document.createElement("div");
        row.classList.add("row");

        return row;
    };

    static createCol = () => {
        const col = document.createElement("div");
        col.classList.add("col");

        return col;
    };

    static createTitle = (title, fontType) => {
        const row = this.createRow();
        const col = this.createCol();
        const div = this.createDiv();

        const value = document.createElement(fontType);
        value.classList.add("fw-bold");
        value.textContent = title;

        row.appendChild(col);
        col.appendChild(div);
        div.appendChild(value);

        return row;
    };

    static createDiv = () => {
        const div = document.createElement("div");

        return div;
    };

    static createParagraph = (paragraph) => {
        const p = document.createElement("p");
        p.textContent = paragraph;

        return p;
    };

    static createButton = (type, value) => {
        const btn = document.createElement("button");
        btn.type = type;
        btn.innerHTML = value;

        return btn;
    };

    static createExportButton = (chartContentContainer, data) => {
        const actionBtn = document.createElement("button");
        actionBtn.type = "button";
        actionBtn.title = "export_btn";
        actionBtn.classList.add(
            "dashboard__chart-action-btn",
            "position-absolute",
            "border",
            "border-0",
            "bg-inherit",
            "text-secondary"
        );

        const icon = this.createIcon("fa-ellipsis");

        actionBtn.appendChild(icon);

        const actionContainer = this.createDiv();
        actionContainer.classList.add(
            "dashboard__action-container",
            "position-absolute",
            "border",
            "rounded",
            "py-2",
            "bg-white",
            "d-none"
        );
        const exportBtnWrapper = this.createDiv();
        exportBtnWrapper.classList.add(
            "dashboard__export-btn",
            "w-100",
            "py-2",
            "px-3",
            "d-flex",
            "gap-2"
        );
        const exportBtnText = this.createParagraph("Export Data");
        exportBtnText.classList.add("m-0");
        const exportBtnIcon = this.createIcon("fa-download");

        chartContentContainer.appendChild(actionContainer);
        actionContainer.appendChild(exportBtnWrapper);
        exportBtnWrapper.appendChild(exportBtnText);
        exportBtnWrapper.appendChild(exportBtnIcon);

        actionBtn.addEventListener("click", () => {
            actionContainer.classList.toggle("d-none");
            actionContainer.classList.toggle("d-block");
        });

        exportBtnWrapper.addEventListener("click", () => {
            this.exportData(data);

            actionContainer.classList.toggle("d-none");
        });

        return actionBtn;
    };

    static exportData = (data) => {
        // Create a new workbook
        const wb = XLSX.utils.book_new();

        // Convert each array of data to a worksheet
        const ws = XLSX.utils.json_to_sheet(data);

        // Add the worksheet to the workbook
        XLSX.utils.book_append_sheet(wb, ws, "Exported Data");

        // Save the workbook as an Excel file
        XLSX.writeFile(wb, "exported_data.xlsx");
    };

    static createInput = (
        labelText,
        inputType,
        inputName,
        inputValue,
        disabled
    ) => {
        const label = document.createElement("label");
        label.classList.add("fw-bold");
        label.textContent = labelText;

        const input = document.createElement("input");
        input.classList.add(
            "ims__input-field",
            "px-2",
            "py-1",
            "w-100",
            "rounded"
        );
        input.type = inputType;
        input.name = inputName;
        input.value = inputValue;
        input.disabled = disabled;
        if (inputType == "number") {
            input.min = 1;
        }
        if (inputName == "price") {
            input.setAttribute("step", "any");
        }

        const wrapper = document.createElement("div");
        wrapper.classList.add("mb-3");
        const wrapperRow = this.createRow();
        const wrapperCol1 = this.createCol();
        wrapperCol1.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-3",
            "col-lg-3",
            "col-xl-3",
            "col-xxl-3"
        );
        const wrapperCol2 = this.createCol();
        wrapperCol2.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-9",
            "col-lg-9",
            "col-xl-9",
            "col-xxl-9"
        );
        wrapper.appendChild(wrapperRow);
        wrapperRow.appendChild(wrapperCol1);
        wrapperRow.appendChild(wrapperCol2);
        wrapperCol1.appendChild(label);
        wrapperCol2.appendChild(input);

        return wrapper;
    };

    static createDropdown = (
        labelText,
        options,
        idPropertyName,
        namePropertyName,
        disabled,
        selectedID
    ) => {
        const label = document.createElement("label");
        label.classList.add("fw-bold");
        label.textContent = labelText;

        const select = document.createElement("select");
        select.classList.add(
            "ims__input-field",
            "px-2",
            "py-1",
            "w-100",
            "rounded"
        );
        select.name = idPropertyName;
        select.disabled = disabled;

        options.forEach((option) => {
            const optionElement = document.createElement("option");
            optionElement.value = option[idPropertyName];
            optionElement.textContent = option[namePropertyName];

            if (option[idPropertyName] === selectedID) {
                optionElement.selected = true;
            }

            select.appendChild(optionElement);
        });

        const wrapper = document.createElement("div");
        wrapper.classList.add("mb-3");
        const wrapperRow = this.createRow();
        const wrapperCol1 = this.createCol();
        wrapperCol1.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-3",
            "col-lg-3",
            "col-xl-3",
            "col-xxl-3"
        );
        const wrapperCol2 = this.createCol();
        wrapperCol2.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-9",
            "col-lg-9",
            "col-xl-9",
            "col-xxl-9"
        );
        wrapper.appendChild(wrapperRow);
        wrapperRow.appendChild(wrapperCol1);
        wrapperRow.appendChild(wrapperCol2);
        wrapperCol1.appendChild(label);
        wrapperCol2.appendChild(select);

        return wrapper;
    };

    static createLabel = (value) => {
        const label = document.createElement("label");
        label.classList.add("fw-bold");
        label.textContent = value;

        return label;
    };

    static createForm = () => {
        const form = document.createElement("form");
        form.setAttribute("enctype", "multipart/form-data");

        return form;
    };

    static createImage = (src, alt) => {
        const img = document.createElement("img");
        img.src = src;
        img.alt = alt;

        return img;
    };

    static createIcon = (icon) => {
        const div = document.createElement("div");

        const iconElement = document.createElement("i");
        iconElement.classList.add("fa-solid", icon);

        div.appendChild(iconElement);

        return div;
    };

    static createTable = (id) => {
        const table = document.createElement("table");
        table.id = id;

        return table;
    };

    static createTableHeader = (table, labels) => {
        const tableHeaderRow = table.createTHead().insertRow();

        labels.forEach((label) => {
            const th = document.createElement("th");
            th.textContent = label;
            tableHeaderRow.appendChild(th);
        });

        return tableHeaderRow;
    };

    static createTableBody = (tableType, table, responses) => {
        const tableBody = table.createTBody();

        if (tableType == "product") {
            let counter = 1;

            responses.forEach((response) => {
                const row = tableBody.insertRow();

                // Insert the counter in the first cell
                const counterCell = row.insertCell();
                counterCell.textContent = counter++;

                // Define the order of keys
                const keysOrder = [
                    "product_code",
                    "product_name",
                    "category_name",
                    "supplier_name",
                    "description",
                    "cost_price",
                    "sale_price",
                    "quantity",
                ];

                keysOrder.forEach((key) => {
                    const cell = row.insertCell();
                    cell.textContent = response[key];
                });

                const actionCell = row.insertCell();

                const viewDetailButton = this.createButton(
                    "button",
                    "View Detail"
                );
                viewDetailButton.classList.add("ims__view-detail-btn");
                viewDetailButton.setAttribute("data-bs-toggle", "modal");
                viewDetailButton.setAttribute("data-bs-target", "#ims__modal");

                viewDetailButton.addEventListener("click", () => {
                    this.showModal("Detail", tableType, response);
                });

                actionCell.appendChild(viewDetailButton);
            });
        } else if (tableType == "transaction") {
            let counter = 1;

            responses.forEach((response) => {
                const row = tableBody.insertRow();

                // Insert the counter in the first cell
                const counterCell = row.insertCell();
                counterCell.textContent = counter++;

                // Define the order of keys
                const keysOrder = [
                    "transaction_code",
                    "product_code",
                    "product_name",
                    "type_name",
                    "quantity",
                    "unit_price",
                    "created",
                ];

                keysOrder.forEach((key) => {
                    const cell = row.insertCell();
                    cell.textContent = response[key];
                });

                const actionCell = row.insertCell();

                const viewDetailButton = this.createButton(
                    "button",
                    "View Detail"
                );
                viewDetailButton.classList.add("ims__view-detail-btn");
                viewDetailButton.setAttribute("data-bs-toggle", "modal");
                viewDetailButton.setAttribute("data-bs-target", "#ims__modal");

                response.no = counter - 1;
                viewDetailButton.addEventListener("click", () => {
                    this.showModal("Detail", tableType, response);
                });

                actionCell.appendChild(viewDetailButton);
            });
        } else if (tableType == "supplier") {
            let counter = 1;

            responses.forEach((response) => {
                const row = tableBody.insertRow();

                // Insert the counter in the first cell
                const counterCell = row.insertCell();
                counterCell.textContent = counter++;

                // Define the order of keys
                const keysOrder = [
                    "supplier_code",
                    "supplier_name",
                    "contact_person",
                    "contact_no",
                    "email",
                    "location",
                ];

                keysOrder.forEach((key) => {
                    const cell = row.insertCell();
                    cell.textContent = response[key];
                });

                const actionCell = row.insertCell();

                const viewDetailButton = this.createButton(
                    "button",
                    "View Detail"
                );
                viewDetailButton.classList.add("ims__view-detail-btn");
                viewDetailButton.setAttribute("data-bs-toggle", "modal");
                viewDetailButton.setAttribute("data-bs-target", "#ims__modal");

                response.no = counter - 1;
                viewDetailButton.addEventListener("click", () => {
                    this.showModal("Detail", tableType, response);
                });

                actionCell.appendChild(viewDetailButton);
            });
        }

        return tableBody;
    };

    static createOverviewContent = (contents, overviewContentRow) => {
        contents.forEach((content) => {
            const overviewContentCol = this.createCol();
            const overviewContentWrapper = this.createDiv();
            overviewContentWrapper.classList.add("bg-white", "rounded", "p-3");
            overviewContentCol.appendChild(overviewContentWrapper);

            const titleRow = this.createTitle(content.title, "p");
            titleRow.classList.add(
                "ims__overview-content-title",
                "text-secondary"
            );

            const dataRow = this.createRow();
            const dataCol = this.createCol();
            dataRow.appendChild(dataCol);

            const valueContainer = this.createDiv();
            const value = this.createParagraph("0");
            value.classList.add("ims__overview-content-value", "m-0");
            dataCol.appendChild(valueContainer);
            valueContainer.appendChild(value);

            overviewContentWrapper.appendChild(titleRow);
            overviewContentWrapper.appendChild(dataRow);

            overviewContentRow.appendChild(overviewContentCol);

            this.animateCountingEffect(value, content.value);
        });
    };

    static animateCountingEffect = (element, finalValue, duration = 2000) => {
        const initialValue = 0;
        const increment = finalValue / (duration / 16); // 16ms is approximately the frame duration for 60fps

        let currentVal = initialValue;

        const updateValue = () => {
            currentVal += increment;
            element.textContent = Math.round(currentVal);

            if (currentVal < finalValue) {
                requestAnimationFrame(updateValue);
            } else {
                // Ensure the final value is set
                element.textContent = finalValue;
            }
        };

        updateValue();
    };

    static createCanvas = (id) => {
        const canvas = document.createElement("canvas");
        canvas.id = id;

        return canvas;
    };

    static createProductChart = (title, id, data) => {
        const chartContainer = this.createCol();
        chartContainer.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-12",
            "col-lg-12",
            "col-xl-6",
            "col-xxl-6"
        );

        const chartContentRow = this.createRow();
        const chartContentCol = this.createCol();
        const chartContentContainer = this.createDiv();
        chartContentContainer.classList.add(
            "dashboard__chart-content",
            "p-3",
            "position-relative"
        );

        const exportButton = this.createExportButton(
            chartContentContainer,
            data
        );

        const chartCanvas = this.createCanvas(id);

        chartContentRow.appendChild(chartContentCol);
        chartContentCol.appendChild(chartContentContainer);
        chartContentContainer.appendChild(exportButton);
        chartContentContainer.appendChild(chartCanvas);

        chartContainer.appendChild(chartContentRow);

        const categories = data.map((entry) => entry.category_name);
        const values = data.map((entry) => parseFloat(entry.value));

        const chartData = {
            labels: categories,
            datasets: [
                {
                    label: "Total products",
                    data: values,
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.5)",
                        "rgba(54, 162, 235, 0.5)",
                        "rgba(255, 206, 86, 0.5)",
                        "rgba(75, 192, 192, 0.5)",
                        "rgba(153, 102, 255, 0.5)",
                        "rgba(255, 159, 64, 0.5)",
                    ],
                    borderColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(255, 206, 86, 1)",
                        "rgba(75, 192, 192, 1)",
                        "rgba(153, 102, 255, 1)",
                        "rgba(255, 159, 64, 1)",
                    ],
                    borderWidth: 1,
                },
            ],
        };

        const chartOptions = {
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: "Total product over categories",
                    align: "start",
                    padding: {
                        bottom: 30,
                    },
                    font: {
                        size: 20,
                    },
                },
            },
        };

        this.initializeChart(chartCanvas, chartData, "bar", chartOptions);

        return chartContainer;
    };

    static createPurchaseChart = (title, id, data) => {
        const dummyData = [
            {
                month: "January",
                purchase: "85500.75",
            },
            {
                month: "February",
                purchase: "68250.50",
            },
            {
                month: "March",
                purchase: "72890.25",
            },
            {
                month: "April",
                purchase: "99760.80",
            },
            {
                month: "May",
                purchase: "81567.20",
            },
            {
                month: "June",
                purchase: "74680.10",
            },
            {
                month: "July",
                purchase: "87520.45",
            },
            {
                month: "August",
                purchase: "90430.60",
            },
            {
                month: "September",
                purchase: "128890.75",
            },
            {
                month: "October",
                purchase: "80670.30",
            },
            {
                month: "November",
                purchase: "109500.15",
            },
            {
                month: "December",
                purchase: "146116.00",
            },
        ];

        const chartContainer = this.createCol();
        chartContainer.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-12",
            "col-lg-12",
            "col-xl-6",
            "col-xxl-6"
        );

        const chartContentRow = this.createRow();
        const chartContentCol = this.createCol();
        const chartContentContainer = this.createDiv();
        chartContentContainer.classList.add(
            "dashboard__chart-content",
            "p-3",
            "position-relative"
        );

        // Putting dummy data
        const exportButton = this.createExportButton(
            chartContentContainer,
            dummyData
        );

        const chartCanvas = this.createCanvas(id);

        chartContentRow.appendChild(chartContentCol);
        chartContentCol.appendChild(chartContentContainer);
        chartContentContainer.appendChild(exportButton);
        chartContentContainer.appendChild(chartCanvas);

        chartContainer.appendChild(chartContentRow);

        // Putting dummy data
        const months = dummyData.map((entry) => entry.month);
        const purchaseValues = dummyData.map((entry) =>
            parseFloat(entry.purchase)
        );

        const chartData = {
            labels: months,
            datasets: [
                {
                    label: "Purchase",
                    data: purchaseValues,
                    borderColor: "rgba(255, 99, 132, 1)",
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    borderWidth: 1,
                    fill: false,
                },
            ],
        };

        const chartOptions = {
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: "Purchase over months",
                    align: "start",
                    padding: {
                        bottom: 30,
                    },
                    font: {
                        size: 20,
                    },
                },
            },
        };

        this.initializeChart(chartCanvas, chartData, "line", chartOptions);

        return chartContainer;
    };

    static createSaleChart = (title, id, data) => {
        const dummyData = [
            {
                month: "January",
                sale: "35500.75",
            },
            {
                month: "February",
                sale: "38250.50",
            },
            {
                month: "March",
                sale: "62890.25",
            },
            {
                month: "April",
                sale: "39760.80",
            },
            {
                month: "May",
                sale: "41567.20",
            },
            {
                month: "June",
                sale: "54680.10",
            },
            {
                month: "July",
                sale: "47520.45",
            },
            {
                month: "August",
                sale: "46430.60",
            },
            {
                month: "September",
                sale: "48890.75",
            },
            {
                month: "October",
                sale: "50670.30",
            },
            {
                month: "November",
                sale: "49500.15",
            },
            {
                month: "December",
                sale: "40065.49",
            },
        ];

        const chartContainer = this.createCol();
        chartContainer.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-12",
            "col-lg-12",
            "col-xl-6",
            "col-xxl-6"
        );

        const chartContentRow = this.createRow();
        const chartContentCol = this.createCol();
        const chartContentContainer = this.createDiv();
        chartContentContainer.classList.add(
            "dashboard__chart-content",
            "p-3",
            "position-relative"
        );

        // Putting dummy data
        const exportButton = this.createExportButton(
            chartContentContainer,
            dummyData
        );

        const chartCanvas = this.createCanvas(id);

        chartContentRow.appendChild(chartContentCol);
        chartContentCol.appendChild(chartContentContainer);
        chartContentContainer.appendChild(exportButton);
        chartContentContainer.appendChild(chartCanvas);

        chartContainer.appendChild(chartContentRow);

        // Putting dummy data
        const months = dummyData.map((entry) => entry.month);
        const saleValues = dummyData.map((entry) => parseFloat(entry.sale));

        const chartData = {
            labels: months,
            datasets: [
                {
                    label: "Sale",
                    data: saleValues,
                    borderColor: "rgba(255, 96, 23, 1)",
                    backgroundColor: "rgba(255, 96, 23, 0.2)",
                    borderWidth: 1,
                    fill: false,
                },
            ],
        };

        const chartOptions = {
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: "Sale over months",
                    align: "start",
                    padding: {
                        bottom: 30,
                    },
                    font: {
                        size: 20,
                    },
                },
            },
        };

        this.initializeChart(chartCanvas, chartData, "line", chartOptions);

        return chartContainer;
    };

    static createRevenueProfitChart = (title, id, data) => {
        const dummyData = [
            {
                month: "January",
                revenue: "35500.75",
                profit: "6123.45",
            },
            {
                month: "February",
                revenue: "38250.50",
                profit: "7245.60",
            },
            {
                month: "March",
                revenue: "62890.25",
                profit: "12345.30",
            },
            {
                month: "April",
                revenue: "39760.80",
                profit: "7456.75",
            },
            {
                month: "May",
                revenue: "41567.20",
                profit: "7634.90",
            },
            {
                month: "June",
                revenue: "54680.10",
                profit: "20056.20",
            },
            {
                month: "July",
                revenue: "47520.45",
                profit: "9345.75",
            },
            {
                month: "August",
                revenue: "46430.60",
                profit: "8776.40",
            },
            {
                month: "September",
                revenue: "48890.75",
                profit: "9234.60",
            },
            {
                month: "October",
                revenue: "50670.30",
                profit: "10123.50",
            },
            {
                month: "November",
                revenue: "49500.15",
                profit: "9789.25",
            },
            {
                month: "December",
                revenue: "40065.49",
                profit: "7760.00",
            },
        ];

        const chartContainer = this.createCol();
        chartContainer.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-12",
            "col-lg-12",
            "col-xl-6",
            "col-xxl-6"
        );

        const chartContentRow = this.createRow();
        const chartContentCol = this.createCol();
        const chartContentContainer = this.createDiv();
        chartContentContainer.classList.add(
            "dashboard__chart-content",
            "p-3",
            "position-relative"
        );

        // Putting dummy data
        const exportButton = this.createExportButton(
            chartContentContainer,
            dummyData
        );

        const chartCanvas = this.createCanvas(id);

        chartContentRow.appendChild(chartContentCol);
        chartContentCol.appendChild(chartContentContainer);
        chartContentContainer.appendChild(exportButton);
        chartContentContainer.appendChild(chartCanvas);

        chartContainer.appendChild(chartContentRow);

        // Putting dummy data
        const months = dummyData.map((entry) => entry.month);
        const revenueValues = dummyData.map((entry) =>
            parseFloat(entry.revenue)
        );
        const profitValues = dummyData.map((entry) => parseFloat(entry.profit));

        const chartData = {
            labels: months,
            datasets: [
                {
                    label: "Revenue",
                    data: revenueValues,
                    borderColor: "rgba(255, 96, 23, 1)",
                    backgroundColor: "rgba(255, 96, 23, 0.2)",
                    borderWidth: 1,
                    fill: false,
                },
                {
                    label: "Profit",
                    data: profitValues,
                    borderColor: "rgba(73, 237, 47, 1)",
                    backgroundColor: "rgba(73, 237, 47, 0.2)",
                    borderWidth: 1,
                    fill: false,
                },
            ],
        };

        const chartOptions = {
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                },
            },
            plugins: {
                legend: {
                    display: true,
                },
                title: {
                    display: true,
                    text: "Revenue and Profit over months",
                    align: "start",
                    padding: {
                        bottom: 30,
                    },
                    font: {
                        size: 20,
                    },
                },
            },
        };

        this.initializeChart(chartCanvas, chartData, "line", chartOptions);

        return chartContainer;
    };

    static createLowStockChart = (title, id, data) => {
        const chartContainer = this.createCol();
        chartContainer.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-12",
            "col-lg-12",
            "col-xl-6",
            "col-xxl-6"
        );

        const chartContentRow = this.createRow();
        const chartContentCol = this.createCol();
        const chartContentContainer = this.createDiv();
        chartContentContainer.classList.add(
            "dashboard__chart-content",
            "p-3",
            "position-relative"
        );

        const exportButton = this.createExportButton(
            chartContentContainer,
            data
        );

        const chartCanvas = this.createCanvas(id);

        chartContentRow.appendChild(chartContentCol);
        chartContentCol.appendChild(chartContentContainer);
        chartContentContainer.appendChild(exportButton);
        chartContentContainer.appendChild(chartCanvas);

        chartContainer.appendChild(chartContentRow);

        const labels = data.map((entry) => entry.product_name);
        const values = data.map((entry) => parseInt(entry.quantity));

        const chartData = {
            labels: labels,
            datasets: [
                {
                    data: values,
                    borderColor: "rgba(247, 244, 35, 1)",
                    backgroundColor: "rgba(247, 244, 35, 0.8)",
                    borderWidth: 1,
                },
            ],
        };

        const chartOptions = {
            scales: {
                x: {
                    beginAtZero: true,
                },
                y: {
                    beginAtZero: true,
                },
            },
            indexAxis: "y",
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: "Low stock products",
                    align: "start",
                    padding: {
                        bottom: 30,
                    },
                    font: {
                        size: 20,
                    },
                },
            },
        };

        this.initializeChart(chartCanvas, chartData, "bar", chartOptions);

        return chartContainer;
    };

    static createBestSellingChart = (title, id, data) => {
        const chartContainer = this.createCol();
        chartContainer.classList.add(
            "col-12",
            "col-sm-12",
            "col-md-12",
            "col-lg-12",
            "col-xl-6",
            "col-xxl-6"
        );

        const chartContentRow = this.createRow();
        const chartContentCol = this.createCol();
        const chartContentContainer = this.createDiv();
        chartContentContainer.classList.add(
            "dashboard__chart-content",
            "p-3",
            "position-relative"
        );

        const exportButton = this.createExportButton(
            chartContentContainer,
            data
        );

        const chartCanvas = this.createCanvas(id);

        chartContentRow.appendChild(chartContentCol);
        chartContentCol.appendChild(chartContentContainer);
        chartContentContainer.appendChild(exportButton);
        chartContentContainer.appendChild(chartCanvas);

        chartContainer.appendChild(chartContentRow);

        const labels = data.map((entry) => entry.product_name);
        const values = data.map((entry) => parseInt(entry.quantity_sold));

        // Find the index of the maximum value in the 'values' array
        const maxIndex = values.indexOf(Math.max(...values));

        // Create an array with the same length as 'values' with zeros, except for the maximum value which has a non-zero offset
        const offsets = values.map((value, index) =>
            index === maxIndex ? 50 : 0
        );

        const chartData = {
            labels: labels,
            datasets: [
                {
                    data: values,
                    backgroundColor: [
                        "rgba(255, 99, 132, 0.5)",
                        "rgba(54, 162, 235, 0.5)",
                        "rgba(255, 206, 86, 0.5)",
                        "rgba(75, 192, 192, 0.5)",
                        "rgba(153, 102, 255, 0.5)",
                        "rgba(255, 159, 64, 0.5)",
                    ],
                    borderColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(255, 206, 86, 1)",
                        "rgba(75, 192, 192, 1)",
                        "rgba(153, 102, 255, 1)",
                        "rgba(255, 159, 64, 1)",
                    ],
                    borderWidth: 1,
                    offset: offsets,
                },
            ],
        };

        const chartOptions = {
            legend: {
                display: true,
                position: "right",
            },
            plugins: {
                title: {
                    display: true,
                    text: "Best-selling products",
                    align: "start",
                    padding: {
                        bottom: 30,
                    },
                    font: {
                        size: 20,
                    },
                },
            },
        };

        this.initializeChart(chartCanvas, chartData, "pie", chartOptions);

        return chartContainer;
    };

    static initializeChart(canvas, data, type, options) {
        const ctx = canvas.getContext("2d");
        new Chart(ctx, {
            type: type,
            data: data,
            options: options,
        });
    }

    static showModal = (actionType, tableType, response) => {
        const modalHeader = document.getElementById("ims__modal-header");
        const modalBody = document.getElementById("ims__modal-body");

        modalHeader.innerHTML = "";
        modalBody.innerHTML = "";

        const modalType =
            tableType.charAt(0).toUpperCase() + tableType.slice(1);
        if (actionType == "Add") {
            this.renderModalHeader(modalHeader, `New ${modalType}`);
        } else if (actionType == "Detail") {
            this.renderModalHeader(modalHeader, `${modalType} ${actionType}`);
        }

        this.renderModalBody(actionType, tableType, response, modalBody);
    };

    static renderModal = () => {
        var modalContainer = this.createDiv();
        modalContainer.classList.add("modal", "fade");
        modalContainer.id = "ims__modal";
        modalContainer.tabIndex = "-1";

        var modalDialog = this.createDiv();
        modalDialog.classList.add(
            "modal-dialog",
            "modal-lg",
            "modal-dialog-centered",
            "modal-dialog-scrollable"
        );

        var modalContent = this.createDiv();
        modalContent.classList.add("modal-content");

        var modalHeader = this.createDiv();
        modalHeader.id = "ims__modal-header";
        modalHeader.classList.add("modal-header");

        var modalBody = this.createDiv();
        modalBody.id = "ims__modal-body";
        modalBody.classList.add("modal-body");

        modalContent.appendChild(modalHeader);
        modalContent.appendChild(modalBody);

        modalDialog.appendChild(modalContent);

        modalContainer.appendChild(modalDialog);

        document.body.appendChild(modalContainer);
    };

    static renderModalHeader = (modalHeader, title) => {
        const modalTitle = document.createElement("h1");
        modalTitle.classList.add("modal-title", "fw-bold", "fs-4");
        modalTitle.textContent = title;

        const closeButton = this.createButton("button", "");
        closeButton.classList.add("btn-close");
        closeButton.setAttribute("data-bs-dismiss", "modal");
        closeButton.setAttribute("aria-label", "Close");

        modalHeader.appendChild(modalTitle);
        modalHeader.appendChild(closeButton);
    };

    static renderModalBody = (actionType, tableType, response, modalBody) => {
        const formContainer = this.createSection();
        const formContainerRow = this.createRow();
        const formContainerCol = this.createCol();
        const form = this.createForm();

        if (tableType == "product") {
            switch (actionType) {
                case "Detail":
                    this.renderProductDetailForm(form, response);
                    break;
                case "Add":
                    this.renderProductAddForm(form, tableType);
                    break;
            }
        } else if (tableType == "transaction") {
            switch (actionType) {
                case "Detail":
                    this.renderTransactionDetailForm(form, response);
                    break;
                case "Add":
                    this.renderTransactionAddForm(form, tableType);
                    break;
            }
        } else if (tableType == "supplier") {
            switch (actionType) {
                case "Detail":
                    this.renderSupplierDetailForm(form, response);
                    break;
                case "Add":
                    this.renderSupplierAddForm(form, tableType);
                    break;
            }
        }

        modalBody.appendChild(formContainer);
        formContainer.appendChild(formContainerRow);
        formContainerRow.appendChild(formContainerCol);
        formContainerCol.appendChild(form);
    };

    static renderProductDetailForm = (form, response) => {
        const formRow = this.createRow();
        const formCol1 = this.createCol();
        const formCol2 = this.createCol();
        form.appendChild(formRow);
        formRow.appendChild(formCol1);
        formRow.appendChild(formCol2);

        fetch(`db/product_db.php?product_id=${response.product_id}`, {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const {
                        category_id,
                        cost_price,
                        description,
                        product_code,
                        product_id,
                        product_name,
                        quantity,
                        sale_price,
                        supplier_id,
                        img_path,
                    } = data.product;

                    const productImgSrc = img_path;
                    const productImgContainer = this.createDiv();
                    productImgContainer.classList.add("text-center");
                    const productImg = this.createImage(
                        productImgSrc,
                        "Product Image"
                    );
                    productImg.classList.add("ims__product-img");

                    productImgContainer.appendChild(productImg);
                    formCol1.appendChild(productImgContainer);

                    const categoryPromise = fetch("db/category_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const categories = data.categories;
                                const selectedCategoryId = category_id;

                                const categoryDropdown = this.createDropdown(
                                    "Category:",
                                    categories,
                                    "category_id",
                                    "category_name",
                                    true,
                                    selectedCategoryId
                                );

                                formCol2.appendChild(categoryDropdown);
                            } else {
                                console.error(
                                    "Error fetching category data: ",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching category data: ",
                                error
                            );
                        });

                    const supplierPromise = fetch("db/supplier_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const suppliers = data.suppliers;
                                const selectedSupplierId = supplier_id;

                                const supplierDropdown = this.createDropdown(
                                    "Supplier:",
                                    suppliers,
                                    "supplier_id",
                                    "supplier_name",
                                    true,
                                    selectedSupplierId
                                );

                                formCol2.appendChild(supplierDropdown);
                            } else {
                                console.error(
                                    "Error fetching supplier data: ",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching supplier data: ",
                                error
                            );
                        });

                    const productCodeInput = this.createInput(
                        "Product Code:",
                        "text",
                        "product_code",
                        product_code,
                        true
                    );
                    const productNameInput = this.createInput(
                        "Product Name:",
                        "text",
                        "product_name",
                        product_name,
                        true
                    );
                    const descriptionInput = this.createInput(
                        "Description:",
                        "text",
                        "description",
                        description,
                        true
                    );
                    const costPriceInput = this.createInput(
                        "Cost Price:",
                        "number",
                        "cost_price",
                        cost_price,
                        true
                    );
                    const salePriceInput = this.createInput(
                        "Sale Price:",
                        "number",
                        "sale_price",
                        sale_price,
                        true
                    );

                    const quantityInputContainer = this.createDiv();
                    quantityInputContainer.classList.add("mb-3");
                    const wrapperRow = this.createRow();
                    const wrapperCol1 = this.createCol();
                    wrapperCol1.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-3",
                        "col-lg-3",
                        "col-xl-3",
                        "col-xxl-3"
                    );
                    const wrapperCol2 = this.createCol();
                    wrapperCol2.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-9",
                        "col-lg-9",
                        "col-xl-9",
                        "col-xxl-9"
                    );
                    const quantityInputLabel = this.createLabel("Quantity:");
                    const quantityInput = this.createParagraph(quantity);
                    quantityInputContainer.appendChild(wrapperRow);
                    wrapperRow.appendChild(wrapperCol1);
                    wrapperRow.appendChild(wrapperCol2);
                    wrapperCol1.appendChild(quantityInputLabel);
                    wrapperCol2.appendChild(quantityInput);

                    const btnWrapperRow = this.createRow();
                    const btnWrapperCol = this.createCol();
                    const btnWrapperDiv = this.createDiv();
                    btnWrapperDiv.classList.add(
                        "d-flex",
                        "flex-row",
                        "justify-content-end",
                        "gap-2"
                    );
                    const editButton = this.createButton("button", "Edit");
                    editButton.classList.add("ims__view-detail-edit-btn");
                    editButton.addEventListener("click", () => {
                        this.handleProductEditBtnEvent(
                            editButton,
                            form,
                            product_id,
                            product_code
                        );
                    });

                    const deleteButton = this.createButton("button", "Delete");
                    deleteButton.classList.add("ims__view-detail-dlt-btn");
                    deleteButton.addEventListener("click", () => {
                        this.handleProductDltBtnEvent(product_id, product_code);
                    });
                    btnWrapperRow.appendChild(btnWrapperCol);
                    btnWrapperCol.appendChild(btnWrapperDiv);
                    btnWrapperDiv.appendChild(editButton);
                    btnWrapperDiv.appendChild(deleteButton);

                    Promise.all([categoryPromise, supplierPromise]).then(() => {
                        formCol2.appendChild(productCodeInput);
                        formCol2.appendChild(productNameInput);
                        formCol2.appendChild(descriptionInput);
                        formCol2.appendChild(costPriceInput);
                        formCol2.appendChild(salePriceInput);
                        formCol2.appendChild(quantityInputContainer);

                        form.appendChild(btnWrapperRow);
                    });
                } else {
                    console.error(
                        "Error fetching product data: ",
                        data.error_msg
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching product data: ", error);
            });
    };

    static renderProductAddForm = (form, tableType) => {
        const categoryPromise = fetch("db/category_db.php", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const categories = data.categories;

                    const categoryDropdown = this.createDropdown(
                        "Category:",
                        categories,
                        "category_id",
                        "category_name",
                        false,
                        ""
                    );

                    form.appendChild(categoryDropdown);
                } else {
                    console.error(
                        "Error fetching category data: ",
                        data.error_msg
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching category data: ", error);
            });

        const supplierPromise = fetch("db/supplier_db.php", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const suppliers = data.suppliers;

                    const supplierDropdown = this.createDropdown(
                        "Supplier:",
                        suppliers,
                        "supplier_id",
                        "supplier_name",
                        false,
                        ""
                    );

                    form.appendChild(supplierDropdown);
                } else {
                    console.error(
                        "Error fetching supplier data: ",
                        data.error_msg
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching supplier data: ", error);
            });

        const productCodeInput = this.createInput(
            "Product Code:",
            "text",
            "product_code",
            "",
            false
        );
        const productNameInput = this.createInput(
            "Product Name:",
            "text",
            "product_name",
            "",
            false
        );
        const descriptionInput = this.createInput(
            "Description:",
            "text",
            "description",
            "",
            false
        );
        const costPriceInput = this.createInput(
            "Cost Price:",
            "number",
            "cost_price",
            "",
            false
        );
        const salePriceInput = this.createInput(
            "Sale Price:",
            "number",
            "sale_price",
            "",
            false
        );
        const attachmentInput = this.createInput(
            "Attachment:",
            "file",
            "attachment",
            "",
            false
        );
        attachmentInput.id = "ims__add-product-img-input";

        const submitButtonContainer = this.createDiv();
        submitButtonContainer.classList.add("text-end");
        const submitButton = this.createButton("submit", "Submit");
        submitButton.classList.add("ims__add-item-submit-btn");
        submitButtonContainer.appendChild(submitButton);

        Promise.all([categoryPromise, supplierPromise]).then(() => {
            form.appendChild(productCodeInput);
            form.appendChild(productNameInput);
            form.appendChild(descriptionInput);
            form.appendChild(costPriceInput);
            form.appendChild(salePriceInput);
            form.appendChild(attachmentInput);

            form.appendChild(submitButtonContainer);

            form.addEventListener("submit", (event) => {
                event.preventDefault();

                this.handleProductFormEvent(form, tableType);
            });
        });
    };

    static handleProductEditBtnEvent = (
        editButton,
        form,
        productId,
        productCode
    ) => {
        if (editButton.textContent === "Edit") {
            // Toggle to "Save Changes" mode
            editButton.textContent = "Save Changes";

            // Enable all input elements in the form
            const inputElements = form.querySelectorAll("input, select");
            inputElements.forEach((input) => {
                input.disabled = false;
            });
        } else {
            // Save Changes mode - Prompt user for confirmation
            const userConfirmed = window.confirm(
                "Are you sure you want to make the changes for the product detail?"
            );

            if (userConfirmed) {
                const formData = new FormData(form);
                const jsonData = {};

                formData.forEach((value, key) => {
                    jsonData[key] = value;
                });

                jsonData["product_id"] = productId;

                fetch("db/product_db.php", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(jsonData),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            console.log(data);
                            const {
                                category_name,
                                cost_price,
                                description,
                                product_code,
                                product_name,
                                sale_price,
                                supplier_name,
                            } = data.product_data;

                            const productTable = $(
                                "#ims__product-table"
                            ).DataTable();

                            // Find the row index based on the productCode
                            const rowIndex = productTable
                                .rows()
                                .eq(0)
                                .filter((rowIdx) => {
                                    return (
                                        productTable.cell(rowIdx, 1).data() ===
                                        productCode
                                    );
                                });

                            if (rowIndex.length > 0) {
                                const rowNode = productTable
                                    .row(rowIndex)
                                    .node();

                                const cellUpdates = [
                                    {
                                        index: 1,
                                        value: product_code,
                                    },
                                    {
                                        index: 2,
                                        value: product_name,
                                    },
                                    {
                                        index: 3,
                                        value: category_name,
                                    },
                                    {
                                        index: 4,
                                        value: supplier_name,
                                    },
                                    {
                                        index: 5,
                                        value: description,
                                    },
                                    {
                                        index: 6,
                                        value: cost_price,
                                    },
                                    {
                                        index: 7,
                                        value: sale_price,
                                    },
                                ];

                                // Update each specified cell in the row
                                cellUpdates.forEach(({ index, value }) => {
                                    productTable
                                        .cell(rowNode, index)
                                        .data(value);
                                });

                                productTable.draw();
                            }

                            $("#ims__modal").modal("hide");

                            // Reset to "Edit" mode
                            editButton.textContent = "Edit";

                            // Disable all input elements in the form
                            const inputElements =
                                form.querySelectorAll("input, select");
                            inputElements.forEach((input) => {
                                input.disabled = true;
                            });
                        } else {
                            console.error(
                                "Error updating product:",
                                data.error_msg
                            );
                        }
                    })
                    .catch((error) => {
                        console.error("Error updating product:", error);
                    });
            }
        }
    };

    static handleProductDltBtnEvent = (productId, productCode) => {
        const userConfirmed = window.confirm(
            "Are you sure you want to delete this product?"
        );

        if (userConfirmed) {
            fetch(`db/product_db.php`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ product_id: productId }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        const productTable = $(
                            "#ims__product-table"
                        ).DataTable();

                        // Find the row in DataTable based on the productCode and remove it
                        const rowIndex = productTable
                            .rows()
                            .eq(0)
                            .filter((rowIdx) => {
                                return (
                                    productTable.cell(rowIdx, 1).data() ===
                                    productCode
                                );
                            });

                        if (rowIndex.length > 0) {
                            productTable.row(rowIndex).remove().draw();
                        }

                        $("#ims__modal").modal("hide");
                    } else {
                        console.error(
                            "Error deleting product:",
                            data.error_msg
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error deleting product:", error);
                });
        }
    };

    static handleProductFormEvent = (form, tableType) => {
        const fileInput = document.querySelector(
            '#ims__add-product-img-input input[type="file"]'
        );

        const formData = new FormData(form);
        formData.append("attachment", fileInput.files[0]);

        fetch("db/product_db.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const productTable = $("#ims__product-table").DataTable();

                    const nextRowNumber = productTable.rows().count() + 1;

                    const viewDetailButton = this.createButton(
                        "button",
                        "View Detail"
                    );
                    viewDetailButton.classList.add("ims__view-detail-btn");
                    viewDetailButton.setAttribute("data-bs-toggle", "modal");
                    viewDetailButton.setAttribute(
                        "data-bs-target",
                        "#ims__modal"
                    );

                    const viewDetailButtonHTML = viewDetailButton.outerHTML;

                    const {
                        product_code,
                        product_name,
                        category_name,
                        supplier_name,
                        description,
                        cost_price,
                        sale_price,
                        quantity,
                    } = data.product_data;

                    // Add new row to the DataTable
                    productTable.row
                        .add([
                            nextRowNumber,
                            product_code,
                            product_name,
                            category_name,
                            supplier_name,
                            description,
                            cost_price,
                            sale_price,
                            quantity,
                            viewDetailButtonHTML,
                        ])
                        .draw(false);

                    const lastRowNode = productTable
                        .row(productTable.rows().count() - 1)
                        .node();

                    // Attach the event listener to the button in the last added row
                    const lastRowViewDetailButton = lastRowNode.querySelector(
                        ".ims__view-detail-btn"
                    );
                    lastRowViewDetailButton.addEventListener("click", () => {
                        this.showModal("Detail", tableType, data.product_data);
                    });

                    $("#ims__modal").modal("hide");
                } else {
                    console.error("Error adding product:", data.error_msg);
                }
            })
            .catch((error) => {
                console.error("Error adding product:", error);
            });
    };

    static renderTransactionDetailForm = (form, response) => {
        fetch(
            `db/transaction_db.php?transaction_id=${response.transaction_id}`,
            {
                method: "GET",
            }
        )
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const {
                        transaction_id,
                        transaction_code,
                        product_id,
                        product_code,
                        type_id,
                        quantity,
                        unit_price,
                        created,
                    } = data.transaction;

                    const typePromise = fetch("db/transaction_type_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const txn_types = data.txn_types;

                                const typeDropdown = this.createDropdown(
                                    "Type:",
                                    txn_types,
                                    "type_id",
                                    "type_name",
                                    true,
                                    type_id
                                );

                                form.appendChild(typeDropdown);
                            } else {
                                console.error(
                                    "Error fetching transaction type data: ",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching transaction type data: ",
                                error
                            );
                        });

                    const productPromise = fetch("db/product_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const products = data.products;

                                const productDropdown = this.createDropdown(
                                    "Product:",
                                    products,
                                    "product_id",
                                    "product_name",
                                    true,
                                    product_id
                                );

                                form.appendChild(productDropdown);
                            } else {
                                console.error(
                                    "Error fetching product data: ",
                                    data.error_msg
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error fetching product data: ",
                                error
                            );
                        });

                    const quantityInput = this.createInput(
                        "Quantity:",
                        "number",
                        "quantity",
                        quantity,
                        true
                    );

                    const txnCodeInputContainer = this.createDiv();
                    txnCodeInputContainer.classList.add("mb-3");
                    const txnCodeWrapperRow = this.createRow();
                    const txnCodeWrapperCol1 = this.createCol();
                    txnCodeWrapperCol1.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-3",
                        "col-lg-3",
                        "col-xl-3",
                        "col-xxl-3"
                    );
                    const txnCodeWrapperCol2 = this.createCol();
                    txnCodeWrapperCol2.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-9",
                        "col-lg-9",
                        "col-xl-9",
                        "col-xxl-9"
                    );
                    const txnCodeInputLabel =
                        this.createLabel("Transaction Code:");
                    const txnCodeInput = this.createParagraph(transaction_code);
                    txnCodeInputContainer.appendChild(txnCodeWrapperRow);
                    txnCodeWrapperRow.appendChild(txnCodeWrapperCol1);
                    txnCodeWrapperRow.appendChild(txnCodeWrapperCol2);
                    txnCodeWrapperCol1.appendChild(txnCodeInputLabel);
                    txnCodeWrapperCol2.appendChild(txnCodeInput);

                    const prodCodeInputContainer = this.createDiv();
                    prodCodeInputContainer.classList.add("mb-3");
                    const prodCodeWrapperRow = this.createRow();
                    const prodCodeWrapperCol1 = this.createCol();
                    prodCodeWrapperCol1.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-3",
                        "col-lg-3",
                        "col-xl-3",
                        "col-xxl-3"
                    );
                    const prodCodeWrapperCol2 = this.createCol();
                    prodCodeWrapperCol2.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-9",
                        "col-lg-9",
                        "col-xl-9",
                        "col-xxl-9"
                    );
                    const prodCodeInputLabel =
                        this.createLabel("Product Code:");
                    const prodCodeInput = this.createParagraph(product_code);
                    prodCodeInputContainer.appendChild(prodCodeWrapperRow);
                    prodCodeWrapperRow.appendChild(prodCodeWrapperCol1);
                    prodCodeWrapperRow.appendChild(prodCodeWrapperCol2);
                    prodCodeWrapperCol1.appendChild(prodCodeInputLabel);
                    prodCodeWrapperCol2.appendChild(prodCodeInput);

                    const unitPriceInputContainer = this.createDiv();
                    unitPriceInputContainer.classList.add("mb-3");
                    const unitPriceWrapperRow = this.createRow();
                    const unitPriceWrapperCol1 = this.createCol();
                    unitPriceWrapperCol1.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-3",
                        "col-lg-3",
                        "col-xl-3",
                        "col-xxl-3"
                    );
                    const unitPriceWrapperCol2 = this.createCol();
                    unitPriceWrapperCol2.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-9",
                        "col-lg-9",
                        "col-xl-9",
                        "col-xxl-9"
                    );
                    const unitPriceInputLabel = this.createLabel("Unit Price:");
                    const unitPriceInput = this.createParagraph(unit_price);
                    unitPriceInputContainer.appendChild(unitPriceWrapperRow);
                    unitPriceWrapperRow.appendChild(unitPriceWrapperCol1);
                    unitPriceWrapperRow.appendChild(unitPriceWrapperCol2);
                    unitPriceWrapperCol1.appendChild(unitPriceInputLabel);
                    unitPriceWrapperCol2.appendChild(unitPriceInput);

                    const createdInputContainer = this.createDiv();
                    createdInputContainer.classList.add("mb-3");
                    const createdInputWrapperRow = this.createRow();
                    const createdInputWrapperCol1 = this.createCol();
                    createdInputWrapperCol1.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-3",
                        "col-lg-3",
                        "col-xl-3",
                        "col-xxl-3"
                    );
                    const createdInputWrapperCol2 = this.createCol();
                    createdInputWrapperCol2.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-9",
                        "col-lg-9",
                        "col-xl-9",
                        "col-xxl-9"
                    );
                    const createdInputLabel = this.createLabel("Created:");
                    const createdInput = this.createParagraph(created);
                    createdInputContainer.appendChild(createdInputWrapperRow);
                    createdInputWrapperRow.appendChild(createdInputWrapperCol1);
                    createdInputWrapperRow.appendChild(createdInputWrapperCol2);
                    createdInputWrapperCol1.appendChild(createdInputLabel);
                    createdInputWrapperCol2.appendChild(createdInput);

                    const btnWrapperRow = this.createRow();
                    const btnWrapperCol = this.createCol();
                    const btnWrapperDiv = this.createDiv();
                    btnWrapperDiv.classList.add(
                        "d-flex",
                        "flex-row",
                        "justify-content-end",
                        "gap-2"
                    );
                    const editButton = this.createButton("button", "Edit");
                    editButton.classList.add("ims__view-detail-edit-btn");
                    editButton.addEventListener("click", () => {
                        this.handleTransactionEditBtnEvent(
                            editButton,
                            form,
                            transaction_id,
                            transaction_code
                        );
                    });

                    const deleteButton = this.createButton("button", "Delete");
                    deleteButton.classList.add("ims__view-detail-dlt-btn");
                    deleteButton.addEventListener("click", () => {
                        this.handleTransactionDltBtnEvent(
                            transaction_id,
                            transaction_code,
                            product_id
                        );
                    });
                    btnWrapperRow.appendChild(btnWrapperCol);
                    btnWrapperCol.appendChild(btnWrapperDiv);
                    btnWrapperDiv.appendChild(editButton);
                    btnWrapperDiv.appendChild(deleteButton);

                    Promise.all([typePromise, productPromise]).then(() => {
                        form.appendChild(quantityInput);
                        form.appendChild(txnCodeInputContainer);
                        form.appendChild(prodCodeInputContainer);
                        form.appendChild(unitPriceInputContainer);
                        form.appendChild(createdInputContainer);

                        form.appendChild(btnWrapperRow);
                    });
                } else {
                    console.error(
                        "Error fetching transaction data: ",
                        data.error_msg
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching transaction data: ", error);
            });
    };

    static renderTransactionAddForm = (form, tableType) => {
        const typePromise = fetch("db/transaction_type_db.php", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const txn_types = data.txn_types;

                    const typeDropdown = this.createDropdown(
                        "Type:",
                        txn_types,
                        "type_id",
                        "type_name",
                        false,
                        ""
                    );

                    form.appendChild(typeDropdown);
                } else {
                    console.error(
                        "Error fetching transaction type data: ",
                        data.error_msg
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching transaction type data: ", error);
            });

        const productPromise = fetch("db/product_db.php", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const products = data.products;

                    const productDropdown = this.createDropdown(
                        "Product:",
                        products,
                        "product_id",
                        "product_name",
                        false,
                        ""
                    );

                    form.appendChild(productDropdown);
                } else {
                    console.error(
                        "Error fetching product data: ",
                        data.error_msg
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching product data: ", error);
            });

        const quantityInput = this.createInput(
            "Quantity:",
            "number",
            "quantity",
            "",
            false
        );

        const submitButtonContainer = this.createDiv();
        submitButtonContainer.classList.add("text-end");
        const submitButton = this.createButton("submit", "Submit");
        submitButton.classList.add("ims__add-item-submit-btn");
        submitButtonContainer.appendChild(submitButton);

        Promise.all([typePromise, productPromise]).then(() => {
            form.appendChild(quantityInput);

            form.appendChild(submitButtonContainer);

            form.addEventListener("submit", (event) => {
                event.preventDefault();

                this.handleTransactionFormEvent(form, tableType);
            });
        });
    };

    static handleTransactionEditBtnEvent = (
        editButton,
        form,
        transactionId,
        transactionCode
    ) => {
        if (editButton.textContent === "Edit") {
            // Toggle to "Save Changes" mode
            editButton.textContent = "Save Changes";

            // Enable all input elements in the form
            const inputElements = form.querySelectorAll("input, select");
            inputElements.forEach((input) => {
                input.disabled = false;
            });
        } else {
            // Save Changes mode - Prompt user for confirmation
            const userConfirmed = window.confirm(
                "Are you sure you want to make the changes for the transaction detail?"
            );

            if (userConfirmed) {
                const formData = new FormData(form);
                const jsonData = {};

                formData.forEach((value, key) => {
                    jsonData[key] = value;
                });

                jsonData["transaction_id"] = transactionId;

                fetch("db/transaction_db.php", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(jsonData),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            const transactionTable = $(
                                "#ims__transaction-table"
                            ).DataTable();

                            // Find the row index based on the transaction_code
                            const rowIndex = transactionTable
                                .rows()
                                .eq(0)
                                .filter((rowIdx) => {
                                    return (
                                        transactionTable
                                            .cell(rowIdx, 1)
                                            .data() === transactionCode
                                    );
                                });

                            const {
                                product_name,
                                type_name,
                                quantity,
                                unit_price,
                            } = data.transaction_data;

                            if (rowIndex.length > 0) {
                                const rowNode = transactionTable
                                    .row(rowIndex)
                                    .node();

                                const cellUpdates = [
                                    {
                                        index: 3,
                                        value: product_name,
                                    },
                                    {
                                        index: 4,
                                        value: type_name,
                                    },
                                    {
                                        index: 5,
                                        value: quantity,
                                    },
                                    {
                                        index: 6,
                                        value: unit_price,
                                    },
                                ];

                                // Update each specified cell in the row
                                cellUpdates.forEach(({ index, value }) => {
                                    transactionTable
                                        .cell(rowNode, index)
                                        .data(value);
                                });

                                transactionTable.draw();
                            }

                            $("#ims__modal").modal("hide");

                            // Reset to "Edit" mode
                            editButton.textContent = "Edit";

                            // Disable all input elements in the form
                            const inputElements =
                                form.querySelectorAll("input, select");
                            inputElements.forEach((input) => {
                                input.disabled = true;
                            });
                        } else {
                            console.error(
                                "Error updating transaction:",
                                data.error_msg
                            );
                        }
                    })
                    .catch((error) => {
                        console.error("Error updating transaction:", error);
                    });
            }
        }
    };

    static handleTransactionDltBtnEvent = (
        transactionId,
        transactionCode,
        productId
    ) => {
        const userConfirmed = window.confirm(
            "Are you sure you want to delete this transaction?"
        );

        if (userConfirmed) {
            fetch(`db/transaction_db.php`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    transaction_id: transactionId,
                    product_id: productId,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        const transactionTable = $(
                            "#ims__transaction-table"
                        ).DataTable();

                        // Find the row in DataTable based on the transactionCode and remove it
                        const rowIndex = transactionTable
                            .rows()
                            .eq(0)
                            .filter((rowIdx) => {
                                return (
                                    transactionTable.cell(rowIdx, 1).data() ===
                                    transactionCode
                                );
                            });

                        if (rowIndex.length > 0) {
                            transactionTable.row(rowIndex).remove().draw();
                        }

                        $("#ims__modal").modal("hide");
                    } else {
                        console.error(
                            "Error deleting transaction:",
                            data.error_msg
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error deleting transaction:", error);
                });
        }
    };

    static handleTransactionFormEvent = (form, tableType) => {
        const formData = new FormData(form);

        fetch("db/transaction_db.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const transactionTable = $(
                        "#ims__transaction-table"
                    ).DataTable();

                    const nextRowNumber = transactionTable.rows().count() + 1;

                    const viewDetailButton = this.createButton(
                        "button",
                        "View Detail"
                    );
                    viewDetailButton.classList.add(
                        "btn",
                        "ims__view-detail-btn"
                    );
                    viewDetailButton.setAttribute("data-bs-toggle", "modal");
                    viewDetailButton.setAttribute(
                        "data-bs-target",
                        "#ims__modal"
                    );

                    const viewDetailButtonHTML = viewDetailButton.outerHTML;

                    const {
                        transaction_code,
                        product_code,
                        product_name,
                        type_name,
                        quantity,
                        unit_price,
                        created,
                    } = data.transaction_data;

                    // Add new row to the DataTable
                    transactionTable.row
                        .add([
                            nextRowNumber,
                            transaction_code,
                            product_code,
                            product_name,
                            type_name,
                            quantity,
                            unit_price,
                            created,
                            viewDetailButtonHTML,
                        ])
                        .draw(false);

                    const lastRowNode = transactionTable
                        .row(transactionTable.rows().count() - 1)
                        .node();

                    // Attach the event listener to the button in the last added row
                    const lastRowViewDetailButton = lastRowNode.querySelector(
                        ".ims__view-detail-btn"
                    );
                    lastRowViewDetailButton.addEventListener("click", () => {
                        this.showModal(
                            "Detail",
                            tableType,
                            data.transaction_data
                        );
                    });

                    $("#ims__modal").modal("hide");
                } else {
                    console.error("Error adding transaction:", data.error_msg);
                }
            })
            .catch((error) => {
                console.error("Error adding transaction:", error);
            });
    };

    static renderSupplierDetailForm = (form, response) => {
        fetch(`db/supplier_db.php?supplier_id=${response.supplier_id}`, {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const {
                        supplier_id,
                        supplier_code,
                        supplier_name,
                        contact_person,
                        contact_no,
                        email,
                        location,
                    } = data.supplier;

                    const supplierCodeInputContainer = this.createDiv();
                    supplierCodeInputContainer.classList.add("mb-3");
                    const supplierCodeWrapperRow = this.createRow();
                    const supplierCodeWrapperCol1 = this.createCol();
                    supplierCodeWrapperCol1.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-3",
                        "col-lg-3",
                        "col-xl-3",
                        "col-xxl-3"
                    );
                    const supplierCodeWrapperCol2 = this.createCol();
                    supplierCodeWrapperCol2.classList.add(
                        "col-12",
                        "col-sm-12",
                        "col-md-9",
                        "col-lg-9",
                        "col-xl-9",
                        "col-xxl-9"
                    );
                    const supplierCodeInputLabel =
                        this.createLabel("Supplier Code:");
                    const supplierCodeInput =
                        this.createParagraph(supplier_code);
                    supplierCodeInputContainer.appendChild(
                        supplierCodeWrapperRow
                    );
                    supplierCodeWrapperRow.appendChild(supplierCodeWrapperCol1);
                    supplierCodeWrapperRow.appendChild(supplierCodeWrapperCol2);
                    supplierCodeWrapperCol1.appendChild(supplierCodeInputLabel);
                    supplierCodeWrapperCol2.appendChild(supplierCodeInput);

                    const supplierNameInput = this.createInput(
                        "Supplier Name:",
                        "text",
                        "supplier_name",
                        supplier_name,
                        true
                    );

                    const contactPersonInput = this.createInput(
                        "Contact Person:",
                        "text",
                        "contact_person",
                        contact_person,
                        true
                    );

                    const contactNoInput = this.createInput(
                        "Contact No:",
                        "text",
                        "contact_no",
                        contact_no,
                        true
                    );

                    const emailInput = this.createInput(
                        "Email:",
                        "text",
                        "email",
                        email,
                        true
                    );

                    const locationInput = this.createInput(
                        "Location:",
                        "text",
                        "location",
                        location,
                        true
                    );

                    const btnWrapperRow = this.createRow();
                    const btnWrapperCol = this.createCol();
                    const btnWrapperDiv = this.createDiv();
                    btnWrapperDiv.classList.add(
                        "d-flex",
                        "flex-row",
                        "justify-content-end",
                        "gap-2"
                    );

                    const editButton = this.createButton("button", "Edit");
                    editButton.classList.add("ims__view-detail-edit-btn");
                    editButton.addEventListener("click", () => {
                        this.handleSupplierEditBtnEvent(
                            editButton,
                            form,
                            supplier_id,
                            supplier_code
                        );
                    });

                    const deleteButton = this.createButton("button", "Delete");
                    deleteButton.classList.add("ims__view-detail-dlt-btn");
                    deleteButton.addEventListener("click", () => {
                        this.handleSupplierDltBtnEvent(
                            supplier_id,
                            supplier_code
                        );
                    });

                    btnWrapperRow.appendChild(btnWrapperCol);
                    btnWrapperCol.appendChild(btnWrapperDiv);
                    btnWrapperDiv.appendChild(editButton);
                    btnWrapperDiv.appendChild(deleteButton);

                    form.appendChild(supplierCodeInputContainer);
                    form.appendChild(supplierNameInput);
                    form.appendChild(contactPersonInput);
                    form.appendChild(contactNoInput);
                    form.appendChild(emailInput);
                    form.appendChild(locationInput);
                    form.appendChild(btnWrapperRow);
                } else {
                    console.error(
                        "Error fetching supplier data: ",
                        data.error_msg
                    );
                }
            })
            .catch((error) => {
                console.error("Error fetching supplier data: ", error);
            });
    };

    static renderSupplierAddForm = (form, tableType) => {
        const supplierNameInput = this.createInput(
            "Supplier Name:",
            "text",
            "supplier_name",
            "",
            false
        );

        const contactPersonInput = this.createInput(
            "Contact Person:",
            "text",
            "contact_person",
            "",
            false
        );

        const contactNoInput = this.createInput(
            "Contact No:",
            "text",
            "contact_no",
            "",
            false
        );

        const emailInput = this.createInput(
            "Email:",
            "text",
            "email",
            "",
            false
        );

        const locationInput = this.createInput(
            "Location:",
            "text",
            "location",
            "",
            false
        );

        const submitButtonContainer = this.createDiv();
        submitButtonContainer.classList.add("text-end");
        const submitButton = this.createButton("submit", "Submit");
        submitButton.classList.add("ims__add-item-submit-btn");
        submitButtonContainer.appendChild(submitButton);

        form.appendChild(supplierNameInput);
        form.appendChild(contactPersonInput);
        form.appendChild(contactNoInput);
        form.appendChild(emailInput);
        form.appendChild(locationInput);
        form.appendChild(submitButtonContainer);

        form.addEventListener("submit", (event) => {
            event.preventDefault();

            this.handleSupplierFormEvent(form, tableType);
        });
    };

    static handleSupplierEditBtnEvent = (
        editButton,
        form,
        supplierId,
        supplierCode
    ) => {
        if (editButton.textContent === "Edit") {
            // Toggle to "Save Changes" mode
            editButton.textContent = "Save Changes";

            // Enable all input elements in the form
            const inputElements = form.querySelectorAll("input, select");
            inputElements.forEach((input) => {
                input.disabled = false;
            });
        } else {
            // Save Changes mode - Prompt user for confirmation
            const userConfirmed = window.confirm(
                "Are you sure you want to make the changes for the supplier detail?"
            );

            if (userConfirmed) {
                const formData = new FormData(form);
                const jsonData = {};

                formData.forEach((value, key) => {
                    jsonData[key] = value;
                });

                jsonData["supplier_id"] = supplierId;

                fetch("db/supplier_db.php", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(jsonData),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            const supplierTable = $(
                                "#ims__supplier-table"
                            ).DataTable();

                            // Find the row index based on the no
                            const rowIndex = supplierTable
                                .rows()
                                .eq(0)
                                .filter((rowIdx) => {
                                    return (
                                        supplierTable.cell(rowIdx, 1).data() ===
                                        supplierCode
                                    );
                                });

                            const {
                                supplier_name,
                                contact_person,
                                contact_no,
                                email,
                                location,
                            } = data.supplier_data;

                            if (rowIndex.length > 0) {
                                const rowNode = supplierTable
                                    .row(rowIndex)
                                    .node();

                                const cellUpdates = [
                                    {
                                        index: 2,
                                        value: supplier_name,
                                    },
                                    {
                                        index: 3,
                                        value: contact_person,
                                    },
                                    {
                                        index: 4,
                                        value: contact_no,
                                    },
                                    {
                                        index: 5,
                                        value: email,
                                    },
                                    {
                                        index: 6,
                                        value: location,
                                    },
                                ];

                                // Update each specified cell in the row
                                cellUpdates.forEach(({ index, value }) => {
                                    supplierTable
                                        .cell(rowNode, index)
                                        .data(value);
                                });

                                supplierTable.draw();
                            }

                            $("#ims__modal").modal("hide");

                            // Reset to "Edit" mode
                            editButton.textContent = "Edit";

                            // Disable all input elements in the form
                            const inputElements =
                                form.querySelectorAll("input, select");
                            inputElements.forEach((input) => {
                                input.disabled = true;
                            });
                        } else {
                            console.error(
                                "Error updating supplier:",
                                data.error_msg
                            );
                        }
                    })
                    .catch((error) => {
                        console.error("Error updating supplier:", error);
                    });
            }
        }
    };

    static handleSupplierDltBtnEvent = (supplierId, supplierCode) => {
        const userConfirmed = window.confirm(
            "Are you sure you want to delete this supplier?"
        );

        if (userConfirmed) {
            fetch(`db/supplier_db.php`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    supplier_id: supplierId,
                }),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        const supplierTable = $(
                            "#ims__supplier-table"
                        ).DataTable();

                        // Find the row in DataTable based on the supplierCode and remove it
                        const rowIndex = supplierTable
                            .rows()
                            .eq(0)
                            .filter((rowIdx) => {
                                return (
                                    supplierTable.cell(rowIdx, 1).data() ===
                                    supplierCode
                                );
                            });

                        if (rowIndex.length > 0) {
                            supplierTable.row(rowIndex).remove().draw();
                        }

                        $("#ims__modal").modal("hide");
                    } else {
                        console.error(
                            "Error deleting supplier:",
                            data.error_msg
                        );
                    }
                })
                .catch((error) => {
                    console.error("Error deleting supplier:", error);
                });
        }
    };

    static handleSupplierFormEvent = (form, tableType) => {
        const formData = new FormData(form);

        fetch("db/supplier_db.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const supplierTable = $("#ims__supplier-table").DataTable();

                    const nextRowNumber = supplierTable.rows().count() + 1;

                    const viewDetailButton = this.createButton(
                        "button",
                        "View Detail"
                    );
                    viewDetailButton.classList.add(
                        "btn",
                        "ims__view-detail-btn"
                    );
                    viewDetailButton.setAttribute("data-bs-toggle", "modal");
                    viewDetailButton.setAttribute(
                        "data-bs-target",
                        "#ims__modal"
                    );

                    const viewDetailButtonHTML = viewDetailButton.outerHTML;

                    const {
                        supplier_code,
                        supplier_name,
                        contact_person,
                        contact_no,
                        email,
                        location,
                    } = data.supplier_data;

                    // Add new row to the DataTable
                    supplierTable.row
                        .add([
                            nextRowNumber,
                            supplier_code,
                            supplier_name,
                            contact_person,
                            contact_no,
                            email,
                            location,
                            viewDetailButtonHTML,
                        ])
                        .draw(false);

                    const lastRowNode = supplierTable
                        .row(supplierTable.rows().count() - 1)
                        .node();

                    // Attach the event listener to the button in the last added row
                    const lastRowViewDetailButton = lastRowNode.querySelector(
                        ".ims__view-detail-btn"
                    );
                    lastRowViewDetailButton.addEventListener("click", () => {
                        this.showModal("Detail", tableType, data.supplier_data);
                    });

                    $("#ims__modal").modal("hide");
                } else {
                    console.error("Error adding supplier:", data.error_msg);
                }
            })
            .catch((error) => {
                console.error("Error adding supplier:", error);
            });
    };
}
