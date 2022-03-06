// Please import this file after importing jQuery

function freshPreview() {
    let type = $("#noticeType").prop("value");
    let header = $("#noticeHeader").prop("value");
    let content = $("#noticeContent").val();

    if (type==="team" || type==="management") type="队长";
    else if (type==="") type="";
    else type="组长";

    content = content.replace("\n","<br/>");

    $("#previewHeader").html(header);
    $("#previewContent").html(content);
    $("#previewType").html("FROM "+type);
}

$("#noticeType").change(freshPreview);
$("#noticeHeader").change(freshPreview);
$("#noticeContent").change(freshPreview);
