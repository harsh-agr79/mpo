<x-filament::page>
    <h1>{{ $record->operation }} Activity Log</h1>

    <div>
        <p><strong>Table:</strong> {{ $record->table_name }}</p>
        <p><strong>Operation:</strong> {{ ucfirst($record->operation) }}</p>
        <p><strong>Time:</strong> {{ $record->created_at->diffForHumans() }}</p>
        <p><strong>User:</strong> {{ $record->user->name ?? 'N/A' }}</p>
    </div>

    <hr>

    <div>
        <h3>Old Data</h3>
        <pre>{{ json_encode($record->old_data, JSON_PRETTY_PRINT) }}</pre>
    </div>

    <div>
        <h3>New Data</h3>
        <pre>{{ json_encode($record->new_data, JSON_PRETTY_PRINT) }}</pre>
    </div>
</x-filament::page>
