@if(session('success'))<div class="msh-alert msh-alert-ok">{{ session('success') }}</div>@endif
@if(session('error'))<div class="msh-alert msh-alert-err">{{ session('error') }}</div>@endif
@if(isset($errors) && $errors->any())<div class="msh-alert msh-alert-err"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
