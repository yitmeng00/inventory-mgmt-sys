class Product {
    static initProduct() {
        const MAIN_CONTENT = document.getElementById("ims__main-product");

        ElementFactory.rendalModal();

        const productSect = ElementFactory.createSection();

        const productTitleRow = ElementFactory.createTitle("Product", "h2");

        const productBtnRow = ElementFactory.createRow();
        const productBtnCol = ElementFactory.createCol();
        const productBtnContainer = ElementFactory.createDiv();
        productBtnContainer.classList.add("text-end");
        const productBtn = ElementFactory.createButton("Add Product");
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
            "overflow-hidden",
            "overflow-x-scroll"
        );
        const productTable = ElementFactory.createTable("ims__product-table");

        const headerLabels = [
            "Product ID",
            "Product Name",
            "Category ID",
            "Supplier ID",
            "Product Code",
            "Description",
            "Price",
            "Quantity",
            "",
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
