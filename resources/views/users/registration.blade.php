
<form method="post"  action="{{route("users/store")}}">
        @csrf
    <input name="name" placeholder="name">
    <input name="email" placeholder="email">
    <input name="password" placeholder="password">
    <button type="submit"> Зарегистрироваться </button>
    </form>

