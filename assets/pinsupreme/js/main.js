"use strict";
$(function () {
    if ($("#fullCalendar").length) {
        var e, t, a, l, r;
        a = new Date, t = a.getDate(), l = a.getMonth(), r = a.getFullYear(), e = $("#fullCalendar").fullCalendar({
            header: {
                left: "prev,next today",
                center: "title",
                right: "month,agendaWeek,agendaDay"
            },
            selectable: !0,
            selectHelper: !0,
            select: function t(a, l, r) {
                var o;
                return o = prompt("Event Title:"), o && e.fullCalendar("renderEvent", {
                    title: o,
                    start: a,
                    end: l,
                    allDay: r
                }, !0), e.fullCalendar("unselect")
            },
            editable: !0,
            events: [{
                title: "Long Event",
                start: new Date(r, l, 3, 12, 0),
                end: new Date(r, l, 7, 14, 0)
            }, {
                title: "Lunch",
                start: new Date(r, l, t, 12, 0),
                end: new Date(r, l, t + 2, 14, 0),
                allDay: !1
            }, {
                title: "Click for Google",
                start: new Date(r, l, 28),
                end: new Date(r, l, 29),
                url: "http://google.com/"
            }]
        })
    }
    if ($("#formValidate").length && $("#formValidate").validator(), $("input.single-daterange").daterangepicker({singleDatePicker: !0, "locale": {
          // "format": "MM/DD/YYYY",
          "format": "DD/MM/YYYY",
          "separator": " - ",
          "applyLabel": "Valider",
          "cancelLabel": "Annuler",
          "fromLabel": "De",
          "toLabel": "à",
          "customRangeLabel": "Custom",
          "daysOfWeek": [
             "D",
             "L",
             "M",
             "M",
             "J",
             "V",
             "S"
          ],
          "monthNames": [
             "Janvier",
             "Février",
             "Mars",
             "Avril",
             "Mai",
             "Juin",
             "Juillet",
             "Août",
             "Septembre",
             "Octobre",
             "Novembre",
             "Décembre"
          ],
          "firstDay": 8
       }}), $("input.multi-daterange").daterangepicker({
            startDate: "03/28/2017",
            endDate: "04/06/2017",
            "locale": {
          // "format": "MM/DD/YYYY",
          "format": "DD/MM/YYYY",
          "separator": " - ",
          "applyLabel": "Valider",
          "cancelLabel": "Annuler",
          "fromLabel": "De",
          "toLabel": "à",
          "customRangeLabel": "Custom",
          "daysOfWeek": [
             "D",
             "L",
             "M",
             "M",
             "J",
             "V",
             "S"
          ],
          "monthNames": [
             "Janvier",
             "Février",
             "Mars",
             "Avril",
             "Mai",
             "Juin",
             "Juillet",
             "Août",
             "Septembre",
             "Octobre",
             "Novembre",
             "Décembre"
          ],
          "firstDay": 8
       }
        }), $("#formValidate").length && $("#formValidate").validator(), $("#dataTable1").length && $("#dataTable1").DataTable({buttons: ["copy", "excel", "pdf"]}), $("#editableTable").length && $("#editableTable").editableTableWidget(), $(".step-trigger-btn").on("click", function () {
            var e = $(this).attr("href");
            return $('.step-trigger[href="' + e + '"]').click(), !1
        }), $(".step-trigger").on("click", function () {
            var e = $(this).prev(".step-trigger");
            if (e.length && !e.hasClass("active") && !e.hasClass("complete"))return !1;
            var t = $(this).attr("href");
            return $(this).closest(".step-triggers").find(".step-trigger").removeClass("active"), $(this).prev(".step-trigger").addClass("complete"), $(this).addClass("active"), $(".step-content").removeClass("active"), $(".step-content" + t).addClass("active"), !1
        }), $(".select2").length && $(".select2").select2(), $("#ckeditor1").length && CKEDITOR.replace("ckeditor1"), "undefined" != typeof Chart) {
        var o = '"Avenir Next Rounded W01", -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

       /*Affichage lineChartDegree */
       /****************************/
        if (Chart.defaults.global.defaultFontFamily = '' +
          '"Avenir Next Rounded W01", ' +
          '-apple-system, system-ui, ' +
          'BlinkMacSystemFont, ' +
          '"Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
          Chart.defaults.global.tooltips.titleFontSize = 14,
          Chart.defaults.global.tooltips.titleMarginBottom = 6,
          Chart.defaults.global.tooltips.displayColors = !1,
          Chart.defaults.global.tooltips.bodyFontSize = 12,
          Chart.defaults.global.tooltips.xPadding = 10,
          Chart.defaults.global.tooltips.yPadding = 8,
          $("#lineChartDegree").length)var i = $("#lineChartDegree"),
          n = {
             labels: ["1", "5", "10", "15", "20", "25", "30", "35"],
             datasets: [{
                label: "Diplômés",
                fill: !1,
                lineTension: 0,
                backgroundColor: "#fff",
                borderColor: "#6896f9",
                borderCapStyle: "butt",
                borderDash: [],
                borderDashOffset: 0,
                borderJoinStyle: "miter",
                pointBorderColor: "#fff",
                pointBackgroundColor: "#2a2f37",
                pointBorderWidth: 3,
                pointHoverRadius: 10,
                pointHoverBackgroundColor: "#FC2055",
                pointHoverBorderColor: "#fff",
                pointHoverBorderWidth: 3,
                pointRadius: 6,
                pointHitRadius: 10,
                data: [27, 20, 44, 24, 29, 22, 43, 52],
                spanGaps: !1
             }]
          }, s = new Chart(i, {
             type: "line",
             data: n,
             options: {
                legend: {display: !1},
                scales: {
                   xAxes: [{
                      ticks: {fontSize: "11", fontColor: "#969da5"},
                      gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "rgba(0,0,0,0.05)"}
                   }], yAxes: [{display: !1, ticks: {beginAtZero: !0, max: 65}}]
                }
             }
          });

       /*Affichage lineChartCompany */
       /*****************************/
        if (Chart.defaults.global.defaultFontFamily = '' +
           '"Avenir Next Rounded W01", ' +
           '-apple-system, system-ui, ' +
           'BlinkMacSystemFont, ' +
           '"Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
           Chart.defaults.global.tooltips.titleFontSize = 14,
           Chart.defaults.global.tooltips.titleMarginBottom = 6,
           Chart.defaults.global.tooltips.displayColors = !1,
           Chart.defaults.global.tooltips.bodyFontSize = 12,
           Chart.defaults.global.tooltips.xPadding = 10,
           Chart.defaults.global.tooltips.yPadding = 8,
           $("#lineChartCompany").length)var i = $("#lineChartCompany"),
            n = {
                labels: ["1", "2", "3", "4", "5", "6", "7", "8","9", "10", "11",
                   "12", "13", "14", "15", "16", "17", "18", "19","20", "21", "22",
                   "23", "24", "25", "26", "27", "28", "29", "30", "31"],
                datasets: [{
                    label: "Entreprises",
                    fill: !1,
                    lineTension: 0,
                    backgroundColor: "#fff",
                    borderColor: "#6896f9",
                    borderCapStyle: "butt",
                    borderDash: [],
                    borderDashOffset: 0,
                    borderJoinStyle: "miter",
                    pointBorderColor: "#fff",
                    pointBackgroundColor: "#2a2f37",
                    pointBorderWidth: 3,
                    pointHoverRadius: 10,
                    pointHoverBackgroundColor: "#FC2055",
                    pointHoverBorderColor: "#fff",
                    pointHoverBorderWidth: 3,
                    pointRadius: 6,
                    pointHitRadius: 10,
                    data: [5, 6, 8, 9, 9, 12, 20, 25, 30, 40, 50,
                       50, 20, 10, 2, 2, 3, 4, 6, 8, 6, 2,
                       8, 12, 18, 20, 25, 25, 25, 5,10],
                    spanGaps: !1
                }]
            }, s = new Chart(i, {
                type: "line",
                data: n,
                options: {
                    legend: {display: !1},
                    scales: {
                        xAxes: [{
                            ticks: {fontSize: "11", fontColor: "#969da5"},
                            gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "rgba(0,0,0,0.05)"}
                        }], yAxes: [{display: !1, ticks: {beginAtZero: !0, max: 65}}]
                    }
                }
            });

       if ($("#barSatisfactionsEx").length) {
          var d = $("#barSatisfactionsEx"), c = {
             labels: ["", "Diplômés sans emploi", "Diplômés salariés", "Diplômés créateurs", "Entreprises"],
             datasets: [{
                // label: [""],
                backgroundColor: ["#FFFFFF", "#85C441", "#FFD403", "#74AAFF", "#EE3233"],
                borderColor: ["rgba(255, 255, 255, 0)", "rgba(255,99,132,0)", "rgba(54, 162, 235, 0)", "rgba(255, 206, 86, 0)", "rgba(75, 192, 192, 0)"],
                borderWidth: 1,
                data: [100, 20, 40, 89, 25]
             }]
          };
          new Chart(d, {
             type: "bar",
             data: c,
             options: {
                scales: {
                   xAxes: [{
                      display: !1,
                      ticks: {fontSize: "11", fontColor: "#969da5"},
                      gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "rgba(0,0,0,0.05)"}
                   }],
                   yAxes: [{
                      ticks: {beginAtZero: !0},
                      gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "#6896f9"},
                   }]
                }, legend: {display: !1}, animation: {animateScale: !0}
             }
          })
       }

       // /*Affichage Donut  */
       // /******************/
       if ($("#donut").length) {
          var u = $("#donut"), h = {
             labels: ["commerce", "artisanat", "tourisme", "santé", "enseignement", "batiment", "energie", "transport" ],
             datasets: [{
                data: [150, 100, 80, 40, 40, 90, 12, 8],
                backgroundColor: ["#85C441","#FFD403","#74AAFF","#EE3233","#8E1682","#FF8B00","#1C1817","#BFBFBF"],
                hoverBackgroundColor: ["#56802F","#9D8106","#456293","#871D1D","#550D4E","#894B01","#98837E","#737473"],
                borderWidth: 0
             }]
          };
          new Chart(u, {
             type: "doughnut",
             data: h,
             options: {legend: {display: !1}, animation: {animateScale: !0}, cutoutPercentage: 80}
          })
       }


        if ($("#barChart1").length) {
            var d = $("#barChart1"), c = {
                labels: ["January", "February", "March", "April", "May", "June"],
                datasets: [{
                    label: "My First dataset",
                    backgroundColor: ["#5797FC", "#629FFF", "#6BA4FE", "#74AAFF", "#7AAEFF", "#85B4FF"],
                    borderColor: ["rgba(255,99,132,0)", "rgba(54, 162, 235, 0)", "rgba(255, 206, 86, 0)", "rgba(75, 192, 192, 0)", "rgba(153, 102, 255, 0)", "rgba(255, 159, 64, 0)"],
                    borderWidth: 1,
                    data: [24, 42, 18, 34, 56, 28]
                }]
            };
            new Chart(d, {
                type: "bar",
                data: c,
                options: {
                    scales: {
                        xAxes: [{
                            display: !1,
                            ticks: {fontSize: "11", fontColor: "#969da5"},
                            gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "rgba(0,0,0,0.05)"}
                        }],
                        yAxes: [{
                            ticks: {beginAtZero: !0},
                            gridLines: {color: "rgba(0,0,0,0.05)", zeroLineColor: "#6896f9"}
                        }]
                    }, legend: {display: !1}, animation: {animateScale: !0}
                }
            })
        }
        if ($("#pieChart1").length) {
            var f = $("#pieChart1"), g = {
                labels: ["Red", "Blue", "Yellow", "Green", "Purple"],
                datasets: [{
                    data: [300, 50, 100, 30, 70],
                    backgroundColor: ["#5797fc", "#7e6fff", "#4ecc48", "#ffcc29", "#f37070"],
                    hoverBackgroundColor: ["#5797fc", "#7e6fff", "#4ecc48", "#ffcc29", "#f37070"],
                    borderWidth: 0
                }]
            };
            new Chart(f, {
                type: "pie",
                data: g,
                options: {
                    legend: {position: "bottom", labels: {boxWidth: 15, fontColor: "#3e4b5b"}},
                    animation: {animateScale: !0}
                }
            })
        }
        if ($("#donutChart").length) {
            var u = $("#donutChart"), h = {
                labels: ["Red", "Blue", "Yellow", "Green", "Purple"],
                datasets: [{
                    data: [300, 50, 100, 30, 70],
                    backgroundColor: ["#5797fc", "#7e6fff", "#4ecc48", "#ffcc29", "#f37070"],
                    hoverBackgroundColor: ["#5797fc", "#7e6fff", "#4ecc48", "#ffcc29", "#f37070"],
                    borderWidth: 0
                }]
            };
            new Chart(u, {
                type: "doughnut",
                data: h,
                options: {legend: {display: !1}, animation: {animateScale: !0}, cutoutPercentage: 80}
            })
        }
    }
    $(".mobile-menu-trigger").on("click", function () {
        return $(".menu-w").toggleClass("mobile-active"), !1
    });
    var b;
    $(".menu-activated-on-hover > ul.main-menu > li.has-sub-menu").mouseenter(function () {
        var e = $(this);
        clearTimeout(b), e.closest("ul").addClass("has-active").find("> li").removeClass("active"), e.addClass("active")
    }), $(".menu-activated-on-hover > ul.main-menu > li.has-sub-menu").mouseleave(function () {
        var e = $(this);
        b = setTimeout(function () {
            e.removeClass("active").closest("ul").removeClass("has-active")
        }, 200)
    }), $(".menu-activated-on-click li.has-sub-menu > a").on("click", function (e) {
        var t = $(this).closest("li");
        return t.closest("ul").find("li.active").removeClass("active"), t.toggleClass("active"), !1
    })
});
//# sourceMappingURL=./main.js.map