@extends('admin.layout')
@section('title', 'Categories')
@section('heading', 'Tournament Categories')

@section('content')
@php
    $input = 'w-full rounded-xl bg-white border border-zinc-300 px-3.5 py-2 text-sm text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-brand';
@endphp

<div class="space-y-6">
    <!-- Top Information & Stats Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Stat: Total -->
        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-orange-50 border border-orange-100 flex items-center justify-center text-brand">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Categories</div>
                <div class="text-2xl font-extrabold text-zinc-900">{{ $totalCount }}</div>
            </div>
        </div>

        <!-- Stat: Active -->
        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Active Categories</div>
                <div class="text-2xl font-extrabold text-emerald-600">{{ $activeCount }}</div>
            </div>
        </div>

        <!-- Stat / Info: API Endpoint -->
        <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-sm p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-orange-400">Categories API</span>
                <span class="text-[10px] bg-slate-700 px-2 py-0.5 rounded text-slate-300 font-mono">GET</span>
            </div>
            <div class="mt-2 flex items-center justify-between gap-2 bg-slate-800/80 rounded-xl px-3 py-1.5 border border-slate-700/60">
                <span class="text-xs font-mono text-zinc-200 truncate select-all">{{ url('/api/categories') }}</span>
                <button type="button" onclick="navigator.clipboard.writeText('{{ url('/api/categories') }}'); alert('Copied Categories API URL!');"
                        class="text-xs text-orange-400 hover:text-orange-300 font-medium shrink-0 px-2 py-1 rounded hover:bg-slate-700 transition">
                    Copy
                </button>
            </div>
        </div>
    </div>

    <!-- Add Category Form -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-zinc-100">
            <div>
                <h2 class="text-base font-bold text-zinc-900">Add New Category</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Create game categories like Clash Squad, Full Map BR, Lone Wolf, etc. Tournaments can be assigned under them.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-medium text-rose-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                <!-- Image Picker -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1.5">Category Image / Icon (Optional)</label>
                    <label class="flex flex-col items-center justify-center border-2 border-dashed border-zinc-300 hover:border-brand rounded-2xl p-4 text-center cursor-pointer transition-colors bg-zinc-50/50 hover:bg-orange-50/20 group aspect-video">
                        <img id="newCatPreview" src="" alt="Preview" class="w-full h-full object-cover rounded-xl hidden">
                        <div id="newCatPlaceholder" class="flex flex-col items-center justify-center">
                            <svg class="w-8 h-8 text-zinc-400 group-hover:text-brand transition-colors mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-xs font-semibold text-zinc-700 group-hover:text-brand">Click to choose image</span>
                            <span class="text-[10px] text-zinc-400 mt-0.5">PNG, JPG, WebP</span>
                        </div>
                        <input type="file" name="image" accept="image/*" class="hidden" onchange="previewCatImage(event, 'newCatPreview', 'newCatPlaceholder')">
                    </label>
                </div>

                <!-- Title & Short Description & Order -->
                <div class="md:col-span-2 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Category Title <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Clash Squad 4v4" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 mb-1">Display Order</label>
                            <input type="number" name="order" value="{{ old('order', 0) }}" min="0" placeholder="0" class="{{ $input }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 mb-1">Short Description (Optional)</label>
                        <textarea name="short_description" rows="2" placeholder="Brief info about this match format or rules..." class="{{ $input }}">{{ old('short_description') }}</textarea>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand focus:ring-brand border-zinc-300">
                            <span>Active (Visible on App/API)</span>
                        </label>

                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand via-orange-600 to-amber-500 hover:opacity-95 text-white px-5 py-2.5 text-sm font-bold shadow-md shadow-orange-500/20 transition-all">
                            <span>+ Create Category</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Categories List -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 pb-4 border-b border-zinc-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-zinc-900">All Categories ({{ $categories->count() }})</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Manage tournament categories and see linked matches.</p>
            </div>
        </div>

        @if ($categories->isEmpty())
            <div class="text-center py-12 px-4">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-orange-50 flex items-center justify-center text-orange-500 mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div class="text-sm font-bold text-zinc-800">No categories created yet</div>
                <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">Create your first category above to group your tournament matches.</p>
            </div>
        @else
            <div class="divide-y divide-zinc-100">
                @foreach ($categories as $cat)
                    <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-zinc-50/60 transition-colors">
                        <!-- Left: Image, Title, Description -->
                        <div class="flex items-start gap-4 min-w-0">
                            <!-- Image Thumbnail -->
                            <div class="w-16 h-12 rounded-xl bg-zinc-100 border border-zinc-200/80 overflow-hidden shrink-0 flex items-center justify-center">
                                @if ($cat->image_url)
                                    <img src="{{ $cat->image_url }}" alt="{{ $cat->title }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-sm text-zinc-900 truncate">{{ $cat->title }}</h4>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-zinc-100 text-zinc-600">
                                        #{{ $cat->order }}
                                    </span>
                                    @if ($cat->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-100 text-zinc-500">
                                            Inactive
                                        </span>
                                    @endif
                                </div>

                                @if ($cat->short_description)
                                    <p class="text-xs text-zinc-500 mt-1 line-clamp-1">{{ $cat->short_description }}</p>
                                @endif

                                <div class="flex items-center gap-3 mt-2 text-[11px] text-zinc-500">
                                    <a href="{{ route('admin.tournaments.index', ['category_id' => $cat->id]) }}" class="inline-flex items-center gap-1 font-semibold text-orange-600 hover:underline">
                                        <span>🎮 {{ $cat->tournaments_count }} {{ Str::plural('Tournament', $cat->tournaments_count) }}</span>
                                    </a>
                                    <span>•</span>
                                    <span>API: <code class="text-zinc-600 font-mono">/api/categories/{{ $cat->id }}/tournaments</code></span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
                            <!-- Toggle Active Form -->
                            <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-zinc-200 {{ $cat->is_active ? 'bg-white text-zinc-700 hover:bg-zinc-50' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }} transition">
                                    {{ $cat->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            <!-- Edit Trigger Button (Toggles inline edit) -->
                            <button type="button" onclick="document.getElementById('editCat_{{ $cat->id }}').classList.toggle('hidden')"
                                    class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 transition">
                                Edit
                            </button>

                            <!-- Delete Form -->
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category? Linked tournaments will become uncategorized.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Inline Edit Drawer/Form (Hidden by default) -->
                    <div id="editCat_{{ $cat->id }}" class="hidden p-5 bg-zinc-50/80 border-t border-b border-zinc-200/60">
                        <form method="POST" action="{{ route('admin.categories.update', $cat) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <div class="font-bold text-xs text-zinc-800 uppercase tracking-wider mb-2">Edit Category: {{ $cat->title }}</div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Title</label>
                                    <input type="text" name="title" value="{{ $cat->title }}" required class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Display Order</label>
                                    <input type="number" name="order" value="{{ $cat->order }}" min="0" class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Change Image</label>
                                    <input type="file" name="image" accept="image/*" class="{{ $input }} text-xs py-1.5">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 mb-1">Short Description</label>
                                <textarea name="short_description" rows="2" class="{{ $input }}">{{ $cat->short_description }}</textarea>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <label class="flex items-center gap-2 text-xs font-medium text-zinc-700 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" @checked($cat->is_active) class="w-4 h-4 rounded text-brand">
                                    <span>Active</span>
                                </label>

                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="document.getElementById('editCat_{{ $cat->id }}').classList.add('hidden')"
                                            class="text-xs font-semibold px-4 py-2 rounded-xl bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 transition">
                                        Cancel
                                    </button>
                                    <button type="submit" class="text-xs font-semibold px-4 py-2 rounded-xl bg-brand text-white hover:bg-brand-dark transition shadow-sm">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    function previewCatImage(event, previewId, placeholderId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection

