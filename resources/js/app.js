// import { createApp, h } from "vue";
// import { createInertiaApp } from "@inertiajs/inertia-vue3";
// import { InertiaProgress } from "@inertiajs/progress";
//
// InertiaProgress.init();
//
// createInertiaApp({
//     resolve: (name) => require(`./Pages/${name}`),
//     setup({ el, App, props, plugin }) {
//         createApp({ render: () => h(App, props) })
//             .use(plugin)
//             .mount(el);
//     },
// });

var fileobj;
function upload_file(e) {
    e.preventDefault();
    console.log('f ');
    fileobj = e.dataTransfer.files[0];
    token = document.getElementById('_token').value;
    ajax_file_upload(fileobj, token);
}

function file_explorer() {
    document.getElementById('selectfile').click();
    document.getElementById('selectfile').onchange = function() {
        console.log('abc');
        fileobj = document.getElementById('selectfile').files[0];
        token = document.getElementById('_token').value;
        ajax_file_upload(fileobj, token);
    };
}

function ajax_file_upload(file_obj, token) {
    if(file_obj != undefined) {
        document.getElementById('loading').innerHTML = '<br />Aan het uploaden...';
        var form_data = new FormData();
        form_data.append('file', file_obj);
        form_data.append('_token', token)
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "ajax.php", true);
        xhttp.onload = function(event) {
            oOutput = document.querySelector('.img-content');
            oOutput.innerHTML = 'hoi' + xhttp.status;
            // if (xhttp.status == 200 || xhttp.status == 202) {
            //     console.log(event, xhttp, this.responseText);
            //     oOutput.innerHTML = "<img src='"+ this.responseText +"' alt='The Image' />";
            // } else {
            //     console.log('hoi');
            //     console.log(event, this.responseText);
            //     oOutput.innerHTML = "Error " + xhttp.status + " occurred when trying to upload your file.";
            // }
        }

        xhttp.send(form_data);
    }
}
