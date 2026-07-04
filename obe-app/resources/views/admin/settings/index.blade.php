<x-sidebar-layout :title="'Pengaturan Sistem'" :header="'Pengaturan Sistem'">

    <div class="obe-card">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf @method('PUT')

            @foreach($settings as $setting)
                <div class="mb-4">
                    <label for="{{ $setting->key }}" class="form-label fw-semibold">{{ $setting->label }}</label>
                    @if($setting->type === 'textarea')
                        <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" rows="4" class="form-control">{{ $setting->value }}</textarea>
                    @else
                        <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" value="{{ $setting->value }}" class="form-control">
                    @endif
                </div>
            @endforeach

            <div class="d-flex justify-content-end pt-3 border-top">
                <button type="submit" class="btn btn-obe-red">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-sidebar-layout>
