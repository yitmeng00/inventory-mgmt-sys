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

    static createParagraph(paragraph) {
        const p = document.createElement("p");
        p.textContent = paragraph;

        return p;
    }

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
}
