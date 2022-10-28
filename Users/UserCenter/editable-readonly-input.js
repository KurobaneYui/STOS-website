var someElementEditable = false;

function editable_readonly_input() {
    elements = $("input.editable-readonly-input");
    elements.prop("readonly", true);
    elements.removeClass('form-control');
    elements.addClass('form-control-plaintext');
    elements.on("dblclick", function () {
        change_content_editable(this);
        someElementEditable = true;
    });
    elements.on("blur", function () {
        if (someElementEditable) {
            change_content_uneditable(this);
            someElementEditable = false;
        }
    });
}