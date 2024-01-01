class Transaction {
    static initTransaction() {
        const MAIN_CONTENT = document.getElementById("ims__main-transaction");

        ElementFactory.renderModal();

        const transactionSect = ElementFactory.createSection();
        transactionSect.classList.add("mb-3");

        const transactionTitleRow = ElementFactory.createTitle(
            "Transactions",
            "h3"
        );

        const transactionBtnRow = ElementFactory.createRow();
        const transactionBtnCol = ElementFactory.createCol();
        const transactionBtnContainer = ElementFactory.createDiv();
        transactionBtnContainer.classList.add("text-end");
        const transactionBtn = ElementFactory.createButton(
            "button",
            "Add Transaction"
        );
        transactionBtn.classList.add("ims__add-btn", "mb-4");
        transactionBtn.setAttribute("data-bs-toggle", "modal");
        transactionBtn.setAttribute("data-bs-target", "#ims__modal");
        transactionBtn.addEventListener("click", () => {
            ElementFactory.showModal("Add", "transaction", "");
        });

        transactionBtnRow.appendChild(transactionBtnCol);
        transactionBtnCol.appendChild(transactionBtnContainer);
        transactionBtnContainer.appendChild(transactionBtn);

        const transactionTableRow = ElementFactory.createRow();
        const transactionTableCol = ElementFactory.createCol();
        const transactionTableContainer = ElementFactory.createDiv();
        transactionTableContainer.classList.add(
            "ims__transaction-table-container",
            "overflow-hidden",
            "overflow-x-scroll"
        );
        const transactionTable = ElementFactory.createTable(
            "ims__transaction-table"
        );
        transactionTable.classList.add("mb-2", "overflow-hidden");

        const headerLabels = [
            "No",
            "Product Code",
            "Product Name",
            "Type",
            "Quantity",
            "Unit Price",
            "Created",
            "Action",
        ];
        ElementFactory.createTableHeader(transactionTable, headerLabels);

        fetch("db/transaction_db.php", {
            method: "GET",
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const transactions = data.transactions;

                    ElementFactory.createTableBody(
                        "transaction",
                        transactionTable,
                        transactions
                    );

                    transactionTableRow.appendChild(transactionTableCol);
                    transactionTableCol.appendChild(transactionTableContainer);
                    transactionTableContainer.appendChild(transactionTable);

                    transactionSect.appendChild(transactionTitleRow);
                    transactionSect.appendChild(transactionBtnRow);
                    transactionSect.appendChild(transactionTableRow);

                    $(transactionTable).DataTable({
                        order: [[0, "desc"]],
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

        MAIN_CONTENT.appendChild(transactionSect);
    }
}
