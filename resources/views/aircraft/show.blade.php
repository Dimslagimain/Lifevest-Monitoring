@extends('layouts.app')

@section('header-right')
    <x-aircraft-header-info :aircraft="$aircraft" :registration="$registration" />
@endsection

@section('content')
    <x-toolbar :registration="$registration" />

    <x-part-number-bar :aircraft="$aircraft" :qtyAdult="$qtyAdult ?? 0" :qtyCrew="$qtyCrew ?? 0" :qtyInfant="$qtyInfant ?? 0" :expAdult="$expAdult ?? 0" :expCrew="$expCrew ?? 0" :expInfant="$expInfant ?? 0" />

    <x-status-legend />

    @include('aircraft.partials.' . $partial)

    @include('components.date-modal')
@endsection

@push('scripts')
    <script>
        window.AIRCRAFT_CONFIG = {
            registration: '{{ $registration }}',
            updateUrl: '{{ route('aircraft.updateSeats', $registration) }}',
            deleteUrl: '{{ route('aircraft.deleteSeat', $registration) }}',
            csrfToken: '{{ csrf_token() }}',
            isAdmin: {{ auth()->user() && auth()->user()->isAdmin() ? 'true' : 'false' }}
        };
    </script>
@endpush
