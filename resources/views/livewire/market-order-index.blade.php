<?php
/** @var \App\Models\MarketOrder $marketOrder */
?>

<div>
    @include('partials.market-order-show-radios')

    @include('partials.market-order-show-filters')

    <table class="table">
        <thead>
        <tr>
            @if($viewModel->showTypeColumn())
                <td>Type</td>
            @endif
            <td>Item</td>
            <td class="text-center">{{ $viewModel->priceEachLabel() }}</td>
            <td class="text-center">Count</td>
            <td class="text-center">Total Cost</td>
            <td class="text-center">{{ $viewModel->storageOneLabel() }}</td>

            @if($viewModel->showStorageTwoColumn())
                <td class="text-center">{{ $viewModel->storageTwoLabel() }}</td>
            @endif

            @unless($type == 'mine')
                <td class="text-center">Discord Profile</td>
            @endunless

            <td>{{-- action icons --}}</td>
        </tr>
        </thead>
        <tbody>
        @foreach($allMarketOrders as $marketOrder)
            <tr>
                @if($viewModel->showTypeColumn())
                    <td>
                        {{ str($marketOrder->type)->title() }}
                    </td>
                @endif

                <td>
                    {{ $marketOrder->item->name() }}
                </td>

                <td class="text-center">
                    ${{ number_format($marketOrder->price_each) }}
                </td>

                <td class="text-center">
                    {{ number_format($marketOrder->count) }}
                </td>

                <td class="text-center">
                    ${{ number_format($marketOrder->totalCost) }}
                </td>

                <td class="text-center">
                    {{ $marketOrder->storageName }}
                </td>

                @if($viewModel->showStorageTwoColumn())
                    <td class="text-center">
                        {{ $marketOrder->altStorageName }}
                    </td>
                @endif

                @if($type == 'mine')
                    <td class="text-end">
                        <i wire:click="$emit('editMarketOrder', '{{ $marketOrder->id }}')"
                           class="bi bi-pencil-fill text-warning cursor-pointer"
                           title="Edit"></i>

                        <i class="bi bi-x-circle-fill text-danger cursor-pointer"
                           wire:click="closeOrder('{{ $marketOrder->id }}')"
                           title="Close"></i>
                    </td>
                @else
                    <td class="text-center">
                        <x-discord-profile-link-logo :user="$marketOrder->user" />
                    </td>
                    <td>
                        @if($marketOrder->details)
                        <i class="bi bi-eye text-info cursor-pointer"
                           title="Additional Details"
                           wire:click="$emit('openDetailsModal', '{{ $marketOrder->id }}')">
                        </i>
                        @endif
                    </td>
                @endif

            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $allMarketOrders->links() }}
    </div>

    @auth
        <livewire:market-order-create-edit />
        <livewire:market-order-additional-details-modal />
    @endauth
</div>