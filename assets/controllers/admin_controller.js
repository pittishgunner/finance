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
}
