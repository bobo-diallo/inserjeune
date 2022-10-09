/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
// import 'fullcalendar/main.min.css';

// const $ = require('jquery');
global.$ = global.jQuery = $;

import './pinsupreme/bower_components/select2/dist/css/select2.min.css'
import './pinsupreme/bower_components/bootstrap-daterangepicker/daterangepicker.css'
import './pinsupreme/bower_components/dropzone/dist/dropzone.css'
import './pinsupreme/bower_components/datatables/media/css/jquery.dataTables.min.css'
import './pinsupreme/bower_components/datatables/media/css/dataTables.bootstrap4.min.css'
import './pinsupreme/bower_components/fullcalendar/dist/fullcalendar.min.css'
import './pinsupreme/lib/Coverflow-Carousel-jQuery-dnSlide/dist/css/dnSlide-e5b62df849.css'
import './lib/bootstrap-fileinput/bootstrap-fileinput.css'
import './lib/bootstrap-datepicker/bootstrap-datepicker.min.css'

// start the Stimulus application
import './bootstrap';
