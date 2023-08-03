<form action="{{route("userstore",$course->id)}}" method="POST">
    @csrf
<input name="email" placeholder="enter email of user that you want to add to current course">
    <button type="submit">add user to course </button>
</form>
