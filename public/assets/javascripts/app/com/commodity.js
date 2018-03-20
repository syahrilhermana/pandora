(function() {
    $(document).ready(function() {
        //
    });
}).call(this);

function groupAdd(k, n, c) {
    $("#groupCode").text(c);
    $("#groupId").val('');
    $("#groupCodeShow").val('');
    $("#groupName").val('');
    $("#groupParent").val(k);
    $("#groupParentShow").val(n);

    $("#groupParentShow").val(n);
    $("#groupParent").val(k);
    $("#groupCode").text(c);
}

function groupEdit(token) {
    var data = token.split("~");
    $("#groupCode").text('');
    $("#groupId").val(data[0]);
    $("#groupCodeShow").val(data[1]);
    $("#groupName").val(data[2]);
    $("#groupParent").val(data[3]);
    $("#groupParentShow").val(data[4]);
    $("#groupCode").text(data[5]);
}

function uomEdit(token) {
    var data = token.split("~");
    $("#uomCode").val(data[0]);
    $("#uomId").val(data[1]);
    $("#uomName").val(data[2]);
}

function switchType(object) {
    if(object.value === 'S') {
        $(".goods").hide();
    } else {
        $(".goods").show();
    }
}

function generateCode(uri, code) {
    $.ajax({
        url: uri,
        method: "GET",
        data: {
            token: code.value
        },
        success: function (response) {
            $('#catalog-code').val(response.newcode);
        }
    });
}