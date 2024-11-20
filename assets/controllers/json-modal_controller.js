import { Controller } from '@hotwired/stimulus';
import ace from 'brace'
import 'brace/mode/json'
import 'brace/theme/xcode'

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['loading', 'result']

    openJson(event) {
        let href = event.currentTarget.getAttribute('href');
        let split = href.split('/');
        this.getJsonData(
            '/admin/getJsonData/' +
            split[1] + '/' +
            split[2] + '/' +
            split[3]
        );
        console.warn(href.split('/'))

    }

    async getJsonData(url){
        this.loadingTarget.classList.remove('d-none');
        this.resultTarget.classList.add('d-none');

        return fetch(url, {method: "POST"})
            .then((response) => {
                if (!response.ok) {
                    this.initAce('Error: ' + response.status, false)
                } else {
                    response.json().then(obj => {
                        this.initAce(JSON.stringify(obj, null, 2), true);
                    })
                }
            })
            .catch((error) => {
                this.initAce('Cannot connect', false);
            })
            .finally(() => {
                this.loadingTarget.classList.add('d-none');
            });
    }

    initAce(content, init) {
        this.resultTarget.classList.remove('d-none');
        if (init === true) {
            let editor = ace.edit(this.resultTarget)
            let session = editor.getSession()

            editor.setOptions({
                mode: 'ace/mode/json',
                theme: 'ace/theme/xcode',
                displayIndentGuides: true,
                minLines: 52,
                maxLines: 52,
                fontSize: '13px',
            })

            editor.setValue(content);
            editor.clearSelection();
            editor.scrollToLine(0);
        } else {
            this.resultTarget.innerHTML = content;
        }
    }
}
