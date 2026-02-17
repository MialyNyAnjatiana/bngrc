(function ($) {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    /* === VISIT SALE CHART === */
    const visitChartEl = document.getElementById('visit-sale-chart');
    if (visitChartEl) {
      const ctx = visitChartEl.getContext("2d");

      // Gradients
      const gradientViolet = ctx.createLinearGradient(0, 0, 0, 181);
      gradientViolet.addColorStop(0, 'rgba(218, 140, 255, 1)');
      gradientViolet.addColorStop(1, 'rgba(154, 85, 255, 1)');

      const gradientBlue = ctx.createLinearGradient(0, 0, 0, 360);
      gradientBlue.addColorStop(0, 'rgba(54, 215, 232, 1)');
      gradientBlue.addColorStop(1, 'rgba(177, 148, 250, 1)');

      const gradientRed = ctx.createLinearGradient(0, 0, 0, 300);
      gradientRed.addColorStop(0, 'rgba(255, 191, 150, 1)');
      gradientRed.addColorStop(1, 'rgba(254, 112, 150, 1)');

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG'],
          datasets: [
            { label: "CHN", backgroundColor: gradientViolet, data: [20, 40, 15, 35, 25, 50, 30, 20] },
            { label: "USA", backgroundColor: gradientRed, data: [40, 30, 20, 10, 50, 15, 35, 40] },
            { label: "UK",  backgroundColor: gradientBlue, data: [70, 10, 30, 40, 25, 50, 15, 30] }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: { legend: { display: false } },
          scales: { y: { display: false }, x: { display: true, grid: { display: false } } }
        }
      });
    }

    /* === TRAFFIC CHART === */
    const trafficChartEl = document.getElementById('traffic-chart');
    if (trafficChartEl) {
      const ctx = trafficChartEl.getContext('2d');

      const gradientBlue = ctx.createLinearGradient(0, 0, 0, 181);
      gradientBlue.addColorStop(0, 'rgba(54, 215, 232, 1)');
      gradientBlue.addColorStop(1, 'rgba(177, 148, 250, 1)');

      const gradientRed = ctx.createLinearGradient(0, 0, 0, 50);
      gradientRed.addColorStop(0, 'rgba(255, 191, 150, 1)');
      gradientRed.addColorStop(1, 'rgba(254, 112, 150, 1)');

      const gradientGreen = ctx.createLinearGradient(0, 0, 0, 300);
      gradientGreen.addColorStop(0, 'rgba(6, 185, 157, 1)');
      gradientGreen.addColorStop(1, 'rgba(132, 217, 210, 1)');

      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Search Engines 30%', 'Direct Click 30%', 'Bookmarks Click 40%'],
          datasets: [{
            data: [30, 30, 40],
            backgroundColor: [gradientBlue, gradientGreen, gradientRed],
          }]
        },
        options: {
          cutout: 50,
          responsive: true,
          plugins: { legend: { display: false } }
        }
      });
    }

    /* === INLINE DATEPICKER === */
    if ($("#inline-datepicker").length) {
      $('#inline-datepicker').datepicker({
        enableOnReadonly: true,
        todayHighlight: true
      });
    }

    /* === PRO BANNER LOGIC === */
    const proBanner = document.querySelector('#proBanner');
    const navbar = document.querySelector('.navbar');
    const bannerClose = document.querySelector('#bannerClose');
    const pageBody = document.querySelector('.page-body-wrapper');

    if (proBanner && navbar && pageBody) {
      if ($.cookie('purple-pro-banner') !== "true") {
        proBanner.classList.add('d-flex');
        navbar.classList.remove('fixed-top');
      } else {
        proBanner.classList.add('d-none');
        navbar.classList.add('fixed-top');
      }

      if (navbar.classList.contains('fixed-top')) {
        pageBody.classList.remove('pt-0');
        navbar.classList.remove('pt-5');
      } else {
        pageBody.classList.add('pt-0');
        navbar.classList.add('pt-5', 'mt-3');
      }

      if (bannerClose) {
        bannerClose.addEventListener('click', function () {
          proBanner.classList.add('d-none');
          proBanner.classList.remove('d-flex');
          navbar.classList.remove('pt-5', 'mt-3');
          navbar.classList.add('fixed-top');
          pageBody.classList.add('proBanner-padding-top');
          const date = new Date();
          date.setTime(date.getTime() + 24 * 60 * 60 * 1000);
          $.cookie('purple-pro-banner', "true", { expires: date });
        });
      }
    }

  });
})(jQuery);
