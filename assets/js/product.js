class Product {
    static initProduct() {
        const MAIN_CONTENT = document.getElementById("ims__main-product");

        ElementFactory.renderModal();

        const productSect = ElementFactory.createSection();
        productSect.classList.add("mb-3");

        const productTitleRow = ElementFactory.createTitle("Products", "h3");

        const productBtnRow = ElementFactory.createRow();
        const productBtnCol = ElementFactory.createCol();
        const productBtnContainer = ElementFactory.createDiv();
        productBtnContainer.classList.add("text-end");
        const productBtn = ElementFactory.createButton("button", "Add Product");
        productBtn.classList.add("ims__add-btn", "mb-4");
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
            "No",
            "Product Code",
            "Product Name",
            "Category",
            "Supplier",
            "Description",
            "Cost Price",
            "Sale Price",
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

                    productTableRow.appendChild(productTableCol);
                    productTableCol.appendChild(productTableContainer);
                    productTableContainer.appendChild(productTable);

                    productSect.appendChild(productTitleRow);
                    productSect.appendChild(productBtnRow);
                    productSect.appendChild(productTableRow);

                    $(productTable).DataTable({
                        order: [[0, "desc"]],
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

        MAIN_CONTENT.appendChild(productSect);
    }
}
