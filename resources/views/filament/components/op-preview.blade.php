@props(['data' => null])

@if($data)
<div class="space-y-6">
    <!-- Owner Information -->
    <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-bold text-lg">Owner Information</h3>
        <div class="grid grid-cols-2 gap-4 mt-2">
            <div>
                <p class="text-sm text-gray-500">Name</p>
                <p class="font-medium">{{ $data->owner_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Address</p>
                <p class="font-medium">{{ $data->owner_address }}</p>
            </div>
        </div>
    </div>

    <!-- Livestock Details -->
    <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-bold text-lg">Livestock Details</h3>
        <div class="mt-2 space-y-2">
                <div class="border-b pb-2">
                    <p><span class="font-medium">Type:</span> {{ $data->livestock[0]['type'] }}</p>
                    <p><span class="font-medium">Batch:</span> {{ $data->livestock[0]['batch'] }}</p>
                </div>
        </div>
    </div>

    <!-- Fees Breakdown -->
    <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-bold text-lg">Fees Breakdown</h3>
        <div class="mt-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Code</th>
                        <th class="px-4 py-2 text-left">Description</th>
                        <th class="px-4 py-2 text-left">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data->account_code_details as $fee)
                        <tr>
                            <td class="px-4 py-2">{{ $fee['code'] }}</td>
                            <td class="px-4 py-2">
                                @if(is_array($fee['description']))
                                    {{ implode(', ', $fee['description']) }}
                                @else
                                    {{ $fee['description'] }}
                                @endif
                            </td>
                            <td class="px-4 py-2">₱{{ number_format($fee['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-4 py-2 text-right font-bold">Total Amount:</td>
                        <td class="px-4 py-2 font-bold">₱{{ number_format($data->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@else
<div class="bg-red-50 p-4 rounded-lg">
    <p class="text-red-700">Preview data not available</p>
</div>
@endif