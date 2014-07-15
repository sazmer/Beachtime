$(document).ready(function() {

    var writePeriods = function() {
        $.ajax({
            url: "rankGen.php",
            data: {mode: "periods"},
            dataType: 'json',
            success: function(periods) {
                console.log(periods);
                $("#rankPeriodList").text("");
                $.each(periods, function(key, session) {
                    $("#rankPeriods").text("");
                    $("#rankPeriods").append('<div id="period' + key + '" data-role="collapsible" data-theme="a" data-content-theme="a"></div>');
                    $("#period" + key).append('<h3 id="' + key + '" class="collapsible-header-link">' + key + '</h3>');

                    $.each(session, function(key2, value) {
                        $("#period" + key).append('<a data-role="button" href="#sessRank" id="' + value + '">' + value + '</a>');
                    });
                    $(".collapsible-header-link").click(function(f) {
                        console.log("clicked");
                        console.log((this).id);
                        getRanking((this).id);
                         $("#rankPeriods").enhanceWithin();

                    });
                    $("a[href$='#sessRank']").click(function(e) {
                        e.preventDefault();
                        console.log((this).id);
                        var session = (this).id;
                        var period = '2014';
                        $.ajax({
                            url: "rankGen.php",
                            data: {mode: "rankSess", session: session, period: period},
                            dataType: 'json',
                            success: function(rankSess) {
                                $("#rankList").text("");
                                $.each(rankSess, function(key, player) {
                                    $("#rankList").append('<li data-theme="d">' + player[0] + " " + player[1] + '<span class="ui-li-count">' + player[2] + '</span></li>');
                                });
                                $("#rankList").listview();
                                $("#rankList").listview("refresh", true);
                            }
                        });
                    });
                });
                $("#gridRankLeft").enhanceWithin();

            }
        });
    };

    var getRanking = function(period) {
        $.ajax({
            url: "rankGen.php",
            data: {mode: "ranking", period: period},
            dataType: 'json',
            success: function(rankGen) {
                $("#rankList").text("");
                $.each(rankGen, function(key, player) {
                    $("#rankList").append('<li data-theme="d">' + player[0] + " " + player[1] + '<span class="ui-li-count">' + player[2] + '</span></li>');
                });
                $("#rankList").listview();
                $("#rankList").listview("refresh", true);
            }
        });
    };
    $(document).on('pagebeforeshow', '#statistik', function() {
        getRanking("2014");
        writePeriods();
    });


});