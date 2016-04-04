var meicanMap = new MeicanLMap('canvas');
var connIsApproved = true;
var path;

$(document).ready(function() {
    meicanMap.show("rnp", 'dev');
    $("#canvas").css("height", 400);

    drawCircuit($("#circuit-id").attr('value'));

    refresher = setInterval(function() {
        updateCircuitStatus();
    }, 1000);

    buildStatsGraph();
    initHistoryModal();
    initEditModal();
});

$(document).on('ready pjax:success', function() {
    initHistoryModal();
});

function initEditModal() {
    $('#datetime-picker').daterangepicker({
        timePicker: true,
        timePickerIncrement: 1,
        timePicker24Hour: true,
        autoApply: true,
        autoUpdateInput: true,
        startDate: moment($("#info-start").attr('value')).format("DD/MM/YYYY HH:mm"),
        endDate: moment($("#info-end").attr('value')).format("DD/MM/YYYY HH:mm"),
        isInvalidDate: function(date) {
            if (moment().isSame(date) || moment().isBefore(date)) {
                console.log('valid', date);
                return false;
            } 
            console.log(date);
            return true;
        },
        "locale": {
            "format": "DD/MM/YYYY HH:mm",
            "separator": " - ",
            "daysOfWeek": [
                I18N.t("Su"),
                I18N.t("Mo"),
                I18N.t("Tu"),
                I18N.t("We"),
                I18N.t("Th"),
                I18N.t("Fr"),
                I18N.t("Sa")
            ],
            "monthNames": [
                I18N.t("January"),
                I18N.t("February"),
                I18N.t("March"),
                I18N.t("April"),
                I18N.t("May"),
                I18N.t("June"),
                I18N.t("July"),
                I18N.t("August"),
                I18N.t("September"),
                I18N.t("October"),
                I18N.t("November"),
                I18N.t("December")
            ],
        },
    });

    $(".daterangepicker").find('.ranges').remove();

    $("#edit-btn").on("click", function() {
        $('#edit-modal').modal("show");
        return false;
    });

    $("#bandwidth").on("click", '.minus', function() {
        if (!isNaN($("#bandwidth").find('input').val())) {
            var old = $("#bandwidth").find('input').val();
            var temp = parseInt($("#bandwidth").find('input').val()) - 100;
            $("#bandwidth").find('input').val(temp < 0 ? old : temp);
        }
    });

    $("#bandwidth").on("click", '.plus', function() {
        if (!isNaN($("#bandwidth").find('input').val())) {
            $("#bandwidth").find('input').val(parseInt($("#bandwidth").find('input').val()) + 100);
        }
    });
}

function initHistoryModal() {
    $("#history-grid").on("click", '.event-message', function() {
        $('#history-modal').modal('show');
        return false;
    });
}

function updateCircuitStatus() {
    switch($("#status").attr("data-value")) {
        case 'reservating'   : 
            break;
        case 'scheduled'     : 
            if(moment().isAfter($("#circuit-info").find('.start-time').attr("value"))) {
                activatingCircuit();
            } else {
                $("#status").find(".tts").text(moment().to($("#circuit-info").find('.start-time').attr("value")));
            }
            break;
        case 'activating'    : activeCircuit();
            break;
        case 'active'        : finishCircuit();
            break;
        case 'finished'      : 
            break;
    }
}

function scheduleCircuit() {
    $("#status").find(".info-box-text").text("Time to start");
    $("#status").find(".info-box-number").html('<span class="tts">loading...</span><br><small>10/02/2016 at 20:00</small>');
    $("#status").attr("data-value", 'scheduled');
}

function activatingCircuit() {
    $("#status").find(".ion-clock").removeClass().addClass("ion ion-gear-a");
    $("#status").find(".info-box-text").text("Status");
    $("#status").find(".info-box-number").text("Activating");
    $("#status").attr("data-value", 'activating');
}

function activeCircuit() {
    $("#status").find(".ion-clock").removeClass().addClass("ion ion-arrow-up-a");
    $("#status").find(".info-box-text").text("Status");
    $("#status").find(".info-box-number").text("Active");
    $("#status").attr("data-value", 'active');
}

function inactiveCircuit() {
    $("#status").find(".ion-clock").removeClass().addClass("ion ion-close-circled");
    $("#status").find(".info-box-text").text("Status");
    $("#status").find(".info-box-number").text("Inactive");
}

function finishCircuit() {
    $("#status").find(".ion-clock").removeClass().addClass("ion ion-checkmark-circled");
    $("#status").find(".info-box-text").text("Status");
    $("#status").find(".info-box-number").text("Finished");
}

function drawCircuit(connId, animate) {
    $.ajax({
        url: baseUrl+'/circuits/connection/get-ordered-paths',
        dataType: 'json',
        method: "GET",
        data: {
            id: connId,
        },
        success: function(response) {
            if (connIsApproved) {
                var size = response.length;
                path = response;

                //a ordem dos marcadores aqui eh importante,
                //pois eh a ordem do circuito
                //console.log(requiredMarkers);

                addSource(path[0]);
                addDestin(path[size-1]);
                
                for (var i = 1; i < size-1; i++) {
                    addWayPoint(path[i]);
                }
                
                //setMapBoundsMarkersWhenReady(requiredMarkers);
                
                drawCircuitWhenReady(path, animate);
                
            } else {
                var size = response.length;
            
                var requiredMarkers = [];

                //aqui nao importa a ordem dos marcadores, pois nao ha circuito criado
                addSourceMarker(response[0].device_id);
                requiredMarkers.push(response[0].device_id);
                addDestinMarker(response[size-1].device_id);
                requiredMarkers.push(response[size-1].device_id);
                
                for (var i = 1; i < size-1; i++) {
                    if (response[i].device_id != null) {
                        addWayPointMarker(response[i].device_id);
                        requiredMarkers.push(response[i].device_id);
                    }
                }
                
                //setMapBoundsMarkersWhenReady(requiredMarkers);
            }
        }
    });
}

