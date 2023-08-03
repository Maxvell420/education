
<form action="{{route("auth",$user->id)}}" method="post">
    @csrf
    <input placeholder="email" name="email">
    @error("email"){{$message}}
    @enderror
    <input placeholder="password" name="password">
    @error("password"){{$message}}
    @enderror
    <button type="submit"> way to go </button>
</form>
<form action="{{route("users/create")}}">
    <button type="submit" name="registration">
        registration
    </button>
</form>

