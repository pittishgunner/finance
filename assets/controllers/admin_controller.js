import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = [];

    connect() {
        if (document.getElementById('importRulesButton') != null) {
            this.importRulesHandler(
                document.getElementById('importRulesButton'),
                document.getElementById('importRulesFile'),
                document.getElementById('importRulesSubmit'),
            );
        }

        if (document.getElementById('importRecordsButton') != null) {
            this.importRecordsHandler(
                document.getElementById('importRecordsButton'),
                document.getElementById('importRecordsFile'),
                document.getElementById('importRecordsAccount'),
                document.getElementById('importRecordsSubmit'),
            );
        }
    }

    async importRulesHandler(button, file, submit) {
        button.addEventListener('click', function (event){
            event.preventDefault();
            file.click();
        });
        file.addEventListener('change', function (event) {
            button.querySelector('span').innerHTML = ' - ' + file.value.replace(/C:\\fakepath\\/ig,'');
            submit.classList.remove('d-none');
        });
    }

    async importRecordsHandler(button, file, account, submit) {
        button.addEventListener('click', function (event){
            event.preventDefault();
            file.click();
            submit.classList.add('d-none');
        });
        file.addEventListener('change', function (event) {
            button.querySelector('span').innerHTML = ' - ' + file.value.replace(/C:\\fakepath\\/ig,'');
            account.classList.remove('d-none');
        });
        account.addEventListener('change', function (event) {
            if (account.value !== '') {
                submit.classList.remove('d-none');
            } else {
                submit.classList.add('d-none');
            }
        });
    }
}
