class ElementFactory {
    static createSection = () => {
        const section = document.createElement("section");
        section.classList.add("container-fluid", "mb-3");

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
        btn.classList.add("btn");
        btn.innerHTML = value;

        return btn;
    };

    static createInput = (
        labelText,
        inputType,
        inputName,
        inputValue,
        disabled
    ) => {
        const label = document.createElement("label");
        label.textContent = labelText;

        const input = document.createElement("input");
        input.type = inputType;
        input.name = inputName;
        input.value = inputValue;
        input.disabled = disabled;
        if (inputType == "number") {
            input.min = 1;
        }
        if (inputName == "price") {
            input.setAttribute("step", "any")
        }

        const wrapper = document.createElement("div");
        wrapper.appendChild(label);
        wrapper.appendChild(input);

        return wrapper;
    };

    static createForm = () => {
        const form = document.createElement("form");
        form.setAttribute("enctype", "multipart/form-data");

        return form;
    };

    static createTable = (id) => {
        const table = document.createElement("table");
        table.classList.add("table");
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
            responses.forEach((response) => {
                const row = tableBody.insertRow();

                // Define the order of keys
                const keysOrder = [
                    "product_code",
                    "product_name",
                    "category_id",
                    "supplier_id",
                    "description",
                    "price",
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
                viewDetailButton.classList.add("btn", "btn-primary");
                viewDetailButton.setAttribute("data-bs-toggle", "modal");
                viewDetailButton.setAttribute("data-bs-target", "#ims__modal");

                viewDetailButton.addEventListener("click", () => {
                    this.showModal("Detail", tableType, response);
                });

                actionCell.appendChild(viewDetailButton);
            });
        }

        return tableBody;
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
        label.textContent = labelText;

        const select = document.createElement("select");
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
        wrapper.appendChild(label);
        wrapper.appendChild(select);

        return wrapper;
    };

    static createIcon = (icon) => {
        const div = document.createElement("div");

        const iconElement = document.createElement("i");
        iconElement.classList.add("fa-solid", icon);

        div.appendChild(iconElement);

        return div;
    };

    static createCanvas = (id) => {
        const canvas = document.createElement("canvas");
        canvas.id = id;

        return canvas;
    };

    static createChartSection = (contents, chartSectCol) => {
        const chartSectRow = this.createRow();
        chartSectRow.classList.add("mb-3", "gap-3");

        contents.forEach((content) => {
            const chartContainer = this.createCol();
            chartContainer.classList.add("border", "border-black");

            const chartTitleRow = this.createTitle(content.title, "h5");

            const chartContentRow = this.createRow();
            const chartContentCol = this.createCol();
            const chartContentContainer = this.createDiv();
            const chartCanvas = this.createCanvas(content.id);
            chartContentRow.appendChild(chartContentCol);
            chartContentCol.appendChild(chartContentContainer);
            chartContentContainer.appendChild(chartCanvas);

            chartContainer.appendChild(chartTitleRow);
            chartContainer.appendChild(chartContentRow);
            chartSectRow.appendChild(chartContainer);

            const chartData = {
                labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
                datasets: [
                    {
                        label: "# of Votes",
                        data: [12, 19, 3, 5, 2, 3],
                        borderWidth: 1,
                    },
                ],
            };
            const chartOptions = {
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
            };
            this.initializeChart(
                chartCanvas,
                chartData,
                content.type,
                chartOptions
            );
        });

        chartSectCol.appendChild(chartSectRow);
    };

