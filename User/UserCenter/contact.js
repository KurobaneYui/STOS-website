// please import this document when jQuery has been imported

function add_table(table_rows, table_cols) {
    // required
    if (table_rows < 0)
        throw new RangeError("table_rows in add_nav_tab <0");
    if (table_cols < 0)
        throw new RangeError("table_cols in add_nav_tab <0");

    // add table
    var table_head_tr = `<tr class="table-info">` + `<th scope="col">#</th>`.repeat(table_cols) + `</tr>`;
    var table_body_trs = (`<tr><th scope="row">a</th>` + `<td></td>`.repeat(table_cols - 1) + `</tr>`).repeat(table_rows);
    var table_content =
        `<div class="table-responsive">
            <table class="table table-striped" id="team-member-info-table">
                <thead>
                    ${table_head_tr}
                </thead>
                <tbody>
                    ${table_body_trs}
                </tbody>
            </table>
        </div>`;
    $("#team-member-info div").append(table_content);
    return `team-member-info-table`;
}


$("#testbutton").click(function () {
    add_table(5, 8);
});
