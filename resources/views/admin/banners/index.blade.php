@extends('admin.layout')
@section('title', 'Pictures & Banners')
@section('heading', 'Pictures & Banners')

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
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Pictures</div>
                <div class="text-2xl font-extrabold text-zinc-900">{{ $totalCount }}</div>
            </div>
        </div>

        <!-- Stat: Active -->
        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Live on API</div>
                <div class="text-2xl font-extrabold text-emerald-600">{{ $activeCount }}</div>
            </div>
        </div>

        <!-- Stat / Info: API Endpoint -->
        <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-sm p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-orange-400">Public API Endpoint</span>
                <span class="text-[10px] bg-slate-700 px-2 py-0.5 rounded text-slate-300 font-mono">GET</span>
            </div>
            <div class="mt-2 flex items-center justify-between gap-2 bg-slate-800/80 rounded-xl px-3 py-1.5 border border-slate-700/60">
                <span class="text-xs font-mono text-zinc-200 truncate select-all">{{ url('/api/banners') }}</span>
                <button type="button" onclick="navigator.clipboard.writeText('{{ url('/api/banners') }}'); alert('Copied API URL to clipboard!');"
                        class="text-xs text-orange-400 hover:text-orange-300 font-medium shrink-0 px-2 py-1 rounded hover:bg-slate-700 transition">
                    Copy
                </button>
            </div>
        </div>
    </div>

    <!-- Upload Picture Form -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-zinc-100">
            <div>
                <h2 class="text-base font-bold text-zinc-900">Post a New Picture</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Upload pictures here. You can post multiple pictures, and they will all be served through the API.</p>
            </div>
            <span class="hidden sm:inline-flex text-xs text-zinc-400 font-medium">JPEG, PNG, WebP or GIF up to 5MB</span>
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

        <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Image Picker with live preview -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 mb-1.5">Choose Picture <span class="text-rose-500">*</span></label>
                <div class="flex flex-col sm:flex-row items-start gap-4">
                    <!-- Drop/File Input Box -->
                    <label class="w-full sm:w-2/3 flex flex-col items-center justify-center border-2 border-dashed border-zinc-300 hover:border-brand rounded-2xl p-6 text-center cursor-pointer transition-colors bg-zinc-50/50 hover:bg-orange-50/20 group">
                        <svg class="w-10 h-10 text-zinc-400 group-hover:text-brand transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span class="text-sm font-semibold text-zinc-700 group-hover:text-brand">Click to select an image</span>
                        <span class="text-xs text-zinc-400 mt-1">Recommended: 16:9 banner or promo image</span>
                        <input type="file" name="image" id="imageInput" accept="image/*" required class="hidden" onchange="previewImage(event)">
                    </label>

                    <!-- Preview Container -->
                    <div id="previewContainer" class="w-full sm:w-1/3 aspect-video bg-zinc-100 rounded-2xl border border-zinc-200 flex items-center justify-center overflow-hidden relative">
                        <img id="imagePreview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <div id="previewPlaceholder" class="text-xs text-zinc-400 text-center p-3">
                            <svg class="w-8 h-8 mx-auto text-zinc-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Image preview will appear here
                        </div>
                    </div>
                </div>
            </div>

            <!-- Title & Link & Order Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Title (Optional)</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Free Fire Mega Cup" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Link URL (Optional)</label>
                    <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://example.com/tournament" class="{{ $input }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 mb-1">Display Order</label>
                    <input type="number" name="order" value="{{ old('order', 0) }}" min="0" placeholder="0" class="{{ $input }}">
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-zinc-100">
                <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-brand focus:ring-brand border-zinc-300">
                    <span>Activate immediately on API</span>
                </label>

                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-brand via-orange-600 to-amber-500 hover:opacity-95 text-white px-6 py-2.5 text-sm font-bold shadow-md shadow-orange-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Upload & Post</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Uploaded Pictures Section -->
    <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-zinc-900">Uploaded Pictures ({{ $banners->count() }})</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Manage existing pictures, toggle visibility or copy direct URLs.</p>
            </div>
        </div>

        @if ($banners->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-zinc-200 rounded-2xl">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-orange-50 flex items-center justify-center text-orange-500 mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="text-sm font-bold text-zinc-800">No pictures posted yet</div>
                <p class="text-xs text-zinc-500 mt-1 max-w-sm mx-auto">Use the form above to post your pictures. All uploaded pictures will be returned in the API list.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($banners as $b)
                    <div class="rounded-2xl border border-zinc-200/80 overflow-hidden bg-white shadow-xs hover:shadow-md transition-shadow flex flex-col">
                        <!-- Image Container with Badges -->
                        <div class="relative aspect-video bg-zinc-100 overflow-hidden group">
                            <img src="{{ $b->image_url }}" alt="{{ $b->title ?? 'Banner' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                            <!-- Status Badge -->
                            <div class="absolute top-2.5 left-2.5">
                                @if ($b->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/90 text-white backdrop-blur-xs shadow-xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-zinc-800/80 text-zinc-300 backdrop-blur-xs">
                                        Inactive
                                    </span>
                                @endif
                            </div>

                            <!-- Order Badge -->
                            <div class="absolute top-2.5 right-2.5">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-black/60 text-white backdrop-blur-xs">
                                    #{{ $b->order }}
                                </span>
                            </div>

                            <!-- Full Image Link Hover Overlay -->
                            <a href="{{ $b->image_url }}" target="_blank" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                View Full Image
                            </a>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="font-bold text-sm text-zinc-900 truncate" title="{{ $b->title }}">
                                    {{ $b->title ?: 'Untitled Picture' }}
                                </h4>
                                @if ($b->link_url)
                                    <a href="{{ $b->link_url }}" target="_blank" class="text-[11px] text-orange-600 hover:underline truncate block mt-0.5" title="{{ $b->link_url }}">
                                        🔗 {{ $b->link_url }}
                                    </a>
                                @else
                                    <span class="text-[11px] text-zinc-400 block mt-0.5">No link attached</span>
                                @endif
                            </div>

                            <!-- Direct Image URL with quick copy -->
                            <div class="bg-zinc-50 border border-zinc-200/70 rounded-xl p-2 flex items-center justify-between gap-2">
                                <span class="text-[11px] font-mono text-zinc-600 truncate select-all">{{ $b->image_url }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $b->image_url }}'); alert('Copied image URL to clipboard!');"
                                        class="p-1 rounded-md text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200 transition" title="Copy image URL">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>

                            <!-- Actions Row -->
                            <div class="pt-2 border-t border-zinc-100 flex items-center justify-between gap-2">
                                <!-- Quick Toggle Active Status -->
                                <form method="POST" action="{{ route('admin.banners.toggle', $b) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold {{ $b->is_active ? 'text-zinc-500 hover:text-zinc-800' : 'text-emerald-600 hover:text-emerald-700' }} hover:underline">
                                        {{ $b->is_active ? 'Turn Off' : 'Activate' }}
                                    </button>
                                </form>

                                <div class="flex items-center gap-2">
                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" onsubmit="return confirm('Are you sure you want to delete this picture?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('previewPlaceholder');

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
