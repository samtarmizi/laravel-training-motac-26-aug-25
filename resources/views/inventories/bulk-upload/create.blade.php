<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel</title>
</head>
<body>
    <h1>Upload Excel File</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="file">Choose Excel file:</label>
        <input type="file" id="file" name="file" accept=".xls,.xlsx" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
