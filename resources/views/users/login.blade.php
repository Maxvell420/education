<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorization Form</title>
    <link rel="stylesheet" type="text/css" href={{asset('/style.css')}}>
    <style>
        .container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        .fcc-btn {
            background-color: #ffffff;
            color: #3498db;
        }
        .form-group input[type="submit"] {
            background-color: #3498db;
            color: #ffffff;
        }
        .form-group input[type="submit"]:hover {
            background-color: #010be5;
            color: #ffffff;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>Authorization Form</h2>
    <form method="POST" action="{{ route('auth',$user->id) }}">
        @csrf

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" >
        </div>

        <div class="form-group">
            @error("email"){{$message}}
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" >
        </div>

        <div class="form-group">
            @error("password"){{$message}}
            @enderror
        </div>

        <div class="form-group">
            <input type="submit" value="Login">
        </div>
    </form>
    <p>Don't have an account? <a target="_blank" class="fcc-btn" href="{{ route('users/create') }}">Sign Up</a></p>
</div>
</body>

</html>

