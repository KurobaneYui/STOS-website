// please import this document when jQuery has been imported

function add_table(navContentID, table_rows, table_cols) {
    // required
    if (table_rows < 0)
        throw new RangeError("table_rows in add_nav_tab <0");
    if (table_cols < 0)
        throw new RangeError("table_cols in add_nav_tab <0");

    // add table
    var table_head_tr = `<tr class="table-info">` + `<th scope="col">#</th>`.repeat(table_cols) + `</tr>`;
    var table_body_trs = (`<tr><th scope="row">a</th>` + `<td></td>`.repeat(table_cols - 1) + `</tr>`).repeat(table_rows);
    var table_content =
        `<table class="table table-striped" id="${navContentID}-table">
            <thead>
                ${table_head_tr}
            </thead>
            <tbody>
                ${table_body_trs}
            </tbody>
        </table>`;
    $("#" + navContentID + " div div").append(table_content);
    return `${navContentID}-table`;
}

function add_nav_tab(tab_name, card_title, card_subtitle) {
    // add counter
    if (add_nav_tab.counter == undefined) {
        add_nav_tab.counter = 0;
    } else {
        add_nav_tab.counter++;
    }

    // add nav content
    var card_content =
        `<div class="tab-pane fade" id="group${add_nav_tab.counter}-member-info" role="tabpanel" aria-labelledby="group${add_nav_tab.counter}-member-info-tab">
            <!-- bootstrap card -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">${card_title}</h4>
                    <h5 class="card-subtitle">${card_subtitle}</h5>
                </div>
            </div>
        </div>`;
    var basecontent = $("#team-member-info");
    basecontent.before(card_content);


    // add nav tab
    var tab_content =
        `<a class="nav-link" id="group${add_nav_tab.counter}-member-info-tab" data-toggle="tab" href="#group${add_nav_tab.counter}-member-info" role="tab" aria-controls="group${add_nav_tab.counter}-member-info" aria-selected="false">${tab_name}</a>`;
    var basetab = $("#team-member-info-tab");
    basetab.before(tab_content);

    // select first tab
    $("#member-info-tab a:first-child").click();
    return `group${add_nav_tab.counter}-member-info`;
}

$("#testbutton").click(function () {
    add_nav_tab('testTAB', 'title', 'subtitle');
    add_table("group0-member-info", 8, 5);
    add_table("team-member-info", 5, 8);
});
