const {jsPDF} = require('jspdf');
const XLSX = require("xlsx");
const domToImage = require('dom-to-image');

const $ = require('jquery');
global.$ = global.jQuery = $;
global.jsPDF = jsPDF;
global.XLSX = XLSX;
global.domToImage = domToImage;

// import
import './pinsupreme/bower_components/jquery/dist/jquery.min'
import './lib/bootstrap-bundle/bootstrap.bundle.min'
import './pinsupreme/bower_components/moment/moment'
// import 'moment/dist/moment'
import './pinsupreme/bower_components/select2/dist/js/select2.full.min'
import './pinsupreme/bower_components/chart.js/dist/Chart.min'
import './pinsupreme/bower_components/ckeditor/ckeditor'
import './pinsupreme/bower_components/bootstrap-validator/dist/validator.min'
import './pinsupreme/bower_components/bootstrap-daterangepicker/daterangepicker'
import './pinsupreme/bower_components/dropzone/dist/dropzone'
import './pinsupreme/bower_components/editable-table/mindmup-editabletable'
import './pinsupreme/bower_components/datatables/media/js/jquery.dataTables.min'
import './pinsupreme/bower_components/datatables/media/js/dataTables.bootstrap4.min'
import './pinsupreme/bower_components/fullcalendar/dist/fullcalendar.min'
import './lib/bootstrap-datepicker/bootstrap-datepicker.min'
import './lib/bootstrap-datepicker/bootstrap-datepicker.fr.min'
import './pinsupreme/lib/Coverflow-Carousel-jQuery-dnSlide/dist/js/dnSlide-e3d534bdc1'

import './js/app'
import './pinsupreme/js/main'
import './lib/bootstrap-fileinput/bootstrap-fileinput'
import './pinsupreme/bower_components/jquery/dist/jquery.validate'
import './js/footer'
