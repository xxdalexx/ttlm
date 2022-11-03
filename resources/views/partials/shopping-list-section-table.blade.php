<div class="row">
    <div class="col-12">
        <table class="table">
            <thead>
            <tr>
                <th class="fs-4">
                    {{ str($type)->title() }}
                </th>
                @foreach($viewModel->itemNameColumnHeaders($type) as $columnHeader)
                    <td @class(['text-center', 'table-success' => ! $columnHeader->isStillNeeded]) >
                        @if(App\TT\Recipes::getNamesIfComponentsExist()->contains($columnHeader->internalName ?? ''))
                        <a href="#" wire:click.prevent="setRecipe('{{ $columnHeader->internalName }}', {{ $columnHeader->totalNeeded }})">
                            {{ $columnHeader->displayName }}
                        </a>
                        @else
                            {{ $columnHeader->displayName }}
                        @endif
                    </td>
                @endforeach
            </tr>
            </thead>
            <tbody>

            <tr>
                <td>Total Needed</td>
                @foreach($viewModel->totalNeededColumns($type) as $count)
                    <td class="text-center cursor-normal">
                        {{ $count }}
                    </td>
                @endforeach
            </tr>

            <tr>
                <td>Still Needed</td>
                @foreach($viewModel->stillNeededColumns($type) as $count)
                    <td class="text-center cursor-normal">
                        {{ $count }}
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>
    </div>
</div>
