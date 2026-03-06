@props([
    'id' => '',
    'name' => '',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Seleccione una opción',
    'required' => false,
    'class' => '',
    'valueField' => 'id',
    'labelField' => 'name',
    'allowClear' => true,
    'dropdownParent' => null,
])

<select
    id="{{ $id }}"
    name="{{ $name }}"
    class="searchable-select block w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 shadow-sm focus:border-brand-500 focus:ring-brand-500 {{ $class }}"
    {{ $required ? 'required' : '' }}
    data-placeholder="{{ $placeholder }}"
    data-allow-clear="{{ $allowClear ? 'true' : 'false' }}"
    @if($dropdownParent) data-dropdown-parent="{{ $dropdownParent }}" @endif
    {{ $attributes }}
>
    <option value="">{{ $placeholder }}</option>
    @foreach($options as $option)
        @php
            $value = is_array($option) ? $option[$valueField] : $option->{$valueField};
            $label = is_array($option) ? $option[$labelField] : $option->{$labelField};
            $isSelected = $selected == $value;
        @endphp
        <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