    static createOverviewContent = (contents, overviewContentRow) => {
        contents.forEach((content) => {
            const overviewContentCol = this.createCol();
            overviewContentCol.classList.add("border", "border-black", "p-2");

            const titleRow = this.createTitle(content.title, "p");

            const dataRow = this.createRow();
            const dataCol = this.createCol();
            const dataContainer = this.createDiv();
            dataContainer.classList.add("d-flex", "gap-3");
            dataCol.appendChild(dataContainer);
            dataRow.appendChild(dataCol);

            const valueContainer = this.createDiv();
            const value = this.createParagraph(content.value);
            dataContainer.appendChild(valueContainer);
            valueContainer.appendChild(value);

            const percentageContainer = this.createDiv();
            percentageContainer.classList.add("d-flex", "gap-1");

            const iconContainer = this.createIcon(content.icon);
            const percentageValueContainer = this.createDiv();
            const percentage = this.createParagraph(content.percentage);

            percentageContainer.appendChild(iconContainer);
            percentageContainer.appendChild(percentageValueContainer);
            percentageValueContainer.appendChild(percentage);
            dataContainer.appendChild(percentageContainer);

            overviewContentCol.appendChild(titleRow);
            overviewContentCol.appendChild(dataRow);

            overviewContentRow.appendChild(overviewContentCol);
        });
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
            this.rendalModalHeader(modalHeader, `${actionType} ${modalType}`);
        } else {
            this.rendalModalHeader(modalHeader, `${modalType} ${actionType}`);
        }

