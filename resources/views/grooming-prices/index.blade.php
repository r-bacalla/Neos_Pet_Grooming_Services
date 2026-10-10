@extends('layouts.app')

@section('title', 'Grooming Pricing')

@section('content')

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Grooming Pricing
        </h1>

        <p class="text-sm text-slate-500 mt-1">
            Set the price used when a pet is received by size category.
        </p>
    </div>


    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 p-4">
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Add Price --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">

        <h2 class="text-lg font-semibold text-slate-800 mb-4">
            Add Size Price
        </h2>

        <form method="POST"
              action="{{ route('grooming-prices.store') }}"
              class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Pet Size
                </label>

                <select name="category" required class="w-full rounded-lg border-slate-300">
                    <option value="">Select size</option>
                    <option value="Small">Small</option>
                    <option value="Medium">Medium</option>
                    <option value="Large">Large</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Grooming Price
                </label>

                <input type="number"
                       name="price"
                       step="0.01"
                       min="0"
                       required
                       placeholder="₱0.00"
                       class="w-full rounded-lg border-slate-300">
            </div>


            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Add Size Price
            </button>

        </form>

    </div>


    {{-- Existing Prices --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">
                Current Size Pricing
            </h2>
        </div>


        @if($prices->count())

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-slate-50 border-b border-slate-200">

                        <tr>

                            <th class="text-left px-6 py-4">
                                Pet Size
                            </th>

                            <th class="text-left px-6 py-4">
                                Price
                            </th>

                            <th class="text-left px-6 py-4">
                                Status
                            </th>

                            <th class="text-left px-6 py-4">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($prices as $price)

                            <tr class="border-b border-slate-100">

                                <form method="POST"
                                      action="{{ route('grooming-prices.update', $price) }}">

                                    @csrf
                                    @method('PUT')

                                    <td class="px-6 py-4">

                                        <select name="category" required class="rounded-lg border-slate-300">
                                            @foreach(['Small', 'Medium', 'Large'] as $category)
                                                <option value="{{ $category }}" @selected($price->categoryLabel() === $category)>{{ $category }}</option>
                                            @endforeach
                                        </select>

                                    </td>


                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-2">

                                            <span>₱</span>

                                            <input type="number"
                                                   name="price"
                                                   value="{{ $price->price }}"
                                                   step="0.01"
                                                   min="0"
                                                   required
                                                   class="w-32 rounded-lg border-slate-300">

                                        </div>

                                    </td>


                                    <td class="px-6 py-4">

                                        <label class="inline-flex items-center gap-2">

                                            <input type="hidden"
                                                   name="active"
                                                   value="0">

                                            <input type="checkbox"
                                                   name="active"
                                                   value="1"
                                                   {{ $price->active ? 'checked' : '' }}
                                                   class="rounded border-slate-300">

                                            <span class="text-sm">
                                                Active
                                            </span>

                                        </label>

                                    </td>


                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <button type="submit"
                                                    class="text-indigo-600 hover:underline">
                                                Save
                                            </button>

                                </form>


                                            <form method="POST"
                                                  action="{{ route('grooming-prices.destroy', $price) }}"
                                                  onsubmit="return confirm('Move this grooming price to Archive?');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="text-slate-700 hover:underline">
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="p-10 text-center text-slate-500">
                No size prices configured yet. Add Small, Medium, and Large prices to enable pet receiving.
            </div>

        @endif

    </div>

</div>

@endsection