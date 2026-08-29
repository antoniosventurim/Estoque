@if ($errors->any())
    <div class="mb-4 rounded-md border border-mv-danger-solid/50 bg-mv-danger-bg p-3 text-[14px] text-mv-danger">
        <ul class="ml-4 list-disc space-y-1">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif
