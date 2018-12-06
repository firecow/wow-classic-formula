/**
 * @constructor
 */
WowClassicFormula = function() {

};

WowClassicFormula.prototype.submitPressed = function() {
    var form = document.getElementById("form");
    var formData = new FormData(form);
    var request = new XMLHttpRequest();
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
WowClassicFormula.prototype.specPressed = function(event) {

    $("#i_healing").val("1");
    $("#i_spellDmg").val("1");
    $("#i_mana5").val("2.5");
    $("#i_intellect").val("0.6");
    $("#i_spirit").val("0.35");

    $('#class').dropdown('set selected', "Priest");

    this.submitPressed();
};