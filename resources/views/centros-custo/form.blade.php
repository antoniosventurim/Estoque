<x-app.layout>
    <x-app.page-header
        title="{{ $costCenter ? 'Editar Centro de Custo' : 'Novo Centro de Custo' }}"
        subtitle="{{ $costCenter ? $costCenter->name : 'Cadastre um curso, setor ou laboratório' }}"
        icon="building"
    >
        <x-app.btn as="a" href="{{ route('centros-custo.index') }}" variant="back">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <x-app.card class="p-5">
        <form method="POST" action="{{ $costCenter ? route('centros-custo.update', $costCenter) : route('centros-custo.store') }}" class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
            @csrf
            @if ($costCenter)@method('PUT')@endif

            <div class="sm:col-span-2">
                <x-app.input label="Nome *" name="name" value="{{ old('name', $costCenter->name ?? '') }}" placeholder="Ex: Medicina" required />
            </div>

            <x-app.searchable-select
                label="Tipo *"
                name="type"
                placeholder="Buscar tipo..."
                :required="true"
                :selected="old('type', $costCenter->type ?? 'curso')"
                :selected-label="old('type', $costCenter->type ?? 'curso') ? (['curso' => 'Curso', 'setor' => 'Setor', 'laboratorio' => 'Laboratório', 'administrativo' => 'Administrativo'][old('type', $costCenter->type ?? 'curso')] ?? '') : ''"
                :options="collect([['id' => 'curso', 'name' => 'Curso', 'sub' => ''], ['id' => 'setor', 'name' => 'Setor', 'sub' => ''], ['id' => 'laboratorio', 'name' => 'Laboratório', 'sub' => ''], ['id' => 'administrativo', 'name' => 'Administrativo', 'sub' => '']])"
            />

            <x-app.input label="Código" name="code" value="{{ old('code', $costCenter->code ?? '') }}" placeholder="Ex: MED" mono maxlength="20" />

            <div class="flex items-end sm:col-span-2">
                <x-bladewind::checkbox
                    name="is_active"
                    value="1"
                    label="Centro de custo ativo"
                    color="blue"
                    add_clearing="false"
                    :checked="(bool) old('is_active', $costCenter->is_active ?? true)"
                />
            </div>

            <div class="flex gap-2 sm:col-span-2">
                <x-app.btn type="submit" icon="check">{{ $costCenter ? 'Salvar alterações' : 'Cadastrar Centro de Custo' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('centros-custo.index') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>
</x-app.layout>