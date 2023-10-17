
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href={{asset('/style.css')}}>
    <title>Your Learning Platform</title>
</head>
<div style="font-family: system-ui">
    <x-header>
    </x-header>
    <x-body>
        {{$slot}}
    </x-body>
</div>
</html>

