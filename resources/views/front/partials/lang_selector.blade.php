<select class=" hvr-fade form-control choose_lang">
    @foreach ($languages as $lang)
        <option {{\App::getLocale() == $lang->code?'selected':''}} value="{{ $lang->code }}">{{ $lang->full_name }}</option>
    @endforeach
</select>
