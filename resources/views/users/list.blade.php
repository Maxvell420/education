<header/>
<style type="text/css">
    button[name="registration"],[name="delete"],[name="login"] {
        border: none;
        border-radius: 7px;
        padding: 10px 25px;
        background: #071575;
        cursor: pointer;
        text-transform: uppercase;
        font-weight: bold;
        color: white;
    }
    button[name="delete"]:hover,[name="registration"]:hover {
        background: deepskyblue;
    }
</style>
@foreach($users as $user)
                {{$user->id}} user
                <a href="{{route("users.show",$user->id)}}"> show user</a>
                <a href="{{route("users.edit",$user->id)}}"> edit user</a>
                <form method="post" action="{{route("users.update",$user->id)}}">
        <button type="submit" name="delete"> delete </button>
        @csrf
        @method("DELETE")
        </form>
@endforeach

<form action="{{route("users.create")}}">
    <button type="submit" name="registration">
        registration
    </button>
</form>
<form action="{{route("login")}}">
    <button type="submit" name="login">
        login
    </button>
</form>
{{$users}}
