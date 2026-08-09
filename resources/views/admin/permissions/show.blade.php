<x-app-layout>

<x-slot name="header">

<div class="flex items-center justify-between">

<div>

<h2 class="font-semibold text-xl text-gray-800">
Detail Permission
</h2>

<p class="mt-1 text-sm text-gray-500">
Informasi permission dan role yang menggunakan akses ini.
</p>

</div>


<a
href="{{ route('admin.permissions.index') }}"
class="rounded-md border px-4 py-2 text-sm"
>
Kembali
</a>


</div>

</x-slot>



<div class="py-8">

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">



<div class="bg-white shadow-sm rounded-lg">

<div class="p-6">


<div class="grid grid-cols-1 md:grid-cols-4 gap-6">


<div>

<div class="text-sm text-gray-500">
Permission
</div>

<div class="mt-1 font-mono">
{{ $permission->name }}
</div>

</div>



<div>

<div class="text-sm text-gray-500">
Module
</div>

<div class="mt-1">
{{ $permission->module }}
</div>

</div>



<div>

<div class="text-sm text-gray-500">
Action
</div>

<div class="mt-1">
{{ $permission->action }}
</div>

</div>



<div>

<div class="text-sm text-gray-500">
Status
</div>


<div class="mt-2">

@if($permission->is_active)

<span class="rounded-full bg-green-50 px-2 py-1 text-xs text-green-700">
Aktif
</span>

@else

<span class="rounded-full bg-red-50 px-2 py-1 text-xs text-red-700">
Nonaktif
</span>

@endif


</div>


</div>



</div>



@if($permission->description)

<div class="mt-6">

<div class="text-sm text-gray-500">
Deskripsi
</div>

<p class="mt-1">
{{ $permission->description }}
</p>

</div>

@endif



</div>

</div>





<div class="bg-white shadow-sm rounded-lg">


<div class="p-6">


<h3 class="text-lg font-semibold">
Role yang memiliki permission ini
</h3>


<p class="mt-1 text-sm text-gray-500">
Daftar role yang dapat menggunakan akses ini.
</p>



<div class="mt-6 overflow-x-auto">


<table class="min-w-full divide-y divide-gray-200">


<thead class="bg-gray-50">

<tr>

<th class="px-4 py-3 text-left">
Role
</th>

<th class="px-4 py-3 text-left">
Label
</th>

<th class="px-4 py-3 text-left">
Status
</th>

</tr>

</thead>



<tbody class="divide-y">


@forelse($permission->roles as $role)


<tr>


<td class="px-4 py-3 font-mono">

{{ $role->name }}

</td>


<td class="px-4 py-3">

{{ $role->display_name }}

</td>


<td class="px-4 py-3">


@if($role->is_active)

<span class="text-green-700">
Aktif
</span>

@else

<span class="text-red-700">
Nonaktif
</span>

@endif


</td>


</tr>


@empty


<tr>

<td colspan="3"
class="px-4 py-6 text-center text-gray-500">

Belum ada role.

</td>

</tr>


@endforelse


</tbody>


</table>


</div>


</div>

</div>



</div>

</div>


</x-app-layout>