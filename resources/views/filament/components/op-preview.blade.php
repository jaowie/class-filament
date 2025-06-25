<div class="space-y-6">
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

    <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-bold text-lg">Livestock Details</h3>
        <div class="mt-2 space-y-2">
            @foreach($data->livestock as $livestock)
                <div class="border-b pb-2">
                    <p><span class="font-medium">Type:</span> {{ $livestock['type'] }}</p>
                    <p><span class="font-medium">Batch:</span> {{ $livestock['batch'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-bold text-lg">Fees Breakdown</h3>
        <div class="mt-2">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data->account_code_details as $fee)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $fee['code'] }}</td>
                            <td class="px-4 py-2">{{ $fee['description'] }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">₱ {{ number_format($fee['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-4 py-2 text-right font-bold">Total Amount:</td>
                        <td class="px-4 py-2 font-bold">₱ {{ number_format($data->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($data->remarks)
    <div class="p-4 bg-gray-50 rounded-lg">
        <h3 class="font-bold text-lg">Remarks</h3>
        <p class="mt-2">{{ $data->remarks }}</p>
    </div>
    @endif

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    Please review all details carefully before confirming. This action will create the official Order of Payment.
                </p>
            </div>
        </div>
    </div>
</div>