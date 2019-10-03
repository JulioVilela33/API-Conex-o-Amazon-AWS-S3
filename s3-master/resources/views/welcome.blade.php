<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    
    <title>Teste</title>
</head>
<body>

<form action="#" method="POST" enctype="multipart/form-data">
@csrf
    
			<input type="file" name="file" id="file" />       
			
            <button type="submit" class="btn btn-success">Upload</button>
                      
</form>

</body>
</html>