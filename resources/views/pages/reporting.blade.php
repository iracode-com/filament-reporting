@php use IracodeCom\FilamentReporting\Support\Utils; @endphp
@php(
    $tabs = [
        'filtering',
        'settings',
        'preview'
    ]
)

<x-filament-panels::page>


    <div x-data="{ activeTab: $wire.entangle('activeTab').live }">
        <x-filament::tabs >

            <x-filament::tabs.item
                    alpine-active="activeTab === 'filtering'"
                    x-on:click="activeTab = 'filtering'"
                    icon="heroicon-o-funnel"
                    :badge="$record->step > 0 ? __('Done.') : ''"
                    badge-color="success"
                    badge-icon="heroicon-o-check"
            >
                {{ Utils::translate('filtering') }}
            </x-filament::tabs.item>

            @if($record->step > 0)
                <x-filament::tabs.item
                        alpine-active="activeTab === 'settings'"
                        x-on:click="activeTab = 'settings'"
                        icon="heroicon-o-pencil-square"
                        :badge="$record->step > 1 ? __('Done.') : ''"
                        badge-color="success"
                        badge-icon="heroicon-o-check">
                    {{ Utils::translate('settings') }}
                </x-filament::tabs.item>
            @else
                <x-filament::tabs.item icon="heroicon-o-pencil-square">{{ Utils::translate('settings') }}</x-filament::tabs.item>
            @endif

            @if($record->step > 1)
                <x-filament::tabs.item
                        alpine-active="activeTab === 'preview'"
                        x-on:click="activeTab = 'preview'"
                        icon="heroicon-o-clipboard-document"
                        :badge="$record->step > 2 ? __('Done.') : ''"
                        badge-color="success"
                        badge-icon="heroicon-o-check">
                    {{ Utils::translate('preview') }}
                </x-filament::tabs.item>
            @else
                <x-filament::tabs.item icon="heroicon-o-clipboard-document">{{ Utils::translate('preview') }}</x-filament::tabs.item>
            @endif
        </x-filament::tabs>

        <div class="mt-2">
            <div x-show="activeTab === 'filtering'">
                {{ $this->table }}
            </div>

            <div x-show="activeTab === 'settings'">
                <form wire:submit="submitSettings">
                    <x-filament::button type="submit" class="my-4">{{ __('Create') }}</x-filament::button>
                    {{ $this->form }}
                </form>
            </div>

            <div x-show="activeTab === 'preview'">
                {{ $this->table }}
            </div>
        </div>
    </div>

    <x-filament-actions::modals/>

</x-filament-panels::page>
