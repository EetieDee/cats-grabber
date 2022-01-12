<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <script src="js/script.js"></script>
</head>
<body>
<center>
<img src="img/ukomst-logo.svg" width="300" style="margin: 20px" />
<div style="color: #00BED1; font-family: 'Arial'; font-size: 20px; margin: 10px">Overheids-pdfbestand-scraper</div>
<div id="drop_file_zone" ondrop="upload_file(event)" ondragover="return false">
    <div id="drag_upload_file">
        <p style="color: #00BED1; font-family: 'Arial'; font-size: 13px">Drop hier het pdf-bestand</p>
        {!! csrf_field() !!}
        <p  style="color: #00BED1; font-family: 'Arial'; font-size: 13px">- of -</p>
        <p><input type="button" value="Select File" onclick="file_explorer();" /></p>
        <input type="file" id="selectfile" />
    </div>
</div>
<div class="img-content" id="loading" style="color: #00BED1; font-family: 'Arial'; font-size: 20px; margin: 10px"></div>

</center>
</body>
</html>
