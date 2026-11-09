@if (! $record)
    <div></div>
@else
    @php
        $isOnlineOrder = empty($record->created_by);
        $customerName = trim($record->customer_name ?? '');

        if ($isOnlineOrder) {
            $creatorDisplay = $customerName !== '' ? "Online Customer ({$customerName})" : 'Online Wholesale Client';
            $creatorBadge = [
                'label' => 'Online Web Order',
                'class' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950 dark:text-sky-300 dark:border-sky-800',
            ];
        } else {
            $creatorDisplay = $record->creator?->name ?? 'Staff';
            $creatorBadge = [
                'label' => 'Staff Member',
                'class' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800',
            ];
        }

        $createdTime = $record->created_at?->format('d M, Y \a\t h:i A') ?? '-';

        if ($record->editor) {
            $editorDisplay = $record->editor->name;
            $editorBadge = [
                'label' => 'Staff Member',
                'class' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800',
            ];
        } elseif ($isOnlineOrder) {
            $editorDisplay = $record->payment_status === \App\Enums\PaymentStatus::Paid ? 'Paystack Gateway / Client' : $creatorDisplay;
            $editorBadge = [
                'label' => 'Online Order',
                'class' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950 dark:text-sky-300 dark:border-sky-800',
            ];
        } else {
            $editorDisplay = $creatorDisplay;
            $editorBadge = $creatorBadge;
        }
        $updatedTime = $record->updated_at?->format('d M, Y \a\t h:i A') ?? '-';

        $logs = $record->auditLogs()->with('user')->limit(30)->get();
    @endphp

    <div class="space-y-4">
        {{-- Top Summary Cards: Recorded By & Last Modified By --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Initially Recorded By Card --}}
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 shadow-xs">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Initially Recorded By</span>
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-md border {{ $creatorBadge['class'] }}">
                        {{ $creatorBadge['label'] }}
                    </span>
                </div>
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-snug">
                    {{ $creatorDisplay }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $createdTime }}</span>
                </p>
            </div>

            {{-- Last Edited By Card --}}
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/70 shadow-xs">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Last Modified By</span>
                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-md border {{ $editorBadge['class'] }}">
                        {{ $editorBadge['label'] }}
                    </span>
                </div>
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-snug">
                    {{ $editorDisplay }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $updatedTime }}</span>
                </p>
            </div>
        </div>

        {{-- Vertical Timeline Panel --}}
        <div class="p-4 sm:p-5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xs">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-gray-100 dark:border-gray-800 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Activity &amp; Edit Timeline</h4>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                        {{ $logs->count() }} {{ $logs->count() === 1 ? 'Event' : 'Events' }}
                    </span>
                </div>
                <span class="text-[11px] text-gray-400 dark:text-gray-500 font-medium">Chronological Audit Trail (Newest First)</span>
            </div>

            @if ($logs->isEmpty())
                <div class="text-center py-8 text-xs text-gray-400 dark:text-gray-500 italic">
                    No edits recorded yet.
                </div>
            @else
                <div class="space-y-0">
                    @foreach ($logs as $index => $log)
                        @php
                            $userStr = $log->user_name ?: ($log->user?->name ?: ($log->user_id ? 'Staff' : ($customerName ? "Online Customer ({$customerName})" : 'Online Customer')));
                            $isStaff = ! empty($log->user_id);
                            $isGateway = str_contains(strtolower($userStr), 'paystack');

                            $actorLabel = $isStaff ? 'Staff' : ($isGateway ? 'Payment Gateway' : 'Customer');
                            $actorBadgeClass = match (true) {
                                $isStaff => 'bg-purple-50 text-purple-800 border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-700',
                                $isGateway => 'bg-sky-50 text-sky-800 border-sky-200 dark:bg-sky-900/40 dark:text-sky-300 dark:border-sky-700',
                                default => 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700',
                            };

                            $timeStr = $log->created_at?->diffForHumans() ?? '';
                            $exactTime = $log->created_at?->format('d M, Y \a\t h:i A') ?? '';

                            // Normalize historical descriptions so they reference invoice_number instead of #ID
                            $descRaw = $log->description;
                            $descRaw = str_replace(
                                ["Sale (#{$record->id})", "(#{$record->id})"],
                                "order {$record->invoice_number}",
                                $descRaw
                            );

                            [$actionTitle, $actionBadgeClass, $iconBg, $iconType] = match ($log->action) {
                                'created' => [
                                    'Order Placed',
                                    'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700',
                                    'bg-emerald-600 text-white',
                                    'create',
                                ],
                                'payment_recorded' => [
                                    'Payment Confirmed',
                                    'bg-teal-100 text-teal-800 border-teal-200 dark:bg-teal-900/40 dark:text-teal-300 dark:border-teal-700',
                                    'bg-teal-600 text-white',
                                    'payment',
                                ],
                                'status_changed' => [
                                    'Status Updated',
                                    'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700',
                                    'bg-amber-500 text-white',
                                    'status',
                                ],
                                'payment_cancelled' => [
                                    'Payment Cancelled',
                                    'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-900/40 dark:text-rose-300 dark:border-rose-700',
                                    'bg-rose-600 text-white',
                                    'cancel',
                                ],
                                'order_reversed' => [
                                    'Order Reversal',
                                    'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700',
                                    'bg-red-600 text-white',
                                    'reverse',
                                ],
                                default => [
                                    ucwords(str_replace('_', ' ', $log->action)),
                                    'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                    'bg-gray-500 text-white',
                                    'default',
                                ],
                            };

                            // Format [status] values nicely with colored pills
                            $descHtml = e($descRaw);
                            $descHtml = preg_replace(
                                '/\[(.*?)\]/',
                                "<span class='inline-block px-2 py-0.5 mx-1 font-bold text-[11px] uppercase tracking-wider rounded bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-900/50 dark:text-amber-200 dark:border-amber-700'>$1</span>",
                                $descHtml
                            );
                        @endphp

                        <div class="flex gap-3.5 relative pb-4 last:pb-0">
                            {{-- Left Column with Circular Node & Continuous Connecting Line --}}
                            <div class="flex flex-col items-center shrink-0" style="width: 32px;">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shadow-xs shrink-0 {{ $iconBg }}">
                                    @if ($iconType === 'create')
                                        <svg class="w-4 h-4 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    @elseif ($iconType === 'payment')
                                        <svg class="w-4 h-4 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @elseif ($iconType === 'status')
                                        <svg class="w-4 h-4 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    @elseif ($iconType === 'cancel')
                                        <svg class="w-4 h-4 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @elseif ($iconType === 'reverse')
                                        <svg class="w-4 h-4 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    @else
                                        <svg class="w-4 h-4 shrink-0" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                                    @endif
                                </div>
                                @if (! $loop->last)
                                    <div class="w-0.5 flex-1 bg-gray-200 dark:bg-gray-700 my-1"></div>
                                @endif
                            </div>

                            {{-- Right Column Event Card --}}
                            <div class="flex-1 min-w-0 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-3.5 shadow-xs mb-1 hover:border-gray-300 dark:hover:border-gray-600 transition-colors">
                                <div class="flex items-center justify-between gap-2 flex-wrap mb-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border uppercase tracking-wider {{ $actionBadgeClass }}">
                                        {{ $actionTitle }}
                                    </span>
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400" title="{{ $exactTime }}">
                                        {{ $timeStr }}
                                    </span>
                                </div>

                                <div class="text-xs font-medium text-gray-900 dark:text-gray-100 leading-relaxed mb-2.5">
                                    {!! $descHtml !!}
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-800 text-[11px] flex-wrap gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-400 dark:text-gray-500 font-semibold uppercase text-[10px] tracking-wider">{{ $actorLabel }}:</span>
                                        <span class="font-bold px-2 py-0.5 rounded border text-[11px] {{ $actorBadgeClass }}">
                                            {{ $userStr }}
                                        </span>
                                    </div>
                                    <span class="text-gray-400 dark:text-gray-500 text-[11px]" title="Exact timestamp">
                                        {{ $exactTime }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
