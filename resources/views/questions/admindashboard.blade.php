<x-layout>
    Список курсов доступных для редактирования:
        @foreach($courses as $course)
            <div>
                {{$course->courseName}}
                <a href="{{route("course.show",$course)}}"> Показать курс </a>
                <a href="{{route("course.edit",$course)}}"> Редактировать курс </a>
            </div>
        @endforeach
</x-layout>

