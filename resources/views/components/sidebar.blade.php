<div class="sidebar" style="height: 800px">
    <div style="margin: auto; width: 150px; height: 200px;text-align: center;background: radial-gradient(#ffffff, #3498db);">
        <div class="avatar">
            <img src={{asset('logos/avatar.png')}} alt='Avatar'>
        </div>
        {{$user->name}}
        {{$user->email}}
        <a href={{route('users.settings')}}>Изменить данные</a>
    </div>
</div>
