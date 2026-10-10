@if(\App\Models\GeneratedReport::query()->exists())
    <form method="POST" action="{{ route('reports.destroy-all') }}" onsubmit="return confirm('Move all saved reports to Archive? Their files will be kept and can be restored later.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
            Delete All
        </button>
    </form>
@endif
