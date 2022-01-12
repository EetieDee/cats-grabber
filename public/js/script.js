var fileobj;
function upload_file(e) {
    e.preventDefault();
    fileobj = e.dataTransfer.files[0];
    token = document.getElementsByName('_token')[0].value;
    ajax_file_upload(fileobj, token);
}

function file_explorer() {
    document.getElementById('selectfile').click();
    document.getElementById('selectfile').onchange = function() {
        fileobj = document.getElementById('selectfile').files[0];
        token = document.getElementsByName('_token')[0].value;
        ajax_file_upload(fileobj, token);
    };
}

function ajax_file_upload(file_obj, token) {
    if(file_obj != undefined) {
        document.getElementById('loading').innerHTML = '<br />Aan het uploaden...';
        var form_data = new FormData();
        form_data.append('file', file_obj);
        form_data.append('_token', token);
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "/drop", true);
        xhttp.onload = function(event) {
            oOutput = document.querySelector('.img-content');
            oOutput.innerHTML = '<br />' + this.responseText;
            // if (xhttp.status == 200) {
            //     oOutput.innerHTML = "<img src='"+ this.responseText +"' alt='The Image' />";
            // } else {
            //     oOutput.innerHTML = "Error " + xhttp.status + " occurred when trying to  upload your file.";
            // }
        }

        xhttp.send(form_data);
    }
}