        this.rendalModalBody(actionType, tableType, response, modalBody);
    };

    static rendalModal = () => {
        var modalContainer = this.createDiv();
        modalContainer.classList.add("modal", "fade");
        modalContainer.id = "ims__modal";
        modalContainer.tabIndex = "-1";

        var modalDialog = this.createDiv();
        modalDialog.classList.add(
            "modal-dialog",
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

    static rendalModalHeader = (modalHeader, title) => {
        const modalTitle = document.createElement("h1");
        modalTitle.classList.add("modal-title", "fs-5");
        modalTitle.textContent = title;

        const closeButton = this.createButton("button", "");
        closeButton.classList.add("btn-close");
        closeButton.setAttribute("data-bs-dismiss", "modal");
        closeButton.setAttribute("aria-label", "Close");

        modalHeader.appendChild(modalTitle);
        modalHeader.appendChild(closeButton);
    };

    static rendalModalBody = (actionType, tableType, response, modalBody) => {
        const form = this.createForm();

        if (tableType == "product") {
            switch (actionType) {
                case "Detail":
                    const productCodeInput = this.createInput(
                        "Product Code:",
                        "text",
                        "product_code",
                        response.product_code,
                        true
                    );
                    const productNameInput = this.createInput(
                        "Product Name:",
                        "text",
                        "product_name",
                        response.product_name,
                        true
                    );
                    const descriptionInput = this.createInput(
                        "Description:",
                        "text",
                        "description",
                        response.description,
                        true
                    );
                    const priceInput = this.createInput(
                        "Price:",
                        "number",
                        "price",
                        response.price,
                        true
                    );
                    const quantityInput = this.createInput(
                        "Quantity:",
                        "number",
                        "quantity",
                        response.quantity,
                        true
                    );

                    const editButton = this.createButton("button", "Edit");
                    editButton.addEventListener("click", () => {
                        if (editButton.textContent === "Edit") {
                            // Toggle to "Save Changes" mode
                            editButton.textContent = "Save Changes";

                            // Enable all input elements in the form
                            const inputElements =
                                form.querySelectorAll("input, select");
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
                                
                                jsonData['product_id'] = response.product_id;

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
                                            $("#ims__modal").modal("hide");
                                        } else {
                                            console.error(
                                                "Error updating product:",
                                                data.error_msg
                                            );
                                        }
                                    })
                                    .catch((error) => {
                                        console.error(
                                            "Error updating product:",
                                            error
                                        );
                                    });

                                // Reset to "Edit" mode
                                editButton.textContent = "Edit";

                                // Disable all input elements in the form
                                const inputElements =
                                    form.querySelectorAll("input, select");
                                inputElements.forEach((input) => {
                                    input.disabled = true;
                                });
                            }
                        }
                    });

                    const deleteButton = this.createButton("button", "Delete");
                    deleteButton.addEventListener("click", () => {
                        const userConfirmed = window.confirm(
                            "Are you sure you want to delete this product?"
                        );

                        if (userConfirmed) {
                            const productId = response.product_id;

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
                                        $("#ims__modal").modal("hide");
                                    } else {
                                        console.error(
                                            "Error deleting product:",
                                            data.error_msg
                                        );
                                    }
                                })
                                .catch((error) => {
                                    console.error(
                                        "Error deleting product:",
                                        error
                                    );
                                });
                        }
                    });

                    const categoryPromise = fetch("db/category_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const categories = data.categories;
                                const selectedCategoryId = response.category_id;

                                const categoryDropdown =
                                    ElementFactory.createDropdown(
                                        "Category:",
                                        categories,
                                        "category_id",
                                        "category_name",
                                        true,
                                        selectedCategoryId
                                    );

                                form.appendChild(categoryDropdown);
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

                    const supplierPromise = fetch("db/supplier_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const suppliers = data.suppliers;
                                const selectedSupplierId = response.supplier_id;

                                const supplierDropdown =
                                    ElementFactory.createDropdown(
                                        "Supplier:",
                                        suppliers,
                                        "supplier_id",
                                        "supplier_name",
                                        true,
                                        selectedSupplierId
                                    );

                                form.appendChild(supplierDropdown);
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

                    Promise.all([categoryPromise, supplierPromise]).then(() => {
                        form.appendChild(productCodeInput);
                        form.appendChild(productNameInput);
                        form.appendChild(descriptionInput);
                        form.appendChild(priceInput);
                        form.appendChild(quantityInput);

                        form.appendChild(editButton);
                        form.appendChild(deleteButton);
                    });
                    break;
                case "Add":
                    const productCodeAddInput = this.createInput(
                        "Product Code:",
                        "text",
                        "product_code",
                        "",
                        false
                    );
                    const productNameAddInput = this.createInput(
                        "Product Name:",
                        "text",
                        "product_name",
                        "",
                        false
                    );
                    const descriptionAddInput = this.createInput(
                        "Description:",
                        "text",
                        "description",
                        "",
                        false
                    );
                    const priceAddInput = this.createInput(
                        "Price:",
                        "number",
                        "price",
                        "",
                        false
                    );
                    const quantityAddInput = this.createInput(
                        "Quantity:",
                        "number",
                        "quantity",
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

                    const categoryAddPromise = fetch("db/category_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const categories = data.categories;

                                const categoryDropdown =
                                    ElementFactory.createDropdown(
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

                    const supplierAddPromise = fetch("db/supplier_db.php", {
                        method: "GET",
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.success) {
                                const suppliers = data.suppliers;

                                const supplierDropdown =
                                    ElementFactory.createDropdown(
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

                    const submitButton = this.createButton("submit", "Submit");

                    Promise.all([categoryAddPromise, supplierAddPromise]).then(
                        () => {
                            form.appendChild(productCodeAddInput);
                            form.appendChild(productNameAddInput);
                            form.appendChild(descriptionAddInput);
                            form.appendChild(priceAddInput);
                            form.appendChild(quantityAddInput);
                            form.appendChild(attachmentInput);

                            form.appendChild(submitButton);

                            form.addEventListener("submit", (event) => {
                                event.preventDefault();

                                const fileInput = document.querySelector(
                                    '#ims__add-product-img-input input[type="file"]'
                                );
                                console.log(fileInput.files[0]);

                                const formData = new FormData(form);
                                formData.append(
                                    "attachment",
                                    fileInput.files[0]
                                );

                                for (const [key, value] of formData.entries()) {
                                    console.log(`Key: ${key}, Value: ${value}`);
                                }

                                fetch("db/product_db.php", {
                                    method: "POST",
                                    body: formData,
                                })
                                    .then((response) => response.json())
                                    .then((data) => {
                                        if (data.success) {
                                            $("#ims__modal").modal("hide");
                                        } else {
                                            console.error(
                                                "Error adding product:",
                                                data.error_msg
                                            );
                                        }
                                    })
                                    .catch((error) => {
                                        console.error(
                                            "Error adding product:",
                                            error
                                        );
                                    });
                            });
                        }
                    );
                    break;
            }
        }

        modalBody.appendChild(form);
    };
}
