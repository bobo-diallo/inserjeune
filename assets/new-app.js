/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
// import 'fullcalendar/main.min.css';

const $ = require('jquery');
global.$ = global.jQuery = $;

import 'fullcalendar';
import 'fullcalendar/main.min.css';
import 'select2';
import 'select2/dist/css/select2.min.css';
import 'bootstrap-daterangepicker';
import 'bootstrap-daterangepicker/daterangepicker.css';
import 'dropzone'
import 'dropzone/dist/dropzone.css'
import 'datatables'
import 'datatables/media/css/jquery.dataTables.min.css'
import 'datatables.net-bs4'
import 'datatables.net-bs4/css/dataTables.bootstrap4.min.css'
import 'bootstrap-fileinput'
import 'bootstrap-fileinput/css/fileinput.min.css'

// start the Stimulus application
import './bootstrap';