function drawCircuitWhenReady(requiredMarkers, animate) {
    if (areMarkersReady(requiredMarkers)) {
        //console.log("drew");
        if (animate) {
            drawCircuitAnimated(requiredMarkers);
        } else {
            var path = [];
            for (var i = 0; i < meicanMap.getMarkers().length; i++) {
                path.push(meicanMap.getMarkers()[i].options.id);
            }
            meicanMap.addLink(path);
        }
    } else {
        setTimeout(function() {
            drawCircuitWhenReady(requiredMarkers, animate);
        } ,50);
    }
}

function setMapBoundsMarkersWhenReady(requiredMarkers) {
    if (areMarkersReady(requiredMarkers)) {
        //console.log("setbounds");
        var path = [];
        var size = requiredMarkers.length;
        for(var i = 0; i < size; i++){
            path.push(meicanMap.getMarker('dev',requiredMarkers[i]).position);
        }
        setMapBounds(path);
    } else {
        setTimeout(function() {
            setMapBoundsMarkersWhenReady(requiredMarkers);
        } ,50);
    }
}

function addWayPoint(pathItem) {
    //marker = meicanMap.getMarker('dev'+ devId);
    //if (marker) return;

    $.ajax({
        url: baseUrl+'/circuits/connection/get-stp',
        dataType: 'json',
        method: "GET",
        data: {
            id: pathItem.device_id,
        },
        success: function(response) {
            addMarker(response, "#00FF00");
        }
    });
}

function addSource(pathItem) {
    //marker = meicanMap.getMarker('dev'+ devId);
    //if (marker) return meicanMap.changeDeviceMarkerColor(marker, "0000EE");

    $.ajax({
        url: baseUrl+'/circuits/connection/get-stp',
        dataType: 'json',
        method: "GET",
        data: {
            id: pathItem.device_id,
        },
        success: function(response) {
            addMarker(response, "#0000EE");
        }
    });
}

function addDestin(pathItem) {
    //marker = meicanMap.getMarker('dev'+ devId);
    //if (marker) return meicanMap.changeDeviceMarkerColor(marker, "FF0000");

    $.ajax({
        url: baseUrl+'/circuits/connection/get-stp',
        dataType: 'json',
        method: "GET",
        data: {
            id: pathItem.device_id,
        },
        success: function(response) {
            addMarker(response, "#FF0000");
        }
    });
}

function addMarker(dev, color) {
    marker = meicanMap.getMarker('dev'+dev.id);
    if (marker) return marker;

    meicanMap.addMarker(
        'dev'+dev.id,
        dev.name,
        'dev',
        dev.dom,
        dev.lat,
        dev.lng,
        color);
    //markerCluster.addMarker(marker);
    
    //addMarkerListeners(marker);
    
    //marker.setMap(meicanMap.getMap());
}

function areMarkersReady(ids) {
    for (var i = 0; i < ids.length; i++) {
        var marker = meicanMap.getMarker('dev'+ids[i].device_id);
        if (marker === null) {
            return false;
        }
    }
    
    return true;
}

function buildStatsGraph() {
    var DELAY = 1000; // delay in ms to add new data points

  // create a graph2d with an (currently empty) dataset
  var container = document.getElementById('stats');
  var dataset = new vis.DataSet();

  var options = {
    start: vis.moment().add(-30, 'seconds'), // changed so its faster
    end: vis.moment(),
    height: '380px',
    dataAxis: {
      left: {
        range: {
          min:0, max: 20
        }
      }
    },
    drawPoints: {
      style: 'circle' // square, circle
    },
    shaded: {
      orientation: 'bottom' // top, bottom
    }
  };
  var graph2d = new vis.Graph2d(container, dataset, options);

  // a function to generate data points
  function y(x) {
    return ((Math.sin(x / 2) + Math.cos(x / 4)) * 5) + 10;
  }

  function renderStep() {
    // move the window (you can think of different strategies).
    var now = vis.moment();
    var range = graph2d.getWindow();
    var interval = range.end - range.start;
    switch ('continuous') {
      case 'continuous':
        // continuously move the window
        graph2d.setWindow(now - interval, now, {animation: false});
        requestAnimationFrame(renderStep);
        break;

      case 'discrete':
        graph2d.setWindow(now - interval, now, {animation: false});
        setTimeout(renderStep, DELAY);
        break;

      default: // 'static'
        // move the window 90% to the left when now is larger than the end of the window
        if (now > range.end) {
          graph2d.setWindow(now - 0.1 * interval, now + 0.9 * interval);
        }
        setTimeout(renderStep, DELAY);
        break;
    }
  }
  renderStep();

  /**
   * Add a new datapoint to the graph
   */
  function addDataPoint() {
    // add a new data point to the dataset
    var now = vis.moment();
    dataset.add({
      x: now,
      y: y(now / 1000)
    });

    setTimeout(addDataPoint, DELAY);
  }
  addDataPoint();
}
  