@extends('layouts.app')

@section('title', 'Edit Pet')

@section('content')

<div class="mx-auto max-w-4xl space-y-6">

    <div class="flex flex-col gap-4 rounded-xl border border-emerald-900 bg-emerald-950 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div>
            <h1 class="text-2xl font-bold text-white">
                Edit Pet
            </h1>

            <p class="mt-1 text-sm text-emerald-100/80">
                Update {{ $pet->name }}'s information.
            </p>
        </div>

        <a href="{{ route('customers.show', $pet->customer) }}"
           class="inline-flex items-center justify-center rounded-lg border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20">
            ← Back
        </a>
    </div>


    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 sm:px-6">
            <h2 class="font-bold text-slate-900">Pet Information</h2>
            <p class="mt-1 text-sm text-slate-500">Update {{ $pet->name }}'s saved details.</p>
        </div>
        <div class="p-5 sm:p-6">

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                <div class="font-semibold text-red-700 mb-2">
                    Please fix the following:
                </div>

                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form method="POST"
              action="{{ route('pets.update', $pet) }}"
              class="space-y-6"
              x-data="{ species: @js(old('species', $pet->species)), petSize: @js(old('weight_category', $pet->weight_category)) }">

            @csrf
            @method('PUT')


            <div>
                <label for="name"
                       class="block text-sm font-medium text-slate-700 mb-2">
                    Pet Name <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $pet->name) }}"
                    required
                    class="w-full rounded-lg border-slate-300 focus:border-emerald-700 focus:ring-emerald-700"
                >

                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label for="species" class="block text-sm font-medium text-slate-700 mb-2">Species <span class="text-red-500">*</span></label>
                    <select id="species" name="species" x-model="species" @change="if (species === 'Cat') petSize = ''" required class="w-full rounded-lg border-slate-300 focus:border-emerald-700 focus:ring-emerald-700">
                        <option value="">Select species</option>
                        @foreach(['Dog', 'Cat', 'Other'] as $speciesOption)
                            <option value="{{ $speciesOption }}" @selected(old('species', $pet->species) === $speciesOption)>{{ $speciesOption }}</option>
                        @endforeach
                    </select>

                    @error('species')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                <div>
                    <label for="breed"
                           class="block text-sm font-medium text-slate-700 mb-2">
                        Breed
                    </label>

                    <input
                        type="text"
                        id="breed"
                        name="breed"
                        value="{{ old('breed', $pet->breed) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-700 focus:ring-emerald-700"
                    >

                    @error('breed')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label for="sex"
                           class="block text-sm font-medium text-slate-700 mb-2">
                        Sex
                    </label>

                    <select
                        id="sex"
                        name="sex"
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-700 focus:ring-emerald-700"
                    >
                        <option value="">Select sex</option>

                        <option value="male"
                            {{ old('sex', $pet->sex) === 'male' ? 'selected' : '' }}>
                            Male
                        </option>

                        <option value="female"
                            {{ old('sex', $pet->sex) === 'female' ? 'selected' : '' }}>
                            Female
                        </option>
                    </select>
                </div>


                <div>
                    <label for="birth_date"
                           class="block text-sm font-medium text-slate-700 mb-2">
                        Birth Date
                    </label>

                    <input
                        type="date"
                        id="birth_date"
                        name="birth_date"
                        value="{{ old('birth_date', optional($pet->birth_date)->format('Y-m-d')) }}"
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-700 focus:ring-emerald-700"
                    >
                </div>

            </div>


            <div x-show="species && species !== 'Cat'" x-cloak>
                <label for="weight_category" class="block text-sm font-medium text-slate-700 mb-2">Size <span class="text-red-500">*</span></label>
                <select id="weight_category" name="weight_category" x-model="petSize" :required="species && species !== 'Cat'" :disabled="!species || species === 'Cat'" class="w-full rounded-lg border-slate-300 focus:border-emerald-700 focus:ring-emerald-700">
                    <option value="">Select size</option>
                    @foreach(['Small', 'Medium', 'Large', 'XLarge'] as $size)
                        <option value="{{ $size }}" @selected(old('weight_category', $pet->weight_category) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
                @error('weight_category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div>
                <label for="notes"
                       class="block text-sm font-medium text-slate-700 mb-2">
                    Notes
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    class="w-full rounded-lg border-slate-300 focus:border-emerald-700 focus:ring-emerald-700"
                >{{ old('notes', $pet->notes) }}</textarea>
            </div>


            <div class="flex flex-col-reverse justify-end gap-3 border-t border-slate-200 pt-4 sm:flex-row">

                <a href="{{ route('customers.show', $pet->customer) }}"
                   class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Update Pet
                </button>

            </div>

        </form>

    </div>

</div>

@endsection