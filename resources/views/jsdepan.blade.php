<script>
    // Dashboard 1 Morris-chart
$(function () {
    "use strict";
Morris.Area({
        element: 'jumlah-pengunjung',
        data: {!! $dataChart !!},
        xkey: 'tanggal',
        ykeys: ['jumlah'],
        labels: ['Jumlah Pengunjung'],
        pointSize: 0,
        fillOpacity: 0.4,
        pointStrokeColors:['#009efb'],
        behaveLikeLine: true,
        gridLineColor: '#e0e0e0',
        lineWidth: 0,
        parseTime: false,
        smooth: false,
        hideHover: 'auto',
        lineColors: ['#009efb'],
        resize: true

    });
    // Morris donut chart

    Morris.Donut({
        element: 'jumlah-pencarian',
        data: {!! $dataDonut !!},
        resize: true,
        colors:['#55ce63','#fccf03','#009efb', '#2f3d4a']
    });
    var sparklineLogin = function() {


       $("#jumlah-konsultasi").sparkline({!! $dataKonsulChart !!}, {
           type: 'line',
           width: '100%',
           height: '50',
           lineColor: '#26c6da',
           fillColor: '#26c6da',
           maxSpotColor: '#26c6da',
           highlightLineColor: 'rgba(0, 0, 0, 0.2)',
           highlightSpotColor: '#26c6da'
       });
    }
    var sparkResize;

        $(window).resize(function(e) {
            clearTimeout(sparkResize);
            sparkResize = setTimeout(sparklineLogin, 500);
        });
        sparklineLogin();
});

</script>
