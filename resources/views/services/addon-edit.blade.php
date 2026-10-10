@extends('layouts.app')

@section('title', 'Edit Grooming Add-on')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <a href="{{ route('services.index') }}" class="inline-flex min-h-9 items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-emerald-800">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6M9 12h12"/></svg>
            Back to Services
        </a>

        <header class="overflow-hidden rounded-xl border border-emerald-950 border-l-4 border-l-amber-400 bg-emerald-950 px-5 py-5 text-white shadow-sm sm:px-7 sm:py-6">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-200">Service management / Add-ons</p>
            <h1 class="mt-2 text-2xl font-bold text-white">Edit Grooming Add-on</h1>
            <p class="mt-1 text-sm text-emerald-100">Update the details, pricing, and availability for this extra.</p>
        </header>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('grooming-addons.update', $groomingAddon) }}" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @csrf
            @method('PUT')
            <div class="grid lg:grid-cols-[minmax(0,1fr)_17rem]">
                <div class="space-y-5 p-5 sm:p-7">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Add-on details</h2>
                        <p class="mt-1 text-sm text-slate-500">Set the name and price shown when this extra is selected.</p>
                    </div>
                    <div>
                        <label for="addon_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Add-on name <span class="text-emerald-800">*</span></label>
                        <input id="addon_name" name="name" value="{{ old('name', $groomingAddon->name) }}" maxlength="255" required class="w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="addon_category" class="mb-1.5 block text-sm font-semibold text-slate-700">Category <span class="text-emerald-800">*</span></label>
                            <select id="addon_category" name="category" required class="w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                                <option value="extra_service" @selected(old('category', $groomingAddon->category ?? 'extra_service') === 'extra_service')>Extra Services</option>
                                <option value="detangling" @selected(old('category', $groomingAddon->category) === 'detangling')>Detangling</option>
                            </select>
                        </div>
                        <div>
                            <label for="addon_rate" class="mb-1.5 block text-sm font-semibold text-slate-700">Additional price <span class="text-emerald-800">*</span></label>
                            <div class="relative">
                                <span aria-hidden="true" class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm font-semibold text-slate-500">₱</span>
                                <input id="addon_rate" type="number" name="rate" min="0" step="0.01" value="{{ old('rate', $groomingAddon->rate) }}" required class="w-full rounded-lg border-slate-300 bg-white pl-8 text-sm tabular-nums shadow-sm focus:border-emerald-700 focus:ring-emerald-700">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="addon_description" class="mb-1.5 block text-sm font-semibold text-slate-700">Description <span class="font-normal text-slate-400">(optional)</span></label>
                        <textarea id="addon_description" name="description" rows="4" maxlength="1000" class="w-full resize-y rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-emerald-700 focus:ring-emerald-700">{{ old('description', $groomingAddon->description) }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-500">A short note can help staff distinguish similar add-ons.</p>
                    </div>
                </div>

                <aside class="border-t border-slate-200 bg-slate-50/70 p-5 sm:p-7 lg:border-l lg:border-t-0">
                    <h2 class="text-base font-bold text-slate-900">Availability</h2>
                    <p class="mt-1 text-sm leading-5 text-slate-500">Choose whether this add-on can be selected during grooming.</p>
                    <input type="hidden" name="active" value="0">
                    <label for="addon_active" class="mt-4 flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 bg-white p-3.5 transition hover:border-emerald-300">
                        <input id="addon_active" type="checkbox" name="active" value="1" @checked(old('active', $groomingAddon->active)) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-700">
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">Active</span>
                            <span class="mt-0.5 block text-xs leading-5 text-slate-500">Available to add to grooming visits.</span>
                        </span>
                    </label>

                    <div class="mt-6 border-t border-slate-200 pt-5">
                        <h3 class="text-sm font-bold text-slate-800">Category behavior</h3>
                        <div class="mt-3 space-y-4">
                            <div>
                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">Detangling</span>
                                <p class="mt-1.5 text-xs leading-5 text-slate-600">Customers choose one detangling option per pet.</p>
                            </div>
                            <div>
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-900">Extra Services</span>
                                <p class="mt-1.5 text-xs leading-5 text-slate-600">Extra services can be selected together.</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-7">
                <a href="{{ route('services.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancel</a>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-emerald-800 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:ring-offset-2">Save Add-on</button>
            </div>
        </form>
    </div>
</div>
@endsection
