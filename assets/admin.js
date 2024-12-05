import './styles/admin.css';
import './bootstrap';
import './notify';
import moment from 'moment'

window.moment = moment

// symfony react triggers dropdowns to auto close
// this unfortunately disables closing on click on link, but still works on clicking outside
document.querySelectorAll('.dropdown').forEach((el) => {
    el.addEventListener('hide.bs.dropdown', function (ev) {
        if (typeof ev.clickEvent === 'undefined') {
            ev.preventDefault();
        }
    })
});
