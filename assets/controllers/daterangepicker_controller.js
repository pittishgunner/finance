import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['input', 'loading', 'submit']

    initialize() {
        const ranges = {
            Today: [moment(), moment()],
            Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 365 Days': [moment().subtract(364, 'days'), moment()],
            'Last 20 years': [moment().subtract(20, 'years'), moment()],
        }

        this.dateRangePicker = new DateRangePicker(this.inputTarget, {
            alwaysShowCalendars: true,
            ranges: ranges,
            opens: 'left',
            autoApply: true,
            showWeekNumbers: true,
            locale: { format: 'DD MMM YYYY', "firstDay": 1  }
        })
    }

    async onSetRange() {
        await this.postSetRange(this.inputTarget.value)
    }
    async postSetRange(range) {
        this.loadingTarget.classList.remove('d-none');
        this.submitTarget.setAttribute('disabled', 'disabled');
        let here = new URL(window.location.href);
        return fetch('/admin/setRange', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({dateRange:range,fromUrl:here})
            })
            .then((response) => {
                if (response?.ok) {
                    return response.json();
                } else {
                    console.error(`Failed ${response?.status} ${response?.statusText}`);
                    this.loadingTarget.classList.add('d-none');
                    this.submitTarget.removeAttribute('disabled');
                    return '';
                }
            })
            .then((redirectData) => {
                window.location.replace(redirectData.redirect);

                return '';
            })
            .catch((error) => {
                console.error(`Failed ${error}`);
                this.loadingTarget.classList.add('d-none');
                this.submitTarget.removeAttribute('disabled');
                return '';
            });
    }
}
