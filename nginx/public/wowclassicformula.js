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