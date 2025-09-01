<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Form Filters -->
        <x-filament-panels::form wire:submit="generateReport">
            {{ $this->form }}
        </x-filament-panels::form>

        <!-- Report Preview -->
        @if($reportUrl)
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Report Preview</h3>
                        <div class="flex space-x-2">
                            <a href="{{ $reportUrl }}" 
                               target="_blank" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Open in New Tab
                            </a>
                            <button onclick="refreshIframe()" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="border border-gray-200 rounded-lg overflow-hidden" style="height: 800px;">
                        <iframe id="reportFrame" 
                                src="{{ $reportUrl }}" 
                                class="w-full h-full border-0"
                                title="Transaction Report">
                        </iframe>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm p-12">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8l2 2 4-4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Report Generated</h3>
                    <p class="mt-1 text-sm text-gray-500">Configure your filters above and click "Preview Report" to generate a report.</p>
                </div>
            </div>
        @endif
    </div>

    <script>
        function refreshIframe() {
            const iframe = document.getElementById('reportFrame');
            if (iframe && iframe.src) {
                iframe.src = iframe.src;
            }
        }
    </script>
</x-filament-panels::page>