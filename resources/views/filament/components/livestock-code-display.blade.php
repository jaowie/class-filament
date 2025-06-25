<div class="space-y-2 p-4 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Generated Codes:</h3>
    @if (count($codes) > 0)
        <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300">
            @foreach ($codes as $code)
                <li>
                    <span class="font-medium text-primary-600 dark:text-primary-400">{{ $code }}</span>
                </li>
            @endforeach
        </ul>
        {{-- <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">Please review these codes. If they look correct, click "Create Livestock Entries" to finalize.</p> --}}
    @else
        <p class="text-gray-500 dark:text-gray-400">No codes generated yet. Please ensure the 'Owner', 'Quantity of Livestock', and 'Date of Delivery' fields are filled in the previous step.</p>
    @endif
</div>

