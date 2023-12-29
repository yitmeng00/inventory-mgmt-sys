class Product {
    static initProduct() {
        const MAIN_CONTENT = document.getElementById("ims__main-product");

        ElementFactory.rendalModal();

        const productSect = ElementFactory.createSection();
        productSect.classList.add("mb-3");

        const productTitleRow = ElementFactory.createTitle("Products", "h2");

        const productBtnRow = ElementFactory.createRow();
        const productBtnCol = ElementFactory.createCol();
        const productBtnContainer = ElementFactory.createDiv();
        productBtnContainer.classList.add("text-end");
        const productBtn = ElementFactory.createButton("button", "Add Product");
        productBtn.classList.add("btn-primary", "mb-5");
        productBtn.setAttribute("data-bs-toggle", "modal");
        productBtn.setAttribute("data-bs-target", "#ims__modal");
        productBtn.addEventListener("click", () => {
            ElementFactory.showModal("Add", "product", "");
        });

        productBtnRow.appendChild(productBtnCol);
        productBtnCol.appendChild(productBtnContainer);
        productBtnContainer.appendChild(productBtn);

        const productTableRow = ElementFactory.createRow();
        const productTableCol = ElementFactory.createCol();
        const productTableContainer = ElementFactory.createDiv();
        productTableContainer.classList.add(
            "ims__product-table-container",
            "overflow-hidden",
            "overflow-x-scroll"
        );
        const productTable = ElementFactory.createTable("ims__product-table");
        productTable.classList.add("mb-2", "overflow-hidden");

        const headerLabels = [
            "Product Code",
            "Product Name",
            "Category ID",
            "Supplier ID",
            "Description",
            "Price",
            "Quantity",
            "Action",
        ];
        ElementFactory.createTableHeader(productTable, headerLabels);

        fetch("db/product_db.php", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const products = data.products;

                    ElementFactory.createTableBody(
                        "product",
                        productTable,
                        products
                    );

                    $(productTable).DataTable();
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

        productTableRow.appendChild(productTableCol);
        productTableCol.appendChild(productTableContainer);
        productTableContainer.appendChild(productTable);

        productSect.appendChild(productTitleRow);
        productSect.appendChild(productBtnRow);
        productSect.appendChild(productTableRow);

        MAIN_CONTENT.appendChild(productSect);
    }
}
