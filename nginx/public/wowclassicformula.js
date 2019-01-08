/**
 * @constructor
 */
WowClassicFormula = function() {
    var specTemplate = '<div class="item"><div class="ui button icon circular" style="padding: 0.2em 0.2em 0.2em;" onclick="wcf.specPressed(event, \'{{ spec }}\');" data-content="{{ specPretty }}" data-position="bottom center"><img class="ui mini circular image" src="{{ icon }}"></div></div>';

    this.specs = {};

    $.getJSON('/specs.json', function(specs) {
        var specsElem = document.getElementById("specs");
        this.specs = specs;
        for(var specName in specs) {
            var data = specs[specName];
            var specHtml = specTemplate.replace(/{{ spec }}/g, specName);
            specHtml = specHtml.replace(/{{ specPretty }}/g, specName.replace('_', ' ').split(' ').map(function(w) { return w[0].toUpperCase() + w.substr(1).toLowerCase()}).join(' '));
            specHtml = specHtml.replace("{{ icon }}", data['icon']);
            $(specsElem).append(specHtml);
        }
        $('.button').popup();
    }.bind(this));


    $('.dropdown').dropdown();
    $('.accordion').accordion();
};

WowClassicFormula.prototype.submitPressed = function() {
    var form = document.getElementById("form");
    var formData = new FormData(form);

    var values = $('#patch').dropdown('get value');
    formData.append("patch", values);
    var request = new XMLHttpRequest();
    document.getElementById("content").innerHTML = null;
    request.addEventListener('load', function(data) {
        if (request.status === 200) {
            document.getElementById("content").innerHTML = request.responseText;
            $('.accordion').accordion();
        } else {
            console.error(request);
        }
    });
    request.open("POST", "/query/");
    request.send(formData);

    console.log("Submit Pressed");
};

/**
 *
 */
WowClassicFormula.prototype.specPressed = function(event, specName) {
    // Reset
    $(document.getElementsByClassName('item_attribute')).val(null);

    // Set attributes
    var specData = this.specs[specName];
    for (var attrName in specData['attrs']) {
        var value = specData['attrs'][attrName];
        $("#i_" + attrName).val(value);
    }
    // Set class
    $('#class').dropdown('set selected', specData['class']);

    this.submitPressed();
};