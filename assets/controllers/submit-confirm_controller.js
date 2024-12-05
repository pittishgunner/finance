import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';
export default class extends Controller {
    static values = {
        title: String,
        text: String,
        icon: String,
        confirmButtonText: String,
        submitAsync: String,
    }

    onSubmit(event) {
        event.preventDefault();
        Swal.fire({
            title: this.titleValue || null,
            text: this.textValue || null,
            icon: this.iconValue || null,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: this.confirmButtonTextValue || 'Yes',
        }).then((result) => {
            if (result.isConfirmed) {
                if (this.submitAsyncValue === '') {
                    this.element.submit();
                    return;
                }
                // TODO - do stuff async
                // https://symfonycasts.com/screencast/stimulus/options-form-ajax
                Swal.fire(
                    'Deleted!',
                    'Your file has been deleted.',
                    'success',
                )
            }
        })
    }
}
