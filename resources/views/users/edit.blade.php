
<form method="post"  action="{{route("users.update",$user->id)}}">
    @csrf
    @method("PUT")
    <input name="name" placeholder={{$user->name}}>
    <input name="email" placeholder={{$user->email}}>
    <input name="password" placeholder="your password">
    <input type="submit" value="edit">
</form>
@foreach($errors->all() as $error)
    <li> {{$error}}</li>
@endforeach
