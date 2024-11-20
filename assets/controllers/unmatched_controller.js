import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap';

export default class extends Controller {
    static targets = ['rule', 'openModalButton', 'unmatchedModal', 'modalContent', 'loading', 'modalContent']
    currentRule = 0;
    rules = [];

    async onOpenModalButtonClicked(event) {
        let content = '<h6>Current matches</h6>';
        JSON.parse(document.querySelector('#ruleSelect option:checked').getAttribute('data-matches')).forEach((el) =>
            content += '<input type="text" disabled class="form-control" value=\'' + el + '\' style="margin-bottom: 2px"/>'
        );
        content += '<br><h6>Add your matches</h6>';

        this.rules.forEach((el) =>
            content += '<input type="text" class="form-control newMatchInput" value="' + el.value + '" style="margin-bottom: 2px"/>'
        );

        this.modalContentTarget.innerHTML = content;
        const modal = new Modal(this.unmatchedModalTarget);
        modal.show();
    }

    async onAssignAndRerun(event) {
        this.loadingTarget.classList.toggle('d-none');
        let matches = [];

        document.querySelectorAll('.newMatchInput').forEach((el) =>
            matches.push(el.value)
        );

        return fetch('/admin/setNewMatchesToRule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ruleId: this.currentRule, matches: matches})
        })
        .then((response) => {
            if (response?.ok) {
                location.reload();

                return '';
            } else {
                console.error(`Failed ${response?.status} ${response?.statusText}`);
                this.loadingTarget.classList.add('d-none');

                return '';
            }
        })
        .catch((error) => {
            console.error(`Failed ${error}`);
            this.loadingTarget.classList.add('d-none');

            return '';
        });
    }

    async onRuleChange() {
        this.currentRule = this.ruleTarget.value;
        this.toggleAssignButton();
    }

    async onRecordChange() {
        this.rules = document.querySelectorAll('input:checked');
        this.toggleAssignButton();
    }

    async toggleAssignButton() {
        if (this.currentRule > 0 && this.rules.length > 0) {
            this.openModalButtonTarget.removeAttribute('disabled');
        } else {
            this.openModalButtonTarget.setAttribute('disabled', 'disabled');
        }
    }
}
