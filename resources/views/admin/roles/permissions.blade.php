<x-app-layout>


<x-slot name="header">

<div class="flex justify-between">

<div>

<h2 class="text-xl font-semibold">
Atur Permission Role
</h2>

<p class="text-sm text-gray-500">
{{ $role->display_name }}
</p>

</div>


<a
href="{{ route('admin.roles.show',$role) }}"
class="border px-4 py-2 rounded"
>
Kembali
</a>


</div>

</x-slot>



<div class="py-8">

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


<form method="POST"
action="{{ route('admin.roles.permissions.update',$role) }}">


@csrf

@method('PUT')



<div class="bg-white rounded shadow p-6">


<h3 class="font-semibold mb-5">
Daftar Permission
</h3>



<div class="space-y-6">


@foreach($permissions as $module=>$items)


<div class="border rounded p-4">


<h4 class="font-semibold text-gray-700 mb-3 uppercase">

{{ $module }}

</h4>



<div class="grid md:grid-cols-3 gap-3">


@foreach($items as $permission)


<label class="flex items-center gap-2">


<input
type="checkbox"
name="permissions[]"
value="{{ $permission->id }}"

@if(in_array(
$permission->id,
$rolePermissionIds
))

checked

@endif

>


<div>

<div class="font-mono text-sm">

{{ $permission->name }}

</div>


<div class="text-xs text-gray-500">

{{ $permission->display_name }}

</div>

</div>


</label>


@endforeach


</div>


</div>


@endforeach



</div>




<div class="mt-6">


<button
type="submit"
class="bg-green-700 text-white px-5 py-2 rounded"
>

Simpan Permission

</button>


</div>



</div>


</form>


</div>

</div>


</x-app-layout>