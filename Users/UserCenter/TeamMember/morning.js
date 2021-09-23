// Please import this file after jQuery has been imported

var line_num = 0;

function add_line(Button) {
    let d = `<div class="form-row align-items-center border-bottom mb-3">
                <div class="col-md-1">
                    <button type="button" onclick="remove_line(this)" class="btn btn-sm btn-danger btn-rounded mb-3"> X </button>
                </div>
                <div class="form-group col-md-5">
                    <label for="morning-absent-name-${line_num}">姓名：</label>
                    <input type="text" class="form-control" id="morning-absent-name-${line_num}" required>
                </div>
                <div class="form-group col-md-5">
                    <label for="morning-absent-studentID-${line_num}">学号：</label>
                    <input type="text" class="form-control" id="morning-absent-studentID-${line_num}"
                           required>
                </div>
            </div>`;
    $(Button).parent().before(d);
    line_num++;
}

function remove_line(Button) {
    if (line_num > 0) {
        line_num--;
        $(Button).parent().parent().remove();
        refresh_Line_ID();
    }
}

function refresh_Line_ID() {
    let a = $("#morning-absent-upload").children().first().children().first();
    let i = 0;
    while(a.hasClass("align-items-center")) {
        let firstInput = a.children().first().next();
        let secondInput = firstInput.next().children().last();
        firstInput = firstInput.children().last();

        firstInput.attr("id",`morning-absent-name-${i}`);
        secondInput.attr("id",`morning-absent-studentID-${i}`);

        i++;
        a = a.next();
    }
    if (i!==line_num) {
        alert("缺勤表录入页可能遇到问题，请刷新页面，或提交后重新检查名单数据");
        line_num = i;
    }
}