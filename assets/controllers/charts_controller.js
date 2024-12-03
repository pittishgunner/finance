import {Controller} from '@hotwired/stimulus';

const charts = [];

export default class extends Controller {


    averageAnnotation = {
        type: 'line',
        borderColor: 'black',
        borderDash: [6, 6],
        borderDashOffset: 0,
        borderWidth: 3,
        label: {
            enabled: true,
            content: (ctx) => 'Average: ' + average(ctx).toFixed(2),
            position: 'end'
        },
        scaleID: 'y',
        value: (ctx) => this.average(ctx)
    };

    connect() {
        this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.addEventListener('chartjs:connect', this._onConnect);
    }

    disconnect() {
        // You should always remove listeners when the controller is disconnected to avoid side effects
        this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.removeEventListener('chartjs:connect', this._onConnect);
    }

    _onPreConnect(event) {
        // The chart is not yet created
        // You can access the config that will be passed to "new Chart()"
        let allValues = event.detail.config.data.datasets[0].data;
        function getVisibleValues() {
            let x = event.detail.config.options.scales.x;

            allValues = event.detail.config.data.datasets[0].data.slice(x.min, x.max + 1);
        }

        function average(ctx) {
            return allValues.reduce((a, b) => a + b, 0) / allValues.length;
        }

        // event.detail.config.options.plugins.zoom.zoom.onZoomComplete = getVisibleValues;
        // event.detail.config.options.plugins.zoom.pan.onPanComplete = getVisibleValues;

        event.detail.config.options.plugins.annotation.annotations[0] = {
            type: 'line',
            borderColor: 'black',
            borderDash: [6, 6],
            borderDashOffset: 0,
            borderWidth: 1,
            label: {
                display: true,
                content: (ctx) => 'Total Average: ' + average(ctx).toFixed(2),
                position: 'end',
            },
            scaleID: 'y',
            value: (ctx) => average(ctx)
        };


        const getOrCreateLegendList = (chart, id) => {
            const legendContainer = document.getElementById(id);
            let listContainer = legendContainer.querySelector('ul');

            if (!listContainer) {
                listContainer = document.createElement('ul');
                // listContainer.style.display = 'flex';
                // listContainer.style.flexDirection = 'row';
                listContainer.style.margin = '12px 0 0 0';
                listContainer.style.padding = 0;

                legendContainer.appendChild(listContainer);
            }

            return listContainer;
        };

        const htmlLegendPlugin = {
            id: 'htmlLegend',
            afterUpdate(chart, args, options) {
                const ul = getOrCreateLegendList(chart, options.containerID);

                // Remove old legend items
                while (ul.firstChild) {
                    ul.firstChild.remove();
                }

                // Reuse the built-in legendItems generator
                const items = chart.options.plugins.legend.labels.generateLabels(chart);
                console.warn(items);

                items.forEach(item => {
                    const li = document.createElement('li');
                    li.style.alignItems = 'center';
                    li.style.cursor = 'pointer';
                    li.style.display = 'flex';
                    li.style.flexDirection = 'row';
                    li.style.marginLeft = '0';

                    li.onclick = () => {
                        const {type} = chart.config;
                        if (type === 'pie' || type === 'doughnut') {
                            // Pie and doughnut charts only have a single dataset and visibility is per item
                            chart.toggleDataVisibility(item.index);
                        } else {
                            chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
                        }
                        chart.update();
                    };

                    // Color box
                    const boxSpan = document.createElement('span');
                    boxSpan.style.background = item.fillStyle;
                    boxSpan.style.borderColor = item.strokeStyle;
                    boxSpan.style.borderWidth = item.lineWidth + 'px';
                    boxSpan.style.display = 'inline-block';
                    boxSpan.style.flexShrink = 0;
                    boxSpan.style.height = '20px';
                    boxSpan.style.marginRight = '10px';
                    boxSpan.style.width = '20px';

                    // Text
                    const textContainer = document.createElement('p');
                    textContainer.style.color = item.fontColor;
                    textContainer.style.margin = 0;
                    textContainer.style.padding = 0;
                    textContainer.style.textDecoration = item.hidden ? 'line-through' : '';

                    const text = document.createTextNode(item.text);
                    textContainer.appendChild(text);

                    li.appendChild(boxSpan);
                    li.appendChild(textContainer);
                    ul.appendChild(li);
                });
            }
        };

        event.detail.config.plugins = [htmlLegendPlugin];

        // For instance you can format Y axis
        // To avoid overriding existing config, you should distinguish 3 cases:
        // // # 1. No existing scales config => add a new scales config
        // event.detail.config.options.scales = {
        //     y: {
        //         ticks: {
        //             callback: function (value, index, values) {
        //                 /* ... */
        //             },
        //         },
        //     },
        // };
        // // # 2. Existing scales config without Y axis config => add new Y axis config
        // event.detail.config.options.scales.y = {
        //     ticks: {
        //         callback: function (value, index, values) {
        //             /* ... */
        //         },
        //     },
        // };
        // // # 3. Existing Y axis config => update it
        // event.detail.config.options.scales.y.ticks = {
        //     callback: function (value, index, values) {
        //         /* ... */
        //     },
        // };
    }

    _onConnect(event) {
        // The chart was just created
        //console.log(event.detail.chart); // You can access the chart instance using the event details
        charts.push(event.detail.chart);

        // For instance you can listen to additional events
        event.detail.chart.options.onHover = (mouseEvent) => {
            //console.warn(mouseEvent);
        };
        event.detail.chart.options.onClick = (mouseEvent) => {
            /* ... */
        };
    }

    onToggle() {
        charts.forEach(function (chart) {
            chart.data.datasets.forEach(function(ds) {
                ds.hidden = !ds.hidden;
            });
            chart.update();
        });
        //console.warn(charts)
    }

}
