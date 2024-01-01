class Supplier {
    static initSupplier() {
        const MAIN_CONTENT = document.getElementById("ims__main-supplier");

        ElementFactory.renderModal();

        const supplierSect = ElementFactory.createSection();
        supplierSect.classList.add("mb-3");

        const supplierTitleRow = ElementFactory.createTitle("Suppliers", "h3");

        const supplierBtnRow = ElementFactory.createRow();
        const supplierBtnCol = ElementFactory.createCol();
        const supplierBtnContainer = ElementFactory.createDiv();
        supplierBtnContainer.classList.add("text-end");
        const supplierBtn = ElementFactory.createButton(
            "button",
            "Add Supplier"
        );
        supplierBtn.classList.add("ims__add-btn", "mb-4");
        supplierBtn.setAttribute("data-bs-toggle", "modal");
        supplierBtn.setAttribute("data-bs-target", "#ims__modal");
        supplierBtn.addEventListener("click", () => {
            ElementFactory.showModal("Add", "supplier", "");
        });

        supplierBtnRow.appendChild(supplierBtnCol);
        supplierBtnCol.appendChild(supplierBtnContainer);
        supplierBtnContainer.appendChild(supplierBtn);

        const supplierTableRow = ElementFactory.createRow();
        const supplierTableCol = ElementFactory.createCol();
        const supplierTableContainer = ElementFactory.createDiv();
        supplierTableContainer.classList.add(
            "ims__supplier-table-container",
            "overflow-hidden",
            "overflow-x-scroll"
        );
        const supplierTable = ElementFactory.createTable("ims__supplier-table");
        supplierTable.classList.add("mb-2", "overflow-hidden");

        const headerLabels = [
            "No",
            "Supplier Name",
            "Contact Person",
            "Contact No",
            "Email",
            "Location",
            "Action",
        ];
        ElementFactory.createTableHeader(supplierTable, headerLabels);

        fetch("db/supplier_db.php", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const suppliers = data.suppliers;

                    ElementFactory.createTableBody(
                        "supplier",
                        supplierTable,
                        suppliers
                    );

                    supplierTableRow.appendChild(supplierTableCol);
                    supplierTableCol.appendChild(supplierTableContainer);
                    supplierTableContainer.appendChild(supplierTable);

                    supplierSect.appendChild(supplierTitleRow);
                    supplierSect.appendChild(supplierBtnRow);
                    supplierSect.appendChild(supplierTableRow);

                    $(supplierTable).DataTable({
                        order: [[0, "desc"]],
                    });
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

        MAIN_CONTENT.appendChild(supplierSect);
    }
}
